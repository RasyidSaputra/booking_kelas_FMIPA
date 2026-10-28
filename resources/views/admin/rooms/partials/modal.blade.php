<dialog class="master-dialog" x-ref="dialog" aria-labelledby="master-dialog-title" x-on:cancel="if (busy) $event.preventDefault()" x-on:close="reset()" x-on:click="backdrop($event)">
    <form x-ref="form" method="POST" class="master-modal-form" x-on:submit.prevent="submit()" enctype="multipart/form-data">
        @csrf
        <header class="master-modal-heading"><div><p class="dash-eyebrow">PUSAT ADMINISTRASI</p><h2 id="master-dialog-title" x-text="title"></h2></div><button class="dash-icon-button" type="button" x-on:click="close()" x-bind:disabled="busy" aria-label="Tutup form"><x-dashboard-icon name="close" /></button></header>
        <div class="master-modal-body">
            <div class="master-form-errors" x-show="generalError" role="alert" x-cloak><strong x-text="generalError"></strong><ul><template x-for="(messages, field) in errors" :key="field"><li x-text="messages.join(' ')"></li></template></ul></div>

            <template x-if="kind === 'room'">
                <div class="master-form-grid">
                    <x-admin-field name="code" label="Kode ruangan" required maxlength="30" placeholder="Contoh: FMIPA-101" />
                    <x-admin-field name="name" label="Nama ruangan" required maxlength="100" placeholder="Contoh: Ruang Kuliah 101" />
                    <x-admin-field name="capacity" label="Kapasitas (orang)" type="number" required min="1" max="10000" />
                    <x-admin-field name="location" label="Lokasi / lantai" required maxlength="255" placeholder="Gedung A, lantai 1" />
                    <div class="master-field master-full"><label for="master-description">Informasi ruangan</label><textarea id="master-description" name="description" x-model="form.description" rows="3" maxlength="5000" placeholder="Informasi penggunaan dan ketentuan ruangan"></textarea></div>
                    <fieldset class="master-facilities master-full"><legend>Fasilitas ruangan</legend><div>@forelse($facilityOptions as $facility)<label><input type="checkbox" name="facility_ids[]" value="{{ $facility->id }}" x-model="form.facility_ids">{{ $facility->name }}</label>@empty<p>Belum ada fasilitas. Tambahkan melalui tab Fasilitas.</p>@endforelse</div></fieldset>
                    <div class="master-field master-full"><label for="master-photo">Foto ruangan (opsional)</label><input id="master-photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, atau WebP. Maksimal 2 MB.</small><template x-if="form.photo_url"><div class="master-photo-preview"><img x-bind:src="form.photo_url" alt="Foto ruangan saat ini"><label><input name="remove_photo" type="checkbox" value="1" x-model="form.remove_photo">Hapus foto saat ini</label></div></template></div>
                    <label class="master-checkbox master-full"><input type="checkbox" x-model="form.is_active">Ruangan aktif dan menerima pengajuan baru</label>
                </div>
            </template>

            <template x-if="kind === 'facility'"><div class="master-form-grid"><div class="master-full"><x-admin-field name="name" label="Nama fasilitas" required maxlength="100" placeholder="Contoh: Proyektor" /></div></div></template>

            <template x-if="kind === 'user'">
                <div class="master-form-grid">
                    <x-admin-field name="name" label="Nama lengkap" required maxlength="255" />
                    <x-admin-field name="identity_number" label="NIM / NIP" required maxlength="30" />
                    <x-admin-field name="email" label="Alamat email" type="email" required maxlength="255" />
                    <div class="master-field"><label for="master-role">Role akun</label><select id="master-role" name="role" x-model="form.role" required><option value="mahasiswa">Mahasiswa</option><option value="dosen">Dosen</option><option value="admin">Administrator</option></select></div>
                    <x-admin-field name="password" label="Kata sandi" type="password" x-bind:required="!recordId" minlength="8" autocomplete="new-password" />
                    <x-admin-field name="password_confirmation" label="Konfirmasi kata sandi" type="password" x-bind:required="Boolean(form.password)" minlength="8" autocomplete="new-password" />
                    <p class="master-form-note master-full" x-show="recordId">Kosongkan kata sandi untuk mempertahankan kata sandi pengguna. Role akun yang sedang Anda gunakan tidak dapat diubah.</p>
                </div>
            </template>

            <template x-if="kind === 'schedule'">
                <div class="master-form-grid">
                    <div class="master-field"><label for="master-room_id">Ruangan</label><select id="master-room_id" name="room_id" x-model="form.room_id" required><option value="">Pilih ruangan</option>@foreach($roomOptions as $room)<option value="{{ $room->id }}" @disabled(! $room->is_active)>{{ $room->code }} — {{ $room->name }} ({{ $room->capacity }} orang){{ $room->is_active ? '' : ' · Nonaktif' }}</option>@endforeach</select></div>
                    <div class="master-field"><label for="master-user_id">Penanggung jawab</label><select id="master-user_id" name="user_id" x-model="form.user_id" required><option value="">Pilih pengguna</option>@foreach($userOptions as $person)<option value="{{ $person->id }}">{{ $person->name }}{{ $person->identity_number ? ' — '.$person->identity_number : '' }}</option>@endforeach</select></div>
                    <x-admin-field name="starts_at" label="Mulai (WIB)" type="datetime-local" required />
                    <x-admin-field name="ends_at" label="Selesai (WIB)" type="datetime-local" required />
                    <x-admin-field name="participant_count" label="Jumlah peserta" type="number" min="1" max="10000" required />
                    <div class="master-field master-full"><label for="master-purpose">Keperluan kegiatan</label><textarea id="master-purpose" name="purpose" x-model="form.purpose" rows="3" maxlength="5000" required></textarea></div>
                    <p class="master-form-note master-full">Jam operasional ruangan adalah pukul 07:30 – 18:00 WIB (gedung tidak dapat digunakan setelah pukul 18:00). Jadwal yang dibuat admin langsung disetujui setelah pemeriksaan kapasitas dan bentrok waktu.</p>
                </div>
            </template>

            <template x-if="['approve', 'reject', 'cancel', 'delete-room', 'delete-facility', 'delete-user'].includes(kind)">
                <div class="master-confirmation">
                    <span class="master-confirm-icon"><x-dashboard-icon name="info" /></span>
                    <h3 x-text="form.room_name || form.name || form.purpose || 'Konfirmasi tindakan'"></h3>
                    <p x-show="kind === 'approve'">Setujui pengajuan ini? Jadwal dan kapasitas ruangan akan diperiksa ulang.</p>
                    <p x-show="kind === 'reject'">Pengajuan akan ditolak dan alasan berikut dapat dibaca oleh peminjam.</p>
                    <p x-show="kind === 'cancel'">Jadwal akan dibatalkan. Riwayat peminjaman tetap tersimpan.</p>
                    <p x-show="kind.startsWith('delete-')">Data ini akan dihapus. Data yang masih digunakan atau memiliki riwayat akan ditolak penghapusannya.</p>
                    <p class="master-confirm-detail" x-show="form.user_name" x-text="form.user_name"></p>
                    <p class="master-confirm-detail" x-show="form.starts_at" x-text="form.starts_at.replace('T', ' ') + ' — ' + form.ends_at.replace('T', ' ')"></p>
                    <template x-if="kind === 'reject'"><div class="master-field"><label for="master-rejection_reason">Alasan penolakan</label><textarea id="master-rejection_reason" name="rejection_reason" x-model="form.rejection_reason" rows="4" maxlength="2000" required placeholder="Jelaskan alasan penolakan dengan jelas"></textarea></div></template>
                </div>
            </template>
        </div>
        <footer class="master-modal-footer"><button type="button" class="dash-button" x-on:click="close()" x-bind:disabled="busy">Batal</button><button type="submit" class="dash-button is-primary" x-bind:class="{ 'master-danger-button': destructive }" x-bind:disabled="busy"><span x-text="busy ? 'Memproses…' : (['room', 'facility', 'schedule', 'user'].includes(kind) ? 'Simpan perubahan' : 'Ya, konfirmasi')">Simpan perubahan</span><x-dashboard-icon name="check" /></button></footer>
    </form>
</dialog>
