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
        initialDate: new Date().toISOString().split('T')[0], // сегодня
        resourceAreaHeaderContent: 'Номера / Тарифы',
        nowIndicator: true,
        height: 'auto',
        selectable: true,
        editable: true,

        resources: window.resourcesData ?? [],
        events: window.eventsData ?? [],

        eventContent: function(arg) {
            const title = arg.event?.title ?? '';
            return {
                html: `<div style="padding: 5px; text-align: center; font-weight: bold">${title}</div>`
            };
        },

        eventClick: function(info) {
            // Получаем ID и текст выбранного отеля
            const hotelSelect = document.getElementById('hotel_id');
            const hotelName = hotelSelect.options[hotelSelect.selectedIndex].text;

            // Устанавливаем в форму
            $('#modalHotelName').text(hotelName);

            const event = info.event;
            selectedHotel = $('#hotel_id').val();

            const date = event.startStr.split('T')[0]; // ← YYYY-MM-DD
            const formatted = formatDate(date); // → DD.MM.YYYY

            // Заполняем модальное окно
            $('#modalHotelId').val(selectedHotel);
            $('#modalRoomId').val(event.extendedProps.room_id);
            $('#modalRateId').val(event.extendedProps.rate_id);
            $('#modalDateRange').val(`${formatted} - ${formatted}`);
            $('#createBookingModal').modal('show');
        },

        eventDidMount: function(info) {
            const event = info.event;
            if (event.extendedProps.description && event.backgroundColor === '#d95d5d') {
                tippy(info.el, {
                    content: `
                        <div style="padding: 4px 8px; font-size: 14px;">
                            <strong>${event.title}</strong><br>
                            ${event.extendedProps.description}
                        </div>
                    `,
                    allowHTML: true,
                    theme: 'light-border',
                    placement: 'right',
                    zIndex: 999999,
                });
            }
        },

        datesSet: function(info) {
            if (!isRefetching) {
                console.log('[datesSet]', info.startStr, '→', info.endStr);
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

        if (datos.includes(' - ')) {
            const parts = datos.split(' - ');
            selectedStart = parts[0].trim().split('.').reverse().join('-'); // DD.MM.YYYY → YYYY-MM-DD
            selectedEnd = parts[1].trim().split('.').reverse().join('-');
        }

        fetch(`/auth/bookcalendar/books/events?hotel_id=${selectedHotel}&start=${selectedStart}&end=${selectedEnd}`)
            .then(res => res.json())
            .then(data => {
                console.log('[refetchCalendar]', data.events?.length ?? 0, 'событий');
                calendar.removeAllEventSources();
                calendar.setOption('resources', data.resources);
                calendar.addEventSource(data.events);
            })
            .catch(err => console.error('Ошибка загрузки:', err))
            .finally(() => {
                isRefetching = false;
            });
    }

    // первый запуск
    refetchCalendar(calendar);

    // при смене отеля
    document.getElementById('hotel_id').addEventListener('change', () => {
        refetchCalendar(calendar);
    });

    // при выборе дат
    $('#daterange').on('apply.daterangepicker', function () {
        refetchCalendar(calendar);
    });
});
