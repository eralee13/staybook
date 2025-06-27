import { Calendar } from '@fullcalendar/core'
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'
import interactionPlugin from '@fullcalendar/interaction'
import ruLocale from '@fullcalendar/core/locales/ru'
import tippy from 'tippy.js'
import 'tippy.js/dist/tippy.css'

let isRefetching = false;
let selectedHotel = '';
let selectedStart = '';
let selectedEnd = '';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new Calendar(calendarEl, {
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        plugins: [resourceTimelinePlugin, interactionPlugin],
        locale: ruLocale,
        initialView: 'resourceTimelineMonth',
        initialDate: new Date().toISOString().split('T')[0],
        validRange: {
            start: new Date().toISOString().split('T')[0]
        },
        resourceAreaHeaderContent: 'Номера / Тарифы',
        nowIndicator: true,
        height: 'auto',
        selectable: true,
        editable: false,

        resources: window.resourcesData ?? [],
        events: window.eventsData ?? [],

        eventContent: function(arg) {
            const title = arg.event?.title ?? '';
            return {
                html: `<div style="padding: 5px; text-align: center; font-weight: bold">${title}</div>`
            };
        },

        eventClick: function(info) {
            const event = info.event;
            const date = event.startStr.split('T')[0];

            const hotelId = $('#hotel_id').val();
            const hotelName = $('#hotel_id option:selected').text();

            const resourceId = info.event.getResources?.()[0]?.id ?? info.event._def.resourceIds?.[0] ?? '';

            let roomId = '';
            let rateId = '';

            if (resourceId && resourceId.includes('_rate_')) {
                const [roomPart, ratePart] = resourceId.replace('room_', '').split('_rate_');
                roomId = roomPart;
                rateId = ratePart;
            }

            const roomResource = calendar.getResourceById(`room_${roomId}`);
            const rateResource = calendar.getResourceById(`room_${roomId}_rate_${rateId}`);

            $('#modalHotelId').val(hotelId);
            $('#modalHotelName').text(hotelName);
            $('#modalRoomId').val(roomId);
            $('#modalRateId').val(rateId);
            $('#modalRoomName').text(roomResource?.title ?? '—');
            $('#modalRateName').text(rateResource?.title ?? '—');
            $('#modalDateRange').val(`${formatDate(date)} - ${formatDate(date)}`);

            $('#bookingError').addClass('d-none').html('');

            $('#modalDateRange').data('daterangepicker')?.remove();

            $('#modalDateRange').daterangepicker({
                singleDatePicker: false,
                showDropdowns: true,
                autoApply: true,
                startDate: formatDate(date),
                endDate: formatDate(date),
                locale: {
                    format: 'DD.MM.YYYY',
                    separator: ' - ',
                    applyLabel: 'Выбрать',
                    cancelLabel: 'Отмена',
                    fromLabel: 'С',
                    toLabel: 'По',
                    customRangeLabel: 'Выбрать вручную',
                    weekLabel: 'Н',
                    daysOfWeek: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
                    monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
                    firstDay: 1
                }
            });

            $('#createBookingModal').modal('show');
        },

        eventDidMount: function(info) {
            const event = info.event;
            const el = info.el;
            const color = event.backgroundColor || event._def?.ui?.backgroundColor;
            const title = event.title;

            if (event.extendedProps.description && color === '#d95d5d' && event.extendedProps.open_time) {
                const open = event.extendedProps.open_time ?? '—';
                const close = event.extendedProps.close_time ?? '—';

                const priceText = event.title;
                const content = `
                <div style="padding: 4px 8px; font-size: 14px;">
                    <strong>Цена:</strong> ${priceText}<br>
                    <strong>Открытие:</strong> ${open}<br>
                    <strong>Закрытие:</strong> ${close}
                </div>`;
                tippy(el, {
                    content: `
                        <div style="padding: 4px 8px; font-size: 14px;">
                            <strong>${title}</strong><br>
                            ${event.extendedProps.description}
                        </div>
                    `,
                    allowHTML: true,
                    theme: 'light-border',
                    placement: 'right',
                    zIndex: 999999,
                });
            }

            if (color) {
                el.style.backgroundColor = color;
                el.style.borderColor = color;
            }
        },

        datesSet: function(info) {
            if (!isRefetching) {
                selectedStart = info.startStr.split('T')[0];
                selectedEnd = info.endStr.split('T')[0];
                refetchCalendar(calendar);
            }
        }
    });

    calendar.render();

    function formatDate(dateStr) {
        const [y, m, d] = dateStr.split('-');
        return `${d}.${m}.${y}`;
    }

    function refetchCalendar(calendar) {
        if (isRefetching) return;
        isRefetching = true;

        let datos = $('#daterange').val();
        selectedHotel = $('#hotel_id').val();

        if (datos && datos.includes(' - ')) {
            const parts = datos.split(' - ');
            selectedStart = parts[0].trim().split('.').reverse().join('-');
            selectedEnd = parts[1].trim().split('.').reverse().join('-');
        } else {
            const today = new Date().toISOString().split('T')[0];
            selectedStart = today;
            selectedEnd = today;
        }

        fetch(`/auth/bookcalendar/books/events?hotel_id=${selectedHotel}&start=${selectedStart}&end=${selectedEnd}`)
            .then(res => res.json())
            .then(data => {
                calendar.removeAllEventSources();
                calendar.setOption('resources', data.resources);
                calendar.addEventSource(data.events);
            })
            .catch(err => {
                console.error('Ошибка загрузки:', err);
                showToast('Ошибка загрузки календаря', 'danger');
            })
            .finally(() => {
                isRefetching = false;
            });
    }

    $('#createBookingModal').on('show.bs.modal', function () {
        $('#bookingError').addClass('d-none').html('');
    });

    document.getElementById('createBookingForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const range = $('#modalDateRange').val() ?? '';
        console.log('Диапазон:', range);
        const [start, end] = range.includes(' - ') ? range.split(' - ') : [null, null];

        if (!start || !end || start.length < 6 || end.length < 6) {
            showBookingError('Выберите корректный диапазон дат.');
            return;
        }

        const payload = {
            hotel_id: $('#modalHotelId').val(),
            rate_id: $('#modalRateId').val(),
            room_id: $('#modalRoomId').val(),
            start: start.trim().split('.').reverse().join('-'),
            end: end.trim().split('.').reverse().join('-'),
            allotment: $('#modalAllotment').val()
        };

        fetch('/auth/bookcalendar/books/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    $('#createBookingModal').modal('hide');
                    showToast('Квота обновлена', 'success');
                    refetchCalendar(calendar);
                } else if (data.error) {
                    showToast(data.message || 'Ошибка при обновлении квоты', 'danger');
                }
            })
            .catch(() => {
                showToast('Ошибка соединения с сервером.', 'danger');
            });
    });

    function showBookingError(message) {
        const $error = $('#bookingError');
        $error.removeClass('d-none').html(message);
        setTimeout(() => {
            $error.addClass('d-none').html('');
        }, 5000);
    }

    function showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.className = 'toast-message';
        Object.assign(toast.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            backgroundColor: type === 'danger' ? '#dc3545' : '#28a745',
            color: 'white',
            padding: '10px 20px',
            borderRadius: '6px',
            zIndex: 10000,
            boxShadow: '0 0 10px rgba(0,0,0,0.15)',
            fontSize: '15px'
        });
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }

    refetchCalendar(calendar);

    document.getElementById('hotel_id').addEventListener('change', () => {
        refetchCalendar(calendar);
    });

    $('#daterange').on('apply.daterangepicker', function () {
        refetchCalendar(calendar);
    });
});