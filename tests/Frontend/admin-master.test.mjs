import test from 'node:test';
import assert from 'node:assert/strict';
import adminMaster, { masterForm, masterAction } from '../../resources/js/admin-master.js';

const routes = { rooms: '/admin/rooms', users: '/admin/users', facilities: '/admin/facilities', schedules: '/admin/schedules', bookings: '/admin/bookings' };

test('editing initializes dates, facilities and inactive room without retaining passwords', () => {
    const source = { name: 'Ruang 1', password: 'never-copy-this', facility_ids: [1, 2], is_active: 0, starts_at: '2026-10-26 08:30:00', ends_at: '2026-10-26 10:00:00' };
    const form = masterForm(source);
    assert.equal(form.name, 'Ruang 1');
    assert.equal(form.password, '');
    assert.equal(form.is_active, false);
    assert.deepEqual(form.facility_ids, ['1', '2']);
    assert.equal(form.starts_at, '2026-10-26T08:30');
    assert.equal(form.ends_at, '2026-10-26T10:00');
    form.facility_ids.push('3');
    assert.deepEqual(source.facility_ids, [1, 2]);
});

test('create, edit, delete, review and cancel target distinct HTTP actions', () => {
    assert.deepEqual(masterAction(routes, 'room', null), { url: '/admin/rooms', method: 'POST' });
    assert.deepEqual(masterAction(routes, 'user', 7), { url: '/admin/users/7', method: 'PATCH' });
    assert.deepEqual(masterAction(routes, 'delete-facility', 4), { url: '/admin/facilities/4', method: 'DELETE' });
    assert.deepEqual(masterAction(routes, 'approve', 8), { url: '/admin/bookings/8/review', method: 'PATCH' });
    assert.deepEqual(masterAction(routes, 'cancel', 9), { url: '/admin/schedules/9/cancel', method: 'PATCH' });
    assert.throws(() => masterAction(routes, 'unknown', 1));
});

function context(t, response) {
    const previous = { fetch: globalThis.fetch, document: globalThis.document, window: globalThis.window, FormData: globalThis.FormData };
    let reloads = 0;
    let captured;
    globalThis.document = { querySelector: () => ({ content: 'test-csrf' }) };
    globalThis.window = { location: { reload: () => { reloads++; } } };
    globalThis.FormData = class extends Map { constructor() { super(); } };
    globalThis.fetch = async (url, options) => { captured = { url, options }; return response; };
    t.after(() => Object.assign(globalThis, previous));
    const component = adminMaster(routes);
    component.$refs = { form: { querySelector: () => null } };
    component.$nextTick = callback => callback();
    component.kind = 'room';
    return { component, reloads: () => reloads, request: () => captured };
}

test('validation preserves form values and keeps page open for correction', async t => {
    const { component, reloads } = context(t, { status: 422, ok: false, json: async () => ({ errors: { name: ['Nama wajib diisi.'] } }) });
    component.form.code = 'R-12';
    await component.submit();
    assert.deepEqual(component.errors.name, ['Nama wajib diisi.']);
    assert.equal(component.form.code, 'R-12');
    assert.equal(component.busy, false);
    assert.equal(reloads(), 0);
});

test('successful saves submit CSRF and method spoofing then reload the page', async t => {
    const { component, reloads, request } = context(t, { status: 200, ok: true, json: async () => ({ message: 'Tersimpan' }) });
    component.recordId = 4;
    component.form.is_active = false;
    await component.submit();
    assert.equal(request().url, '/admin/rooms/4');
    assert.equal(request().options.method, 'POST');
    assert.equal(request().options.headers['X-CSRF-TOKEN'], 'test-csrf');
    assert.equal(request().options.body.get('_method'), 'PATCH');
    assert.equal(request().options.body.get('is_active'), '0');
    assert.equal(reloads(), 1);
});

test('expired sessions and failed requests do not reload or discard form data', async t => {
    const { component, reloads } = context(t, { status: 419, ok: false, json: async () => ({}) });
    component.form.name = 'Data belum tersimpan';
    await component.submit();
    assert.match(component.generalError, /Sesi/);
    assert.equal(component.form.name, 'Data belum tersimpan');
    assert.equal(reloads(), 0);
    assert.equal(component.busy, false);
});

test('a second submit is ignored while the first request is in flight', async t => {
    const { component, reloads } = context(t, { status: 200, ok: true, json: async () => ({}) });
    let finish;
    let calls = 0;
    globalThis.fetch = () => { calls++; return new Promise(resolve => { finish = resolve; }); };
    const pending = component.submit();
    await component.submit();
    assert.equal(calls, 1);
    finish({ status: 200, ok: true, json: async () => ({}) });
    await pending;
    assert.equal(reloads(), 1);
});
