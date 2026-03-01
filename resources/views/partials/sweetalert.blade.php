@once
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endonce

<div
    id="app-flash-toast"
    class="hidden"
    data-message="{{ session('message') }}"
    data-error="{{ session('error') }}"
    data-status="{{ session('status') }}"
></div>

<script>
    (() => {
        if (window.__appSwalBooted) {
            return;
        }
        window.__appSwalBooted = true;

        const showToast = (type, message) => {
            if (!message || typeof Swal === 'undefined') {
                return;
            }

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type || 'success',
                title: message,
                showConfirmButton: false,
                timer: 2200,
                timerProgressBar: true,
            });
        };

        const showFlashToast = () => {
            const el = document.getElementById('app-flash-toast');
            if (!el) {
                return;
            }

            const message = (el.dataset.message || '').trim();
            const error = (el.dataset.error || '').trim();
            const status = (el.dataset.status || '').trim();

            const flashKey = [message, error, status].join('|');
            if (!flashKey || window.__lastAppFlashToast === flashKey) {
                return;
            }

            window.__lastAppFlashToast = flashKey;

            if (error) {
                showToast('error', error);
                return;
            }

            if (message) {
                showToast('success', message);
                return;
            }

            if (status) {
                showToast('info', status);
            }
        };

        window.appSwalNotify = (payload) => {
            const data = Array.isArray(payload) ? payload[0] : payload;
            showToast(data?.type || 'success', data?.message || 'Done');
        };

        document.addEventListener('DOMContentLoaded', showFlashToast);
        document.addEventListener('livewire:navigated', showFlashToast);

        document.addEventListener('livewire:init', () => {
            Livewire.on('swal:notify', (payload) => {
                window.appSwalNotify(payload);
            });

            Livewire.on('swal:confirm-delete', async (payload) => {
                const data = Array.isArray(payload) ? payload[0] : payload;

                const result = await Swal.fire({
                    title: data?.title || 'Delete this record?',
                    text: data?.text || 'This action cannot be undone.',
                    icon: data?.icon || 'warning',
                    showCancelButton: true,
                    confirmButtonColor: data?.confirmButtonColor || '#dc2626',
                    cancelButtonColor: data?.cancelButtonColor || '#6b7280',
                    confirmButtonText: data?.confirmButtonText || 'Yes, delete it',
                });

                if (result.isConfirmed) {
                    const eventName = data?.confirmEvent || 'motor-rental-delete-confirmed';
                    Livewire.dispatch(eventName, { id: data?.id });
                }
            });
        });
    })();
</script>
