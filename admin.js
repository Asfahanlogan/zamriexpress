const loginView = document.querySelector('#adminLoginView');
const dashboardView = document.querySelector('#adminDashboardView');
const loginForm = document.querySelector('#adminLoginForm');
const loginError = document.querySelector('#adminLoginError');
const logoutBtn = document.querySelector('#adminLogoutBtn');
const ordersBody = document.querySelector('#adminOrdersBody');
const flash = document.querySelector('#adminFlash');

function show(el) {
    if (el) el.hidden = false;
}

function hide(el) {
    if (el) el.hidden = true;
}

function setError(message) {
    if (!loginError) return;
    loginError.textContent = message;
    show(loginError);
}

function clearError() {
    if (!loginError) return;
    loginError.textContent = '';
    hide(loginError);
}

function setFlash(message) {
    if (!flash) return;
    flash.textContent = message;
    show(flash);
    window.setTimeout(() => hide(flash), 2400);
}

async function api(action, { method = 'GET', body } = {}) {
    const opts = { method, credentials: 'same-origin' };
    if (body) {
        opts.headers = { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' };
        opts.body = new URLSearchParams(body).toString();
    }

    const url = `api_admin.php?action=${encodeURIComponent(action)}`;
    const res = await fetch(url, opts);
    const data = await res.json().catch(() => null);

    if (!data || data.ok !== true) {
        const err = data?.error || `http_${res.status}`;
        const e = new Error(err);
        e.status = res.status;
        throw e;
    }

    return data;
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = String(value ?? '');
    return div.innerHTML;
}

function formatPackage(text) {
    return escapeHtml(text).replace(/\n/g, '<br>');
}

function renderOrders(orders) {
    if (!ordersBody) return;

    if (!orders || orders.length === 0) {
        ordersBody.innerHTML = '<tr><td colspan="7">No delivery requests yet.</td></tr>';
        return;
    }

    ordersBody.innerHTML = orders
        .map((o) => {
            const isRead = Number(o.is_read) === 1;
            const statusClass = isRead ? 'status-read' : 'status-new';
            const statusLabel = isRead ? 'Read' : 'New';

            const markReadBtn = isRead
                ? ''
                : `<button type="button" class="action-btn read" data-action="mark_read" data-id="${escapeHtml(o.id)}">Mark as Read</button>`;

            const deleteBtn = `<button type="button" class="action-btn delete" data-action="delete" data-id="${escapeHtml(o.id)}">Delete</button>`;

            return `
                <tr>
                    <td><span class="status-badge ${statusClass}">${statusLabel}</span></td>
                    <td>${escapeHtml(o.name)}</td>
                    <td>${escapeHtml(o.phone)}<br>${escapeHtml(o.email)}</td>
                    <td>${escapeHtml(o.destination)}</td>
                    <td>${formatPackage(o.package_description)}</td>
                    <td>${escapeHtml(o.created_at)}</td>
                    <td>${markReadBtn} ${deleteBtn}</td>
                </tr>
            `;
        })
        .join('');
}

async function refreshOrders() {
    const data = await api('list');
    renderOrders(data.orders);
}

async function boot() {
    try {
        const status = await api('status');
        if (status.logged_in) {
            hide(loginView);
            show(dashboardView);
            await refreshOrders();
        } else {
            hide(dashboardView);
            show(loginView);
        }
    } catch {
        hide(dashboardView);
        show(loginView);
    }
}

if (loginForm) {
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearError();
        const formData = new FormData(loginForm);
        const username = String(formData.get('username') || '');
        const password = String(formData.get('password') || '');

        try {
            await api('login', { method: 'POST', body: { username, password } });
            hide(loginView);
            show(dashboardView);
            await refreshOrders();
        } catch {
            setError('Invalid username or password.');
        }
    });
}

if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
        try {
            await api('logout', { method: 'POST', body: {} });
        } finally {
            hide(dashboardView);
            show(loginView);
        }
    });
}

if (ordersBody) {
    ordersBody.addEventListener('click', async (event) => {
        const btn = event.target.closest('button[data-action][data-id]');
        if (!btn) return;

        const action = btn.getAttribute('data-action');
        const id = btn.getAttribute('data-id');
        if (!action || !id) return;

        if (action === 'delete') {
            // eslint-disable-next-line no-alert
            const ok = confirm('Delete this order?');
            if (!ok) return;
        }

        try {
            await api(action, { method: 'POST', body: { id } });
            setFlash(action === 'delete' ? 'Order deleted.' : 'Order marked as read.');
            await refreshOrders();
        } catch (e) {
            if (e.status === 401) {
                hide(dashboardView);
                show(loginView);
                return;
            }
            setFlash('Action failed. Please try again.');
        }
    });
}

boot();

