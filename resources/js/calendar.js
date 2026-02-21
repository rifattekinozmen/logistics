import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import trLocale from '@fullcalendar/core/locales/tr';

/* FullCalendar v6 injects CSS via JavaScript - no manual import needed */

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    let currentFilter = 'all';

    const calendar = new Calendar(calendarEl, {
        plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
        locale: trLocale,
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        buttonText: {
            today: 'Bugün',
            month: 'Ay',
            week: 'Hafta',
            list: 'Liste'
        },
        height: 'auto',
        navLinks: true,
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        
        events: function(info, successCallback, failureCallback) {
            fetch(`/admin/calendar/events?start=${info.startStr}&end=${info.endStr}`)
                .then(response => response.json())
                .then(events => {
                    const filteredEvents = currentFilter === 'all' 
                        ? events 
                        : events.filter(e => e.extendedProps?.event_type === currentFilter);
                    successCallback(filteredEvents);
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },

        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;
            
            let modalContent = `
                <div class="modal fade" id="eventDetailModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: ${event.backgroundColor}; color: white;">
                                <h5 class="modal-title">${event.title}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                ${props.description ? `<p class="mb-3">${props.description}</p>` : ''}
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Tip:</strong><br>
                                        <span class="badge bg-secondary">${getEventTypeLabel(props.event_type)}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Öncelik:</strong><br>
                                        <span class="badge bg-${getPriorityBadge(props.priority)}">${getPriorityLabel(props.priority)}</span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <strong>Başlangıç:</strong><br>
                                        ${formatDate(event.start)}
                                    </div>
                                    ${event.end ? `
                                    <div class="col-6">
                                        <strong>Bitiş:</strong><br>
                                        ${formatDate(event.end)}
                                    </div>
                                    ` : ''}
                                </div>
                                ${props.related_type ? `
                                <hr>
                                <p class="mb-0">
                                    <strong>İlişkili:</strong> ${props.related_type} #${props.related_id}
                                </p>
                                ` : ''}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                <button type="button" class="btn btn-danger" onclick="deleteEvent(${event.id})">
                                    <i class="bi bi-trash"></i> Sil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existingModal = document.getElementById('eventDetailModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            document.body.insertAdjacentHTML('beforeend', modalContent);
            const modal = new bootstrap.Modal(document.getElementById('eventDetailModal'));
            modal.show();
            
            document.getElementById('eventDetailModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        },

        select: function(info) {
            const modal = new bootstrap.Modal(document.getElementById('addEventModal'));
            document.querySelector('#addEventModal input[name="start_date"]').value = info.startStr;
            document.querySelector('#addEventModal input[name="end_date"]').value = info.endStr;
            modal.show();
        },

        eventDrop: function(info) {
            updateEvent(info.event);
        },

        eventResize: function(info) {
            updateEvent(info.event);
        }
    });

    calendar.render();

    const filterTabs = document.querySelectorAll('#eventFilterTabs .nav-link');
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            calendar.refetchEvents();
        });
    });

    const addEventForm = document.getElementById('addEventForm');
    if (addEventForm) {
        addEventForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            data.is_all_day = formData.has('is_all_day');
            
            try {
                const response = await fetch('/admin/calendar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
                
                if (response.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('addEventModal')).hide();
                    addEventForm.reset();
                    calendar.refetchEvents();
                    showToast('Etkinlik başarıyla eklendi', 'success');
                } else {
                    const error = await response.json();
                    showToast(error.message || 'Bir hata oluştu', 'danger');
                }
            } catch (error) {
                console.error('Error creating event:', error);
                showToast('Bir hata oluştu', 'danger');
            }
        });
    }

    window.deleteEvent = async function(eventId) {
        if (!confirm('Bu etkinliği silmek istediğinize emin misiniz?')) return;
        
        try {
            const response = await fetch(`/admin/calendar/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            if (response.ok) {
                bootstrap.Modal.getInstance(document.getElementById('eventDetailModal')).hide();
                calendar.refetchEvents();
                showToast('Etkinlik silindi', 'success');
            } else {
                showToast('Silme işlemi başarısız', 'danger');
            }
        } catch (error) {
            console.error('Error deleting event:', error);
            showToast('Bir hata oluştu', 'danger');
        }
    };

    async function updateEvent(event) {
        const data = {
            title: event.title,
            start_date: event.startStr,
            end_date: event.endStr || event.startStr,
            is_all_day: event.allDay
        };
        
        try {
            const response = await fetch(`/admin/calendar/${event.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });
            
            if (response.ok) {
                showToast('Etkinlik güncellendi', 'success');
            } else {
                showToast('Güncelleme başarısız', 'danger');
                calendar.refetchEvents();
            }
        } catch (error) {
            console.error('Error updating event:', error);
            showToast('Bir hata oluştu', 'danger');
            calendar.refetchEvents();
        }
    }

    function getEventTypeLabel(type) {
        const labels = {
            meeting: 'Toplantı',
            document: 'Belge',
            payment: 'Ödeme',
            maintenance: 'Bakım',
            inspection: 'Muayene',
            leave: 'İzin',
            delivery: 'Teslimat',
            other: 'Diğer'
        };
        return labels[type] || type;
    }

    function getPriorityLabel(priority) {
        const labels = {
            low: 'Düşük',
            medium: 'Orta',
            high: 'Yüksek'
        };
        return labels[priority] || priority;
    }

    function getPriorityBadge(priority) {
        const badges = {
            low: 'success',
            medium: 'warning',
            high: 'danger'
        };
        return badges[priority] || 'secondary';
    }

    function formatDate(date) {
        if (!date) return '';
        return new Date(date).toLocaleDateString('tr-TR', {
            day: '2-digit',
            month: 'long',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
});
