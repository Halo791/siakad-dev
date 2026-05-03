<x-app-layout>
    <x-slot name="header">Manajemen Keuangan</x-slot>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="card-saas overflow-hidden">
            <div class="px-6 py-4 border-b border-siakad-light">
                <h3 class="font-semibold text-siakad-dark">Pembayaran Menunggu Verifikasi</h3>
            </div>
            <div class="divide-y divide-siakad-light/50">
                @forelse($pembayaran as $pay)
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-siakad-dark">{{ $pay->mahasiswa?->user?->name ?? '-' }}</p>
                                <p class="text-xs text-siakad-secondary">{{ $pay->mahasiswa?->nim ?? '-' }} • {{ $pay->tagihan?->jenis_tagihan ?? '-' }}</p>
                                <p class="text-xs text-siakad-secondary mt-1">Rp{{ number_format($pay->jumlah_bayar, 0, ',', '.') }}</p>
                            </div>
                            <div class="flex gap-2">
                                <form action="{{ route('admin.keuangan.pembayaran.approve', $pay) }}" method="POST">
                                    @csrf
                                    <button class="btn-primary-saas px-3 py-2 rounded-lg text-sm">Setujui</button>
                                </form>
                                <form action="{{ route('admin.keuangan.pembayaran.reject', $pay) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="catatan_verifikasi" class="input-saas px-3 py-2 text-sm w-56" placeholder="Catatan penolakan">
                                    <button class="btn-ghost-saas px-3 py-2 rounded-lg text-sm">Tolak</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-sm text-siakad-secondary">Tidak ada pembayaran pending.</div>
                @endforelse
            </div>
        </div>

        <div class="card-saas overflow-hidden">
            <div class="px-6 py-4 border-b border-siakad-light">
                <h3 class="font-semibold text-siakad-dark">Tagihan Mahasiswa</h3>
            </div>
            <div class="divide-y divide-siakad-light/50">
                @forelse($tagihan as $item)
                    <div class="p-5">
                        <p class="font-medium text-siakad-dark">{{ $item->mahasiswa?->user?->name ?? '-' }}</p>
                        <p class="text-xs text-siakad-secondary">{{ $item->mahasiswa?->nim ?? '-' }} • {{ $item->tahunAkademik?->display_name ?? '-' }}</p>
                        <p class="text-sm text-siakad-secondary mt-2">{{ strtoupper($item->jenis_tagihan) }} - Rp{{ number_format($item->nominal, 0, ',', '.') }} | Status: {{ $item->status }}</p>
                    </div>
                @empty
                    <div class="p-6 text-sm text-siakad-secondary">Belum ada data tagihan.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
