<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PembayaranMahasiswa;
use App\Models\TagihanMahasiswa;
use App\Services\KrsReadinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class KeuanganController extends Controller
{
    public function __construct(
        protected KrsReadinessService $readinessService
    ) {
    }

    public function index()
    {
        $user = Auth::user();
        if (!Schema::hasTable('tagihan_mahasiswa') || !Schema::hasTable('pembayaran_mahasiswa')) {
            return view('admin.keuangan.index', [
                'tagihan' => collect(),
                'pembayaran' => collect(),
            ]);
        }

        $tagihanQuery = TagihanMahasiswa::with(['mahasiswa.user', 'mahasiswa.prodi', 'tahunAkademik']);
        $pembayaranQuery = PembayaranMahasiswa::with(['mahasiswa.user', 'tagihan.tahunAkademik']);

        if (!$user->isSuperAdmin() && $user->fakultas_id) {
            $tagihanQuery->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $user->fakultas_id));
            $pembayaranQuery->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $user->fakultas_id));
        }

        $tagihan = $tagihanQuery->latest()->paginate(config('siakad.pagination', 15));

        $pembayaran = $pembayaranQuery->where('status_verifikasi', 'pending')
            ->latest()
            ->paginate(config('siakad.pagination', 15));

        return view('admin.keuangan.index', compact('tagihan', 'pembayaran'));
    }

    public function approvePembayaran(PembayaranMahasiswa $pembayaran)
    {
        abort_unless(Schema::hasTable('tagihan_mahasiswa') && Schema::hasTable('pembayaran_mahasiswa'), 503, 'Modul keuangan belum tersedia.');

        $pembayaran->load('tagihan', 'mahasiswa.prodi');
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $user->fakultas_id) {
            abort_unless($pembayaran->mahasiswa?->prodi?->fakultas_id === $user->fakultas_id, 403);
        }

        $pembayaran->update([
            'status_verifikasi' => 'approved',
            'diverifikasi_oleh' => Auth::id(),
            'catatan_verifikasi' => $pembayaran->catatan_verifikasi,
        ]);

        $tagihan = $pembayaran->tagihan;
        $tagihan->terbayar = (int) $tagihan->terbayar + (int) $pembayaran->jumlah_bayar;

        if ($tagihan->terbayar >= $tagihan->nominal) {
            $tagihan->status = 'lunas';
            $tagihan->terbayar = $tagihan->nominal;
        } else {
            $tagihan->status = 'cicilan';
        }

        $tagihan->save();

        if ($pembayaran->mahasiswa?->krs()->exists()) {
            $krs = $pembayaran->mahasiswa->krs()
                ->whereHas('tahunAkademik', fn($q) => $q->where('is_active', true))
                ->latest()
                ->first();
            if ($krs) {
                $this->readinessService->refreshKrsFinanceState($krs);
            }
        }

        return redirect()->back()->with('success', 'Pembayaran berhasil disetujui.');
    }

    public function rejectPembayaran(Request $request, PembayaranMahasiswa $pembayaran)
    {
        abort_unless(Schema::hasTable('tagihan_mahasiswa') && Schema::hasTable('pembayaran_mahasiswa'), 503, 'Modul keuangan belum tersedia.');

        $validated = $request->validate([
            'catatan_verifikasi' => ['nullable', 'string', 'max:1000'],
        ]);

        $pembayaran->load('mahasiswa.prodi', 'tagihan');
        $user = Auth::user();

        if (!$user->isSuperAdmin() && $user->fakultas_id) {
            abort_unless($pembayaran->mahasiswa?->prodi?->fakultas_id === $user->fakultas_id, 403);
        }

        $pembayaran->update([
            'status_verifikasi' => 'rejected',
            'diverifikasi_oleh' => Auth::id(),
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? 'Bukti pembayaran belum valid.',
        ]);

        $tagihan = $pembayaran->tagihan;
        if ($tagihan && $tagihan->status !== 'lunas') {
            $tagihan->status = 'belum_bayar';
            $tagihan->save();
        }

        return redirect()->back()->with('warning', 'Pembayaran ditolak dan perlu diperbaiki.');
    }
}
