<?php

namespace App\Services;

use App\Models\BlokirMahasiswa;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MahasiswaBeasiswa;
use App\Models\RplPengajuan;
use App\Models\RplKonversi;
use App\Models\TahunAkademik;
use App\Models\TagihanMahasiswa;
use Illuminate\Support\Facades\Schema;

class KrsReadinessService
{
    public function evaluate(Mahasiswa $mahasiswa): array
    {
        $mahasiswa->loadMissing(['prodi', 'user']);
        $activeTahunAkademik = TahunAkademik::where('is_active', true)->first();

        $financeModuleReady = Schema::hasTable('tagihan_mahasiswa')
            && Schema::hasTable('pembayaran_mahasiswa')
            && Schema::hasTable('mahasiswa_beasiswa')
            && Schema::hasTable('blokir_mahasiswa')
            && Schema::hasTable('rpl_pengajuan');

        if (!$financeModuleReady) {
            return [
                'allowed' => true,
                'status' => 'clear',
                'reasons' => [],
                'active_tagihan_count' => 0,
                'active_tagihan_total' => 0,
                'active_scholarship' => null,
                'pending_scholarship' => null,
                'active_blocks' => collect(),
                'pending_rpl' => null,
                'approved_rpl_sks' => 0,
            ];
        }

        $activeTagihan = TagihanMahasiswa::with(['tahunAkademik', 'pembayaran' => fn($q) => $q->orderByDesc('created_at')])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->when($activeTahunAkademik, fn($q) => $q->where('tahun_akademik_id', $activeTahunAkademik->id))
            ->whereIn('status', ['belum_bayar', 'menunggu_verifikasi', 'cicilan', 'ditolak'])
            ->orderByDesc('created_at')
            ->get();

        $activeScholarship = MahasiswaBeasiswa::with('beasiswa')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'disetujui')
            ->where(function ($q) {
                $q->whereNull('mulai_berlaku')->orWhereDate('mulai_berlaku', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('berakhir_berlaku')->orWhereDate('berakhir_berlaku', '>=', now());
            })
            ->latest()
            ->first();

        $pendingScholarship = MahasiswaBeasiswa::with('beasiswa')
            ->where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('status', ['pengajuan', 'pending_verifikasi'])
            ->latest()
            ->first();

        $activeBlocks = BlokirMahasiswa::where('mahasiswa_id', $mahasiswa->id)
            ->active()
            ->latest()
            ->get();

        $pendingRpl = RplPengajuan::where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('status', ['draft', 'diajukan', 'asesmen', 'verifikasi'])
            ->latest()
            ->first();

        $approvedRplSks = RplKonversi::query()
            ->whereHas('pengajuan', fn($q) => $q->where('mahasiswa_id', $mahasiswa->id)->where('status', 'disetujui'))
            ->where('disetujui', true)
            ->sum('sks_diakui');

        $reasons = [];
        $status = 'clear';

        if ($activeBlocks->isNotEmpty()) {
            $status = 'blocked';
            foreach ($activeBlocks as $block) {
                $reasons[] = $block->alasan;
            }
        }

        $fullScholarship = $activeScholarship && ($activeScholarship->beasiswa->coverage_type ?? null) === 'full';

        if ($activeTagihan->isNotEmpty() && !$fullScholarship) {
            if ($status === 'clear') {
                $status = 'pending_payment';
            }

            foreach ($activeTagihan as $tagihan) {
                $remaining = max(0, (int) $tagihan->nominal - (int) $tagihan->terbayar);
                $reasons[] = sprintf(
                    '%s %s masih tersisa Rp%s',
                    strtoupper($tagihan->jenis_tagihan),
                    optional($tagihan->tahunAkademik)->display_name ?? '-',
                    number_format($remaining, 0, ',', '.')
                );
            }
        }

        if ($pendingScholarship && !$activeScholarship) {
            if ($status === 'clear') {
                $status = 'waiting_scholarship';
            }

            $reasons[] = 'Menunggu validasi beasiswa ' . ($pendingScholarship->beasiswa->nama ?? 'mahasiswa');
        }

        if ($pendingRpl) {
            $status = 'blocked';
            $reasons[] = 'Pengajuan RPL masih berstatus ' . $pendingRpl->status . '.';
        }

        if ($activeScholarship && $status === 'clear') {
            $reasons[] = 'Memiliki beasiswa aktif: ' . $activeScholarship->beasiswa->nama;
        }

        if ($approvedRplSks > 0 && $status === 'clear') {
            $reasons[] = 'SKS RPL yang sudah diakui: ' . (int) $approvedRplSks;
        }

        $allowed = $status === 'clear';

        $outstandingTotal = $activeTagihan->sum(fn($tagihan) => max(0, (int) $tagihan->nominal - (int) $tagihan->terbayar));

        if ($fullScholarship) {
            $outstandingTotal = 0;
        }

        return [
            'allowed' => $allowed,
            'status' => $status,
            'reasons' => array_values(array_unique(array_filter($reasons))),
            'active_tagihan_count' => $activeTagihan->count(),
            'active_tagihan_total' => $outstandingTotal,
            'active_scholarship' => $activeScholarship,
            'pending_scholarship' => $pendingScholarship,
            'active_blocks' => $activeBlocks,
            'pending_rpl' => $pendingRpl,
            'approved_rpl_sks' => (int) $approvedRplSks,
        ];
    }

    public function refreshKrsFinanceState(Krs $krs): Krs
    {
        $mahasiswa = $krs->relationLoaded('mahasiswa') ? $krs->mahasiswa : $krs->load('mahasiswa.prodi', 'mahasiswa.user')->mahasiswa;
        $state = $this->evaluate($mahasiswa);

        $krs->update([
            'keuangan_status' => $state['status'],
            'keuangan_catatan' => $state['reasons'] ? implode("\n", $state['reasons']) : null,
            'keuangan_checked_at' => now(),
        ]);

        return $krs->refresh();
    }

    public function hasClearance(Mahasiswa $mahasiswa): bool
    {
        return $this->evaluate($mahasiswa)['allowed'];
    }

    public function getSummary(Mahasiswa $mahasiswa): array
    {
        return $this->evaluate($mahasiswa);
    }
}
