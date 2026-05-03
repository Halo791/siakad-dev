<x-app-layout>
    <x-slot name="header">RPL - Rekognisi Pembelajaran Lampau</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="card-saas p-6 lg:col-span-2">
            <p class="text-xs uppercase tracking-wider text-siakad-secondary">Status Pengajuan</p>
            <h2 class="text-2xl font-bold text-siakad-dark mt-2">Pengajuan RPL</h2>
            <p class="text-sm text-siakad-secondary mt-2">Ajukan rekognisi pengalaman kerja, pelatihan, atau pendidikan nonformal/informal agar dapat disetarakan menjadi SKS mata kuliah.</p>
        </div>
        <div class="card-saas p-6">
            <p class="text-xs uppercase tracking-wider text-siakad-secondary">Tahun Akademik</p>
            <h3 class="text-xl font-semibold text-siakad-dark mt-2">{{ $activeTA?->display_name ?? '-' }}</h3>
            <div class="mt-4 rounded-xl bg-siakad-primary/5 p-4">
                <p class="text-xs text-siakad-secondary uppercase tracking-wider">SKS Diakui</p>
                <p class="text-3xl font-bold text-siakad-primary mt-1">{{ $pengajuan->sum(fn($item) => (int) $item->total_sks_diakui) }}</p>
                <p class="text-xs text-siakad-secondary mt-1">Total SKS dari pengajuan yang sudah disetujui</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card-saas p-6">
            <h3 class="font-semibold text-siakad-dark">Buat Pengajuan Baru</h3>
            <form action="{{ route('mahasiswa.rpl.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium mb-1">Jenis Pengalaman</label>
                    <select name="jenis_pengalaman" class="input-saas w-full px-4 py-2.5">
                        <option value="kerja">Pengalaman Kerja</option>
                        <option value="pelatihan">Pelatihan / Sertifikasi</option>
                        <option value="pendidikan_formal">Pendidikan Formal Sebelumnya</option>
                        <option value="pendidikan_nonformal">Pendidikan Nonformal</option>
                        <option value="pendidikan_informal">Pendidikan Informal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Judul / Ringkasan Pengajuan</label>
                    <input type="text" name="judul_pengajuan" class="input-saas w-full px-4 py-2.5" placeholder="Misal: Pengalaman sebagai Junior Web Developer">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Perusahaan / Lembaga</label>
                    <input type="text" name="nama_instansi" class="input-saas w-full px-4 py-2.5" placeholder="Nama perusahaan, lembaga, atau penyelenggara pelatihan">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Mulai</label>
                        <input type="date" name="periode_mulai" class="input-saas w-full px-4 py-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Selesai</label>
                        <input type="date" name="periode_selesai" class="input-saas w-full px-4 py-2.5">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Uraian Pengalaman</label>
                    <textarea name="uraian_pengalaman" rows="4" class="input-saas w-full px-4 py-2.5" placeholder="Jelaskan kompetensi, tugas, sertifikat, atau pengalaman yang ingin direkognisi"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">SKS yang Dimohon</label>
                    <input type="number" name="target_sks_dimohon" min="1" max="24" class="input-saas w-full px-4 py-2.5" placeholder="Contoh: 6">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Catatan Tambahan</label>
                    <textarea name="catatan" rows="2" class="input-saas w-full px-4 py-2.5" placeholder="Opsional"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Jenis Dokumen</label>
                    <input type="text" name="jenis_dokumen" class="input-saas w-full px-4 py-2.5" placeholder="sertifikat / portofolio / surat pengalaman / transkrip">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">File Dokumen</label>
                    <input type="file" name="file_dokumen" class="input-saas w-full px-4 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Keterangan Dokumen</label>
                    <textarea name="keterangan_dokumen" rows="2" class="input-saas w-full px-4 py-2.5"></textarea>
                </div>
                <button class="btn-primary-saas px-5 py-2.5 rounded-lg">Ajukan RPL</button>
            </form>
        </div>

        <div class="card-saas overflow-hidden">
            <div class="px-6 py-4 border-b border-siakad-light">
                <h3 class="font-semibold text-siakad-dark">Riwayat Pengajuan</h3>
            </div>
                <div class="divide-y divide-siakad-light/50">
                @forelse($pengajuan as $item)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-siakad-dark">{{ $item->judul_pengajuan }}</p>
                                <p class="text-xs text-siakad-secondary">{{ $item->tahunAkademik?->display_name ?? '-' }}</p>
                                <p class="text-xs text-siakad-secondary mt-1">
                                    {{ ucfirst(str_replace('_', ' ', $item->jenis_pengalaman ?? '-')) }}
                                    @if($item->nama_instansi) • {{ $item->nama_instansi }} @endif
                                </p>
                                <p class="text-xs text-siakad-secondary mt-1">
                                    {{ $item->dokumen->count() }} dokumen • {{ $item->konversi->count() }} konversi • {{ (int) $item->total_sks_diakui }} SKS diakui
                                </p>
                            </div>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-semibold bg-siakad-primary/10 text-siakad-primary">{{ $item->status }}</span>
                        </div>
                        @if($item->periode_mulai || $item->periode_selesai)
                            <p class="text-xs text-siakad-secondary mt-2">
                                Periode: {{ $item->periode_mulai?->format('d M Y') ?? '-' }} - {{ $item->periode_selesai?->format('d M Y') ?? '-' }}
                            </p>
                        @endif
                        @if($item->uraian_pengalaman)
                            <p class="text-sm text-siakad-secondary mt-3">{{ \Illuminate\Support\Str::limit($item->uraian_pengalaman, 180) }}</p>
                        @endif
                        @if($item->catatan)
                            <p class="text-sm text-siakad-secondary mt-3">{{ $item->catatan }}</p>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-sm text-siakad-secondary">Belum ada pengajuan RPL.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
