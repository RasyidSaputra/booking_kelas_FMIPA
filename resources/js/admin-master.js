export function masterForm(record = {}) {
    return {
        code: '', name: '', capacity: '', location: '', description: '', photo_url: null,
        identity_number: '', email: '', role: 'mahasiswa',
        user_id: '', room_id: '', purpose: '', participant_count: '', rejection_reason: '',
        ...record,
        // Password dan pilihan hapus foto tidak pernah diambil dari data tabel.
        password: '', password_confirmation: '', remove_photo: false,
        is_active: record.is_active === undefined ? true : Boolean(Number(record.is_active)),
        facility_ids: (record.facility_ids ?? []).map(String),
        starts_at: (record.starts_at ?? '').replace(' ', 'T').slice(0, 16),
        ends_at: (record.ends_at ?? '').replace(' ', 'T').slice(0, 16),
    };
}

export function masterAction(routes, kind, id) {
    const resources = { room: 'rooms', facility: 'facilities', user: 'users', schedule: 'schedules' };
    if (resources[kind]) {
        return { url: routes[resources[kind]] + (id ? `/${id}` : ''), method: id ? 'PATCH' : 'POST' };
    }
    if (kind.startsWith('delete-') && resources[kind.slice(7)]) {
        return { url: `${routes[resources[kind.slice(7)]]}/${id}`, method: 'DELETE' };
    }
    if (kind === 'approve' || kind === 'reject') {
        return { url: `${routes.bookings}/${id}/review`, method: 'PATCH' };
    }
    if (kind === 'cancel') {
        return { url: `${routes.schedules}/${id}/cancel`, method: 'PATCH' };
    }
    throw new Error('Aksi master data tidak dikenali.');
}

export default function adminMaster(routes) {
    return {
        kind: '', recordId: null, form: masterForm(), errors: {}, generalError: '', busy: false,
        get title() {
            const labels = { room: 'ruangan', facility: 'fasilitas', user: 'pengguna', schedule: 'jadwal' };
            if (labels[this.kind]) return `${this.recordId ? 'Edit' : 'Tambah'} ${labels[this.kind]}`;
            return { approve: 'Setujui pengajuan', reject: 'Tolak pengajuan', cancel: 'Batalkan jadwal', 'delete-room': 'Hapus ruangan', 'delete-facility': 'Hapus fasilitas', 'delete-user': 'Hapus pengguna' }[this.kind] ?? 'Master data';
        },
        get destructive() { return this.kind.startsWith('delete-') || ['reject', 'cancel'].includes(this.kind); },
        open(kind, record = {}) {
            if (this.busy) return;
            this.kind = kind;
            this.recordId = record.id ?? null;
            this.form = masterForm(record);
            this.errors = {};
            this.generalError = '';
            this.$refs.form.reset();
            this.$nextTick(() => this.$refs.dialog.showModal());
        },
        close() { if (!this.busy) this.$refs.dialog.close(); },
        reset() { this.kind = ''; this.recordId = null; this.form = masterForm(); this.errors = {}; this.generalError = ''; },
        backdrop(event) {
            if (event.target !== this.$refs.dialog) return;
            const box = this.$refs.dialog.getBoundingClientRect();
            if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) this.close();
        },
        async submit() {
            if (this.busy) return;
            this.busy = true;
            this.errors = {};
            this.generalError = '';
            try {
                const action = masterAction(routes, this.kind, this.recordId);
                const body = new FormData(this.$refs.form);
                if (action.method !== 'POST') body.set('_method', action.method);
                if (this.kind === 'room') {
                    body.set('is_active', this.form.is_active ? '1' : '0');
                    body.set('remove_photo', this.form.remove_photo ? '1' : '0');
                }
                if (['approve', 'reject'].includes(this.kind)) body.set('decision', this.kind === 'approve' ? 'approved' : 'rejected');
                const response = await fetch(action.url, {
                    method: 'POST', credentials: 'same-origin', body,
                    headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const result = await response.json().catch(() => ({}));
                if (response.status === 422) {
                    this.errors = result.errors ?? {};
                    this.generalError = 'Data belum tersimpan. Periksa isian dan pesan di bawah.';
                    this.$nextTick(() => this.$refs.form.querySelector('[aria-invalid="true"]')?.focus());
                    return;
                }
                if (!response.ok) {
                    this.generalError = response.status === 419 || response.status === 401
                        ? 'Sesi telah berakhir. Muat ulang halaman dan masuk kembali.'
                        : response.status === 403 ? 'Akses ditolak. Fitur ini hanya untuk administrator.' : 'Aksi belum berhasil. Muat ulang halaman dan coba kembali.';
                    return;
                }
                window.location.reload();
            } catch {
                this.generalError = 'Tidak dapat menghubungi server. Periksa koneksi dan coba kembali.';
            } finally {
                this.busy = false;
            }
        },
    };
}
