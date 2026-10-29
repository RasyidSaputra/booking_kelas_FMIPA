<x-dashboard-layout title="Bookingan saya">
    <div x-data="{
        editModalOpen: false,
        cancelModalOpen: false,
        editForm: {
            id: null,
            room_name: '',
            room_code: '',
            capacity: 200,
            booking_date: '',
            start_time: '',
            end_time: '',
            purpose: '',
            participant_count: '',
            status: ''
        },
        cancelData: {
            id: null,
            room_name: '',
            room_code: '',
            schedule: '',
            purpose: '',
            status: ''
        },
        openEditModal(data) {
            this.editForm = Object.assign({}, data);
            this.editModalOpen = true;
        },
        openCancelModal(data) {
            this.cancelData = Object.assign({}, data);
            this.cancelModalOpen = true;
        }
    }">
        <div class="dash-page-heading">
            <div>
                <p class="dash-eyebrow">RENCANA KEGIATAN ANDA</p>
                <h1>Bookingan saya</h1>
                <p>Pantau status, revisi waktu, atau batalkan peminjaman ruangan Anda.</p>
            </div>
            <a class="dash-button is-primary" href="{{ route('rooms.index') }}">
                <x-dashboard-icon name="building" />Jelajahi ruangan
            </a>
        </div>

        @if(session('success'))
            <div class="p-4 mb-5 text-sm font-medium text-emerald-800 bg-emerald-50 rounded-lg border border-emerald-200 flex items-center gap-2" role="alert">
                <x-dashboard-icon name="check" class="w-5 h-5 text-emerald-600 shrink-0" />
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 mb-5 text-sm font-medium text-red-800 bg-red-50 rounded-lg border border-red-200 flex items-center gap-2" role="alert">
                <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="p-4 mb-5 text-sm text-red-800 bg-red-50 rounded-lg border border-red-200" role="alert">
                <strong class="font-bold">Mohon periksa kembali isian form:</strong>
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="dash-table-card" aria-label="Daftar peminjaman saya">
            @if ($bookings->isEmpty())
                <div class="dash-empty"><x-dashboard-icon name="list" /><h2>Belum ada booking</h2><p>Pengajuan peminjaman Anda akan muncul di sini.</p><a class="dash-button is-primary" href="{{ route('rooms.index') }}">Lihat ruangan <x-dashboard-icon name="arrow" /></a></div>
            @else
                <div class="dash-table-scroll"><table class="dash-table"><thead><tr><th scope="col">Ruangan</th><th scope="col">Keperluan</th><th scope="col">Waktu pemakaian</th><th scope="col">Peserta</th><th scope="col">Status</th><th scope="col" style="text-align: right;">Aksi</th></tr></thead><tbody>
                    @foreach ($bookings as $booking)
                        @php
                            $startsAt = \Carbon\CarbonImmutable::parse($booking->starts_at);
                            $endsAt = \Carbon\CarbonImmutable::parse($booking->ends_at);
                            $isPast = $endsAt->isPast();
                            $canManage = in_array($booking->status, ['pending', 'approved']) && ! $isPast;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $booking->room_name }}</strong>
                                <small>{{ $booking->room_code }}</small>
                            </td>
                            <td class="dash-purpose">
                                {{ $booking->purpose }}
                                @if($booking->status === 'rejected' && $booking->rejection_reason)
                                    <small style="color: #b66669;">Alasan ditolak: {{ $booking->rejection_reason }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $startsAt->locale('id')->translatedFormat('d M Y') }}</strong>
                                <small>{{ $startsAt->format('H:i') }} – {{ $endsAt->format('H:i') }} WIB ({{ $startsAt->diffForHumans() }})</small>
                            </td>
                            <td>{{ $booking->participant_count }} orang</td>
                            <td><x-booking-status :status="$booking->status" /></td>
                            <td style="text-align: right; white-space: nowrap;">
                                @if($canManage)
                                    <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                        {{-- Tombol Revisi Waktu --}}
                                        <button type="button"
                                            x-on:click="openEditModal({
                                                id: {{ $booking->id }},
                                                room_name: @js($booking->room_name),
                                                room_code: @js($booking->room_code),
                                                capacity: {{ $booking->room_capacity ?? 200 }},
                                                booking_date: '{{ $startsAt->format('Y-m-d') }}',
                                                start_time: '{{ $startsAt->format('H:i') }}',
                                                end_time: '{{ $endsAt->format('H:i') }}',
                                                purpose: @js($booking->purpose),
                                                participant_count: {{ $booking->participant_count }},
                                                status: '{{ $booking->status }}'
                                            })"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-md transition-colors"
                                            title="Revisi waktu booking ini">
                                            <svg class="w-3.5 h-3.5 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                                            </svg>
                                            <span>Revisi Waktu</span>
                                        </button>

                                        {{-- Tombol Batalkan --}}
                                        <button type="button"
                                            x-on:click="openCancelModal({
                                                id: {{ $booking->id }},
                                                room_name: @js($booking->room_name),
                                                room_code: @js($booking->room_code),
                                                schedule: '{{ $startsAt->locale('id')->translatedFormat('d M Y') }}, {{ $startsAt->format('H:i') }} – {{ $endsAt->format('H:i') }} WIB',
                                                purpose: @js($booking->purpose),
                                                status: '{{ $booking->status }}'
                                            })"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold text-rose-800 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-md transition-colors"
                                            title="Batalkan booking ini">
                                            <svg class="w-3.5 h-3.5 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span>Batalkan</span>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">
                                        @if($isPast)
                                            Selesai
                                        @elseif($booking->status === 'cancelled')
                                            Dibatalkan
                                        @elseif($booking->status === 'rejected')
                                            Ditolak
                                        @else
                                            —
                                        @endif
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody></table></div>
            @endif
        </section>
        <div class="dash-pagination">{{ $bookings->links() }}</div>

        {{-- MODAL REVISI WAKTU BOOKING --}}
        <div x-show="editModalOpen"
            x-cloak
            x-on:keydown.escape.window="editModalOpen = false"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-edit-booking-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div x-show="editModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-on:click="editModalOpen = false"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="editModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-on:click.stop
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">

                    {{-- Header Modal --}}
                    <div class="flex items-start justify-between p-5 border-b border-gray-100 bg-slate-50/70">
                        <div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-emerald-100 text-emerald-800 mb-1" x-text="editForm.room_code"></span>
                            <h3 class="text-base font-bold text-gray-900" id="modal-edit-booking-title">
                                Revisi Waktu Booking
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5" x-text="editForm.room_name"></p>
                        </div>
                        <button type="button"
                            x-on:click="editModalOpen = false"
                            class="text-gray-400 hover:text-gray-500 p-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                            <x-dashboard-icon name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Form Revisi --}}
                    <form method="POST" x-bind:action="'{{ url('/bookings') }}/' + editForm.id" class="p-6 space-y-4">
                        @csrf
                        @method('PATCH')

                        {{-- Alert jika status sebelumnya adalah approved --}}
                        <template x-if="editForm.status === 'approved'">
                            <div class="p-3 text-xs font-medium text-amber-800 bg-amber-50 rounded-lg border border-amber-200 flex items-start gap-2">
                                <span class="text-sm shrink-0">⚠️</span>
                                <span><strong>Catatan:</strong> Booking ini sebelumnya telah disetujui. Merevisi jadwal akan mengajukan ulang jadwal baru ke Admin (status kembali menjadi <strong>Pending</strong>) untuk diverifikasi ulang.</span>
                            </div>
                        </template>

                        {{-- Tanggal Pemakaian --}}
                        <div>
                            <label for="edit_booking_date" class="block text-xs font-bold text-gray-700 mb-1">
                                Tanggal Pemakaian Baru <span class="text-red-500">*</span>
                            </label>
                            <input type="date"
                                id="edit_booking_date"
                                name="booking_date"
                                x-model="editForm.booking_date"
                                min="{{ today()->toDateString() }}"
                                required
                                class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        {{-- Waktu Mulai & Selesai --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="edit_start_time" class="block text-xs font-bold text-gray-700 mb-1">
                                    Waktu Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="time"
                                    id="edit_start_time"
                                    name="start_time"
                                    x-model="editForm.start_time"
                                    min="07:30"
                                    max="18:00"
                                    required
                                    class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label for="edit_end_time" class="block text-xs font-bold text-gray-700 mb-1">
                                    Waktu Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="time"
                                    id="edit_end_time"
                                    name="end_time"
                                    x-model="editForm.end_time"
                                    min="07:30"
                                    max="18:00"
                                    required
                                    class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 text-[11px] flex items-center gap-2">
                            <span class="text-sm">⏰</span>
                            <span>Jam operasional ruangan: <strong>07:30 – 18:00 WIB</strong>. Peminjaman harus selesai pada hari yang sama.</span>
                        </div>

                        {{-- Jumlah Peserta --}}
                        <div>
                            <label for="edit_participant_count" class="block text-xs font-bold text-gray-700 mb-1">
                                Jumlah Peserta (Orang)
                            </label>
                            <input type="number"
                                id="edit_participant_count"
                                name="participant_count"
                                x-model="editForm.participant_count"
                                min="1"
                                x-bind:max="editForm.capacity || 200"
                                class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500">
                            <span class="text-[11px] text-gray-400 mt-0.5 block">Kapasitas ruang: <span x-text="editForm.capacity"></span> orang</span>
                        </div>

                        {{-- Tujuan Kegiatan --}}
                        <div>
                            <label for="edit_purpose" class="block text-xs font-bold text-gray-700 mb-1">
                                Tujuan Kegiatan <span class="text-red-500">*</span>
                            </label>
                            <textarea id="edit_purpose"
                                name="purpose"
                                rows="3"
                                x-model="editForm.purpose"
                                required
                                class="w-full rounded-lg border-gray-300 text-xs shadow-xs focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="pt-3 flex items-center justify-end gap-2 border-t border-gray-100">
                            <button type="button"
                                x-on:click="editModalOpen = false"
                                class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-2xs hover:bg-gray-50 transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                <x-dashboard-icon name="check" class="w-4 h-4" />
                                <span>Simpan Revisi Waktu</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL KONFIRMASI CANCEL BOOKING --}}
        <div x-show="cancelModalOpen"
            x-cloak
            x-on:keydown.escape.window="cancelModalOpen = false"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-cancel-booking-title" role="dialog" aria-modal="true">
            {{-- Backdrop --}}
            <div x-show="cancelModalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-on:click="cancelModalOpen = false"
                class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="cancelModalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-on:click.stop
                    class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-200 p-6">

                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-base font-bold text-gray-900" id="modal-cancel-booking-title">
                                Batalkan Peminjaman Ruangan?
                            </h3>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                Apakah Anda yakin ingin membatalkan peminjaman ruangan ini? Tindakan ini akan membebaskan jadwal ruangan sehingga dapat digunakan oleh pengguna lain.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 p-3.5 bg-slate-50 rounded-lg border border-slate-200 text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Ruangan:</span>
                            <span class="font-semibold text-gray-900" x-text="cancelData.room_name + ' (' + cancelData.room_code + ')'"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Waktu:</span>
                            <span class="font-semibold text-gray-900" x-text="cancelData.schedule"></span>
                        </div>
                        <div class="flex items-start justify-between gap-2 pt-1 border-t border-slate-200/60">
                            <span class="text-gray-500 shrink-0">Keperluan:</span>
                            <span class="font-medium text-gray-700 text-right truncate max-w-[200px]" x-text="cancelData.purpose"></span>
                        </div>
                    </div>

                    <form method="POST" x-bind:action="'{{ url('/bookings') }}/' + cancelData.id + '/cancel'" class="mt-5 flex items-center justify-end gap-2">
                        @csrf
                        @method('PATCH')
                        <button type="button"
                            x-on:click="cancelModalOpen = false"
                            class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg shadow-2xs hover:bg-gray-50 transition-colors">
                            Kembali
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Ya, Batalkan Booking</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-dashboard-layout>
