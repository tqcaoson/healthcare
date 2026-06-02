export default () => ({

    show: false,
    countdown: 60,
    _idleTimer: null,
    _countdownTimer: null,

    init() {
        this._resetTimer();
        ['mousemove', 'keydown', 'click', 'touchstart', 'scroll'].forEach(event =>
            document.addEventListener(event, () => this._resetTimer(), { passive: true })
        );
    },

    _resetTimer() {
        clearTimeout(this._idleTimer);
        clearInterval(this._countdownTimer);
        this.show = false;

        const idleMs = (window.sessionIdleMinutes ?? 14) * 60 * 1000;

        this._idleTimer = setTimeout(() => {
            this.show = true;
            this.countdown = 60;

            this._countdownTimer = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    clearInterval(this._countdownTimer);
                    this.logout();
                }
            }, 1000);
        }, idleMs);
    },

    extend() {
        this._resetTimer();
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrf) {
            fetch('/extend-session', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
            }).catch(() => {});
        }
    },

    logout() {
        const form = document.getElementById('logout-form');
        if (form) {
            form.submit();
        } else {
            window.location.href = '/logout';
        }
    },
});
