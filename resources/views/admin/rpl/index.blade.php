<x-app-layout>
    <x-slot name="header">Manajemen RPL</x-slot>

    <div class="card-saas overflow-hidden">
        <div class="px-6 py-4 border-b border-siakad-light">
            <h3 class="font-semibold text-siakad-dark">Pengajuan RPL</h3>
        </div>
        <div class="divide-y divide-siakad-light/50">
            @forelse($pengajuan as $item)
                <div class="p-5">
                    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-medium text-siakad-dark">{{ $item->mahasiswa?->user?->name ?? '-' }}</p>
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-semibold bg-siakad-primary/10 text-siakad-primary">{{ $item->status }}</span>
                            </div>
                            <p class="text-xs text-siakad-secondary">{{ $item->mahasiswa?->nim ?? '-' }} • {{ $item->judul_pengajuan }}</p>
                            <p class="text-xs text-siakad-secondary mt-1">
                                {{ ucfirst(str_replace('_', ' ', $item->jenis_pengalaman ?? '-')) }}
                                @if($item->nama_instansi) • {{ $item->nama_instansi }} @endif
                            </p>
                            <p class="text-xs text-siakad-secondary mt-1">
                                {{ $item->tahunAkademik?->display_name ?? '-' }} • {{ $item->dokumen->count() }} dokumen • {{ (int) $item->target_sks_dimohon }} SKS dimohon • {{ (int) $item->total_sks_diakui }} SKS diakui
                            </p>
                            @if($item->periode_mulai || $item->periode_selesai)
                                <p class="text-xs text-siakad-secondary mt-1">
                                    Periode: {{ $item->periode_mulai?->format('d M Y') ?? '-' }} - {{ $item->periode_selesai?->format('d M Y') ?? '-' }}
                                </p>
                            @endif
                            @if($item->uraian_pengalaman)
                                <p class="text-sm text-siakad-secondary mt-3">{{ \Illuminate\Support\Str::limit($item->uraian_pengalaman, 200) }}</p>
                            @endif
                            @if($item->catatan)
                                <p class="text-sm text-siakad-secondary mt-2">Catatan: {{ $item->catatan }}</p>
                            @endif
                        </div>
                        <div class="xl:w-[420px] space-y-3">
                            <div class="rounded-2xl border border-siakad-light p-4 bg-siakad-light/20">
                                <p class="text-xs uppercase tracking-wider text-siakad-secondary">Tahap Asesmen</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <form action="{{ route('admin.rpl.assessment', $item) }}" method="POST">
                                        @csrf
                                        <button class="btn-ghost-saas px-3 py-2 rounded-lg text-sm">Pindah ke Asesmen</button>
                                    </form>
                                    <form action="{{ route('admin.rpl.approve', $item) }}" method="POST">
                                        @csrf
                                        <button class="btn-primary-saas px-3 py-2 rounded-lg text-sm">Setujui Akhir</button>
                                    </form>
                                    <form action="{{ route('admin.rpl.reject', $item) }}" method="POST" class="flex gap-2 w-full">
                                        @csrf
                                        <input type="text" name="catatan" class="input-saas px-3 py-2 text-sm flex-1" placeholder="Catatan penolakan">
                                        <button class="btn-ghost-saas px-3 py-2 rounded-lg text-sm whitespace-nowrap">Tolak</button>
                                    </form>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-siakad-light p-4">
                                <p class="text-xs uppercase tracking-wider text-siakad-secondary">Konversi Mata Kuliah</p>
                                <form action="{{ route('admin.rpl.konversi.store', $item) }}" method="POST" class="mt-3 space-y-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-medium mb-1">Mata Kuliah</label>
                                        <select name="mata_kuliah_id" class="input-saas w-full px-3 py-2 text-sm">
                                            <option value="">Pilih mata kuliah</option>
                                            @foreach($mataKuliahOptions as $mk)
                                                <option value="{{ $mk->id }}">{{ $mk->kode_mk }} - {{ $mk->nama_mk }} ({{ $mk->sks }} SKS)</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium mb-1">Nilai Awal</label>
                                            <input type="text" name="nilai_awal" class="input-saas w-full px-3 py-2 text-sm" placeholder="B / A-">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium mb-1">Nilai Konversi</label>
                                            <input type="text" name="nilai_konversi" class="input-saas w-full px-3 py-2 text-sm" placeholder="A / A-">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium mb-1">SKS Diakui</label>
                                            <input type="number" name="sks_diakui" min="1" max="24" class="input-saas w-full px-3 py-2 text-sm" placeholder="3">
                                        </div>
                                    </div>
                                    <label class="inline-flex items-center gap-2 text-xs text-siakad-secondary">
                                        <input type="checkbox" name="disetujui" value="1" checked class="rounded border-siakad-light text-siakad-primary focus:ring-siakad-primary">
                                        Tandai konversi disetujui
                                    </label>
                                    <button class="btn-primary-saas px-3 py-2 rounded-lg text-sm w-full">Simpan Konversi</button>
                                </form>
                            </div>

                            <div class="rounded-2xl border border-siakad-light p-4">
                                <p class="text-xs uppercase tracking-wider text-siakad-secondary">Hasil Konversi</p>
                                <div class="mt-3 space-y-2">
                                    @forelse($item->konversi as $konversi)
                                        <div class="flex items-start justify-between gap-3 rounded-xl bg-siakad-light/20 p-3">
                                            <div>
                                                <p class="text-sm font-medium text-siakad-dark">{{ $konversi->mataKuliah?->nama_mk ?? '-' }}</p>
                                                <p class="text-xs text-siakad-secondary">{{ $konversi->nilai_awal ?? '-' }} → {{ $konversi->nilai_konversi ?? '-' }} • {{ $konversi->sks_diakui }} SKS</p>
                                            </div>
                                            <span class="text-[10px] font-semibold px-2 py-1 rounded-full {{ $konversi->disetujui ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ $konversi->disetujui ? 'Disetujui' : 'Draft' }}
                                            </span>
                                        </div>
                                    @empty
                                        <p class="text-sm text-siakad-secondary">Belum ada konversi mata kuliah.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-sm text-siakad-secondary">Belum ada pengajuan RPL.</div>
            @endforelse
        </div>
        @if(method_exists($pengajuan, 'links'))
            <div class="px-6 py-4 border-t border-siakad-light">
                {{ $pengajuan->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
