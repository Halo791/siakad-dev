<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\RplDokumen;
use App\Models\RplPengajuan;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RplController extends Controller
{
    protected function rplModuleReady(): bool
    {
        return Schema::hasTable('rpl_pengajuan')
            && Schema::hasTable('rpl_dokumen')
            && Schema::hasTable('rpl_konversi');
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;

        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        if (!$this->rplModuleReady()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Modul RPL belum aktif di server ini.');
        }

        $pengajuan = RplPengajuan::with(['tahunAkademik', 'dokumen', 'konversi.mataKuliah'])
            ->where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->get();

        $activeTA = TahunAkademik::where('is_active', true)->first();

        return view('mahasiswa.rpl.index', compact('mahasiswa', 'pengajuan', 'activeTA'));
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;

        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'judul_pengajuan' => ['required', 'string', 'max:255'],
            'jenis_pengalaman' => ['required', 'in:kerja,pelatihan,pendidikan_formal,pendidikan_nonformal,pendidikan_informal'],
            'nama_instansi' => ['nullable', 'string', 'max:255'],
            'periode_mulai' => ['nullable', 'date'],
            'periode_selesai' => ['nullable', 'date', 'after_or_equal:periode_mulai'],
            'uraian_pengalaman' => ['required', 'string', 'max:5000'],
            'target_sks_dimohon' => ['required', 'integer', 'min:1', 'max:24'],
            'catatan' => ['nullable', 'string'],
            'jenis_dokumen' => ['nullable', 'string', 'max:100'],
            'file_dokumen' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:4096'],
            'keterangan_dokumen' => ['nullable', 'string', 'max:500'],
        ]);

        if (!$this->rplModuleReady()) {
            return redirect()->route('mahasiswa.dashboard')
                ->with('error', 'Modul RPL belum aktif di server ini.');
        }

        $activeTA = TahunAkademik::where('is_active', true)->first();

        $pengajuan = RplPengajuan::create([
            'mahasiswa_id' => $mahasiswa->id,
            'tahun_akademik_id' => $activeTA?->id,
            'judul_pengajuan' => $validated['judul_pengajuan'],
            'jenis_pengalaman' => $validated['jenis_pengalaman'],
            'nama_instansi' => $validated['nama_instansi'] ?? null,
            'periode_mulai' => $validated['periode_mulai'] ?? null,
            'periode_selesai' => $validated['periode_selesai'] ?? null,
            'uraian_pengalaman' => $validated['uraian_pengalaman'],
            'target_sks_dimohon' => $validated['target_sks_dimohon'],
            'status' => 'diajukan',
            'catatan' => $validated['catatan'] ?? null,
        ]);

        if ($request->hasFile('file_dokumen')) {
            $filePath = $request->file('file_dokumen')->store('rpl-dokumen', 'public');

            RplDokumen::create([
                'rpl_pengajuan_id' => $pengajuan->id,
                'jenis_dokumen' => $validated['jenis_dokumen'] ?? 'dokumen pendukung',
                'file_path' => $filePath,
                'keterangan' => $validated['keterangan_dokumen'] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan RPL berhasil dibuat.');
    }
}
