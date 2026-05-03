<?php

namespace App\Http\Controllers\Mahasiswa;

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

    protected function financeModuleReady(): bool
    {
        return Schema::hasTable('tagihan_mahasiswa')
            && Schema::hasTable('pembayaran_mahasiswa')
            && Schema::hasTable('mahasiswa_beasiswa')
            && Schema::hasTable('blokir_mahasiswa');
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;

        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        if (!$this->financeModuleReady()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Modul keuangan belum aktif di server ini.');
        }

        $summary = $this->readinessService->getSummary($mahasiswa);

        $tagihan = TagihanMahasiswa::with(['tahunAkademik', 'pembayaran'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->get();

        $pembayaran = PembayaranMahasiswa::with(['tagihan.tahunAkademik'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->get();

        return view('mahasiswa.keuangan.index', compact('mahasiswa', 'summary', 'tagihan', 'pembayaran'));
    }

    public function storePembayaran(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;

        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'tagihan_mahasiswa_id' => ['required', 'exists:tagihan_mahasiswa,id'],
            'metode_pembayaran' => ['required', 'string', 'max:50'],
            'jumlah_bayar' => ['required', 'integer', 'min:1'],
            'bukti_transfer' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$this->financeModuleReady()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Modul keuangan belum aktif di server ini.');
        }

        $tagihan = TagihanMahasiswa::where('id', $validated['tagihan_mahasiswa_id'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->firstOrFail();

        $buktiPath = null;
        if ($request->hasFile('bukti_transfer')) {
            $buktiPath = $request->file('bukti_transfer')->store('bukti-pembayaran', 'public');
        }

        PembayaranMahasiswa::create([
            'tagihan_mahasiswa_id' => $tagihan->id,
            'mahasiswa_id' => $mahasiswa->id,
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'jumlah_bayar' => $validated['jumlah_bayar'],
            'tanggal_bayar' => now(),
            'status_verifikasi' => 'pending',
            'bukti_transfer' => $buktiPath,
            'catatan_verifikasi' => $validated['catatan_verifikasi'] ?? null,
        ]);

        $tagihan->update(['status' => 'menunggu_verifikasi']);

        return redirect()->back()->with('success', 'Pembayaran berhasil dikirim dan menunggu verifikasi.');
    }
}
