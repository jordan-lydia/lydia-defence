document.addEventListener('DOMContentLoaded', function() {
    const notificationList = document.getElementById('notificationList');
    const markAllAsReadBtn = document.getElementById('markAllAsReadBtn');

    function sendMarkRequest(data) {
        return fetch('api/notification_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        }).then(response => response.json());
    }

    function updateBadgeCount() {
        const badge = document.getElementById('notification-badge');
        if (!badge) return;
        
        let currentCount = parseInt(badge.textContent);
        if (currentCount > 0) {
            currentCount--;
            if (currentCount > 0) {
                badge.textContent = currentCount;
            } else {
                badge.remove();
            }
        }
    }

    if (notificationList) {
        notificationList.addEventListener('click', function(e) {
            const button = e.target.closest('.mark-as-read-btn');
            if (button) {
                const entry = button.closest('.notification-entry');
                const notifId = entry.dataset.notifId;
                
                sendMarkRequest({ action: 'mark_as_read', notif_id: notifId })
                    .then(data => {
                        if (data.success) {
                            entry.classList.remove('unread');
                            entry.classList.add('read');
                            button.remove();
                            updateBadgeCount();
                        }
                    });
            }
        });
    }

    if (markAllAsReadBtn) {
        markAllAsReadBtn.addEventListener('click', function() {
            sendMarkRequest({ action: 'mark_all_as_read' })
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('.notification-entry.unread').forEach(entry => {
                            entry.classList.remove('unread');
                            entry.classList.add('read');
                            entry.querySelector('.mark-as-read-btn')?.remove();
                        });
                        const badge = document.getElementById('notification-badge');
                        if (badge) badge.remove();
                        Swal.fire('Succès', 'Toutes les notifications ont été marquées comme lues.', 'success');
                    }
                });
        });
    }
});