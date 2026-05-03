<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\RplKonversi;
use App\Models\RplPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RplController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!Schema::hasTable('rpl_pengajuan')) {
            return view('admin.rpl.index', ['pengajuan' => collect(), 'mataKuliahOptions' => collect()]);
        }

        $query = RplPengajuan::with(['mahasiswa.user', 'mahasiswa.prodi', 'tahunAkademik', 'dokumen', 'konversi.mataKuliah']);

        if (!$user->isSuperAdmin() && $user->fakultas_id) {
            $query->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $user->fakultas_id));
        }

        $pengajuan = $query->latest()->paginate(config('siakad.pagination', 15));
        $mataKuliahOptions = MataKuliah::with('prodi')
            ->when(!$user->isSuperAdmin() && $user->fakultas_id, fn($q) => $q->whereHas('prodi', fn($p) => $p->where('fakultas_id', $user->fakultas_id)))
            ->orderBy('semester')
            ->orderBy('nama_mk')
            ->get();

        return view('admin.rpl.index', compact('pengajuan', 'mataKuliahOptions'));
    }

    public function markAsAssessment(RplPengajuan $pengajuan)
    {
        abort_unless(Schema::hasTable('rpl_pengajuan'), 503, 'Modul RPL belum tersedia.');

        $user = Auth::user();
        $pengajuan->load('mahasiswa.prodi');

        if (!$user->isSuperAdmin() && $user->fakultas_id) {
            abort_unless($pengajuan->mahasiswa?->prodi?->fakultas_id === $user->fakultas_id, 403);
        }

        $pengajuan->update([
            'status' => 'asesmen',
        ]);

        return redirect()->back()->with('success', 'Pengajuan RPL dipindahkan ke tahap asesmen.');
    }

    public function storeKonversi(Request $request, RplPengajuan $pengajuan)
    {
        abort_unless(Schema::hasTable('rpl_pengajuan') && Schema::hasTable('rpl_konversi'), 503, 'Modul RPL belum tersedia.');

        $validated = $request->validate([
            'mata_kuliah_id' => ['required', 'exists:mata_kuliah,id'],
            'nilai_awal' => ['nullable', 'string', 'max:50'],
            'nilai_konversi' => ['nullable', 'string', 'max:50'],
            'sks_diakui' => ['required', 'integer', 'min:1', 'max:24'],
            'disetujui' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $pengajuan->load('mahasiswa.prodi');

        if (!$user->isSuperAdmin() && $user->fakultas_id) {
            abort_unless($pengajuan->mahasiswa?->prodi?->fakultas_id === $user->fakultas_id, 403);
        }

        RplKonversi::updateOrCreate(
            [
                'rpl_pengajuan_id' => $pengajuan->id,
                'mata_kuliah_id' => $validated['mata_kuliah_id'],
            ],
            [
                'nilai_awal' => $validated['nilai_awal'] ?? null,
                'nilai_konversi' => $validated['nilai_konversi'] ?? null,
                'sks_diakui' => $validated['sks_diakui'],
                'disetujui' => $request->boolean('disetujui', true),
            ]
        );

        return redirect()->back()->with('success', 'Konversi mata kuliah berhasil disimpan.');
    }

    public function approve(RplPengajuan $pengajuan)
    {
        abort_unless(Schema::hasTable('rpl_pengajuan'), 503, 'Modul RPL belum tersedia.');

        $user = Auth::user();
        $pengajuan->load('mahasiswa.prodi');

        if (!$user->isSuperAdmin() && $user->fakultas_id) {
            abort_unless($pengajuan->mahasiswa?->prodi?->fakultas_id === $user->fakultas_id, 403);
        }

        $totalSks = $pengajuan->konversi()->where('disetujui', true)->sum('sks_diakui');

        abort_if($totalSks <= 0, 422, 'Masukkan hasil konversi mata kuliah terlebih dahulu sebelum menyetujui RPL.');

        $pengajuan->update([
            'status' => 'disetujui',
            'total_sks_diakui' => $totalSks,
            'catatan' => $pengajuan->catatan,
        ]);

        return redirect()->back()->with('success', 'Pengajuan RPL berhasil disetujui.');
    }

    public function reject(Request $request, RplPengajuan $pengajuan)
    {
        abort_unless(Schema::hasTable('rpl_pengajuan'), 503, 'Modul RPL belum tersedia.');

        $validated = $request->validate([
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $pengajuan->load('mahasiswa.prodi');

        if (!$user->isSuperAdmin() && $user->fakultas_id) {
            abort_unless($pengajuan->mahasiswa?->prodi?->fakultas_id === $user->fakultas_id, 403);
        }

        $pengajuan->update([
            'status' => 'ditolak',
            'catatan' => $validated['catatan'] ?? 'Pengajuan RPL ditolak.',
        ]);

        return redirect()->back()->with('warning', 'Pengajuan RPL ditolak.');
    }
}
