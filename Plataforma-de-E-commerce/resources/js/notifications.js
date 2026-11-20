export default (indexUrl, readAllUrl) => ({
    open: false,
    count: 0,
    notifications: [],
    loading: false,
    busy: false,
    error: '',
    toast: false,
    timer: null,
    toastTimer: null,

    init() {
        this.refresh();
        this.timer = setInterval(() => {
            if (!document.hidden) this.refresh();
        }, 30000);
    },

    destroy() {
        clearInterval(this.timer);
        clearTimeout(this.toastTimer);
    },

    async request(url, method = 'GET') {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        });
        if (!response.ok) throw new Error('Não foi possível atualizar as notificações.');
        return response.json();
    },

    apply(data) {
        const previousCount = this.count;
        this.count = data.unread_count;
        this.notifications = data.notifications;
        this.error = '';
        if (this.count > previousCount && !this.open) {
            this.toast = true;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => { this.toast = false; }, 6000);
        }
        if (this.count === 0) this.toast = false;
    },

    async refresh() {
        if (this.loading || this.busy) return;
        this.loading = true;
        try {
            this.apply(await this.request(indexUrl));
        } catch {
            this.error = 'Não foi possível carregar as notificações.';
        } finally {
            this.loading = false;
        }
    },

    toggle() {
        this.open = !this.open;
        this.toast = false;
        if (this.open) this.refresh();
    },

    async read(notification) {
        if (this.busy) return;
        this.busy = true;
        try {
            this.apply(await this.request(notification.read_url, 'POST'));
            window.location.assign(notification.url);
        } catch {
            this.error = 'Não foi possível abrir a notificação. Tenta novamente.';
        } finally {
            this.busy = false;
        }
    },

    async readAll() {
        if (this.busy) return;
        this.busy = true;
        try {
            this.apply(await this.request(readAllUrl, 'POST'));
        } catch {
            this.error = 'Não foi possível marcar as notificações como lidas.';
        } finally {
            this.busy = false;
        }
    },
});
