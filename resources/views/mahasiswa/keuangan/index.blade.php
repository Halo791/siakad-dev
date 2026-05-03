<x-app-layout>
    <x-slot name="header">Keuangan Mahasiswa</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="card-saas p-6 lg:col-span-2">
            <p class="text-xs uppercase tracking-wider text-siakad-secondary">Status Kelayakan KRS</p>
            <h2 class="text-2xl font-bold text-siakad-dark mt-2">{{ strtoupper($summary['status']) }}</h2>
            <p class="text-sm text-siakad-secondary mt-2">
                {{ $summary['allowed'] ? 'Mahasiswa dapat mengajukan KRS.' : 'KRS masih ditahan sampai status keuangan atau RPL selesai.' }}
            </p>
            @if(!empty($summary['reasons']))
                <ul class="mt-4 space-y-2 text-sm text-siakad-secondary list-disc pl-5">
                    @foreach($summary['reasons'] as $reason)
                        <li>{{ $reason }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card-saas p-6">
            <p class="text-xs uppercase tracking-wider text-siakad-secondary">Ringkasan</p>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><span>Tagihan aktif</span><strong>{{ $summary['active_tagihan_count'] }}</strong></div>
                <div class="flex justify-between"><span>Sisa tagihan</span><strong>Rp{{ number_format($summary['active_tagihan_total'], 0, ',', '.') }}</strong></div>
                <div class="flex justify-between"><span>Beasiswa aktif</span><strong>{{ $summary['active_scholarship']->beasiswa->nama ?? '-' }}</strong></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card-saas overflow-hidden">
            <div class="px-6 py-4 border-b border-siakad-light">
                <h3 class="font-semibold text-siakad-dark">Tagihan Semester</h3>
            </div>
            <div class="divide-y divide-siakad-light/50">
                @forelse($tagihan as $item)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-siakad-dark">{{ strtoupper($item->jenis_tagihan) }}</p>
                                <p class="text-xs text-siakad-secondary">{{ $item->tahunAkademik?->display_name ?? '-' }}</p>
                                <p class="text-xs text-siakad-secondary mt-1">Jatuh tempo: {{ optional($item->jatuh_tempo)->format('d M Y') ?? '-' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-siakad-primary">Rp{{ number_format($item->nominal, 0, ',', '.') }}</p>
                                <p class="text-xs text-siakad-secondary">Terbayar Rp{{ number_format($item->terbayar, 0, ',', '.') }}</p>
                                <span class="inline-flex mt-2 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-siakad-primary/10 text-siakad-primary">{{ $item->status }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-sm text-siakad-secondary">Belum ada tagihan semester.</div>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <div class="card-saas p-6">
                <h3 class="font-semibold text-siakad-dark">Upload Pembayaran</h3>
                <form action="{{ route('mahasiswa.keuangan.pembayaran.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium mb-1">Tagihan</label>
                        <select name="tagihan_mahasiswa_id" class="input-saas w-full px-4 py-2.5">
                            @foreach($tagihan as $item)
                                <option value="{{ $item->id }}">{{ strtoupper($item->jenis_tagihan) }} - {{ $item->tahunAkademik?->display_name ?? '-' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Metode Pembayaran</label>
                        <input type="text" name="metode_pembayaran" value="transfer" class="input-saas w-full px-4 py-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Jumlah Bayar</label>
                        <input type="number" name="jumlah_bayar" min="1" class="input-saas w-full px-4 py-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Bukti Transfer</label>
                        <input type="file" name="bukti_transfer" class="input-saas w-full px-4 py-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Catatan</label>
                        <textarea name="catatan_verifikasi" rows="3" class="input-saas w-full px-4 py-2.5"></textarea>
                    </div>
                    <button class="btn-primary-saas px-5 py-2.5 rounded-lg">Kirim Pembayaran</button>
                </form>
            </div>

            <div class="card-saas overflow-hidden">
                <div class="px-6 py-4 border-b border-siakad-light">
                    <h3 class="font-semibold text-siakad-dark">Riwayat Pembayaran</h3>
                </div>
                <div class="divide-y divide-siakad-light/50">
                    @forelse($pembayaran as $pay)
                        <div class="p-5">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="font-medium text-siakad-dark">{{ $pay->tagihan?->jenis_tagihan ?? '-' }}</p>
                                    <p class="text-xs text-siakad-secondary">{{ $pay->tanggal_bayar?->format('d M Y') ?? '-' }} • {{ $pay->metode_pembayaran }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold">Rp{{ number_format($pay->jumlah_bayar, 0, ',', '.') }}</p>
                                    <p class="text-xs text-siakad-secondary">{{ $pay->status_verifikasi }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-sm text-siakad-secondary">Belum ada pembayaran yang dikirim.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
