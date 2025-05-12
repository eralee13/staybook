import { Calendar } from '@fullcalendar/core'
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'
import interactionPlugin from '@fullcalendar/interaction';
import ruLocale from '@fullcalendar/core/locales/ru'
import tippy from 'tippy.js'
import 'tippy.js/dist/tippy.css'
import { Interaction } from '@fullcalendar/core/internal'
import dayjs from 'dayjs'

document.addEventListener('DOMContentLoaded', function () {
    let selectedHotel = '';
    let selectedStart = '';
    let selectedEnd = '';
    const calendarEl = document.getElementById('calendar');
    const today = new Date();
    const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);

    const calendar = new Calendar(calendarEl, {
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        plugins: [resourceTimelinePlugin, interactionPlugin],
        locale: ruLocale,
        initialView: 'resourceTimelineMonth',
        // initialDate: nextMonth,
        headerToolbar: {
            center: '',
            right: ''
        },
        customButtons: {
            customHtml: {
                text: 'Забронировать', // скрываем текст
                click: () => {},
            }
        },
        selectable: true,
        selectMirror: true,
        editable: true,
        slotMinTime: '00:00:00',
        slotMaxTime: '24:00:00',
        timezone: 'Asian/Bishkek',
        eventClick: function(info) {
            // console.log('Event clicked:', info.event);
            const event = info.event;
        
            if (event.backgroundColor == '#39bb43') {
                
                let selectedDate = dayjs(event.startStr).format('DD.MM.YYYY');
        
                $('#modalRateId').val(event.extendedProps.rate_id);
                $('#modalRoomId').val(event.extendedProps.room_id);
                
                $('#createBookingModal').modal('show');

                    // Инициализация daterangepicker
                    $('#modalDateRange').daterangepicker({
                        locale: {
                            format: 'DD.MM.YYYY',
                            separator: ' - ',
                            applyLabel: 'Выбрать',
                            cancelLabel: 'Отмена',
                            daysOfWeek: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
                            monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
                            firstDay: 1
                        },
                        startDate: selectedDate,
                        endDate: selectedDate,
                    });
                }
        },
        eventRender: function(info) {
            info.el.innerHTML = info.event.title; // Убедись, что FullCalendar отображает HTML
        },
        // datesSet: function(info) {
        //     // info.start, info.end — даты текущего видимого диапазона
        //     console.log('Новый диапазон дат:', info.startStr, 'до', info.endStr);
    
        //     // здесь ты можешь сохранить выбранный диапазон в переменные
        //     selectedStart = dayjs(info.startStr).format('YYYY-DD-MM');
        //     selectedEnd = dayjs(info.endStr).format('YYYY-DD-MM');
            
    
        //     // и перезагрузить события
        //     //refetchCalendar();
        // },
        resourceLabelContent: function(info) {
            // Проверяем, есть ли title в info.resource
            const title = info.resource ? info.resource.title : ''; // Безопасный доступ к title
            
            const titleElement = document.createElement('div');
            
            // Вставляем как HTML <div style="text-align: right">${price} ${currency}</div>
            titleElement.innerHTML = title; // Преобразуем строку в HTML
    
            return { domNodes: [titleElement] }; // Возвращаем элементы с HTML-содержимым
        },
        eventContent: function(arg) {
            const { event } = arg;
            const title = event.title;
            const price = event.extendedProps.price || '';
            const currency = event.extendedProps.currency || '';
        
            const containerEl = document.createElement('div');
            containerEl.innerHTML = `
                <div style="padding: 5px; text-align: center;">${title}</div>
            `;
        
            return { domNodes: [containerEl] };
        },
        resourceAreaHeaderContent: 'Номер и тариф',
        resources: window.resourcesData, 
        events: window.eventsData,
        height: 'auto',
        nowIndicator: true,
        eventDidMount: function(info) {
            console.log(info.backgroundColor);
            const event = info.event;
            const el = info.el;
            if (event.backgroundColor == '#d95d5d') {
                const content = `
                    <div style="padding: 4px 8px; font-size: 14px;">
                        <strong>${event.title}</strong><br>
                        ${event.extendedProps.description ?? ''}
                    </div>
                `;
            
                tippy(el, {
                    content: content,
                    allowHTML: true,
                    theme: 'light-border',
                    placement: 'right',
                    // delay: [100, 0], // задержка на показ
                    zIndex: 9999999,
                    // appendTo: document.body,
                });
            }
            
        }
        
        
    });

    document.getElementById('hotel_id').addEventListener('change', function () {
        selectedHotel = this.value;
        let datos  = $('#daterange').val();
        
        if (datos.includes(' - ')) {
            let [start, end] = datos.split(' - ');
            selectedStart = start.trim();
            selectedEnd = end.trim();
        }

        refetchCalendar();
    });
    
    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        let datos = this.value;
        selectedHotel = $('#hotel_id').val();
        
        if (datos.includes(' - ')) {
            let [start, end] = datos.split(' - ');
            selectedStart = start.trim();
            selectedEnd = end.trim();
        }

        refetchCalendar();
    });

    function refetchCalendar() {

        let datos = $('#daterange').val();
        let selectedDate = datos.split(' - ')[0]; // или просто одна дата, зависит от формата

        // Переходим к выбранной дате в календаре
        if (selectedDate) {
            calendar.gotoDate(selectedDate);
        }

        fetch(`/bookcalendar/events?hotel_id=${selectedHotel}&start=${selectedStart}&end=${selectedEnd}`)
            .then(res => res.json())
            .then(data => {
                // console.log(data);
                calendar.refetchResources();
                calendar.refetchEvents();
                calendar.setOption('resources', data.resources);
                calendar.getEventSourceById('main')?.remove();
                calendar.addEventSource({ id: 'main', events: data.events });
            });
    }


    // Обработка формы создания бронирования
    document.getElementById('createBookingForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const [start, end] = $('#modalDateRange').val().split(' - ');
        const allotment = $('#modalAllotment').val();
        const rateId = $('#modalRateId').val();
        const roomId = $('#modalRoomId').val();
        const hotelId = $('#hotel_id').val();

        fetch('/bookcalendar/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                hotel_id: hotelId,
                rate_id: rateId,
                room_id: roomId,
                start: start,
                end: end,
                allotment: allotment
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Успешно: ' + data.message);
                $('#createBookingModal').modal('hide');
                // window.location.reload(); 
                refetchCalendar();
            } else {
                alert('Ошибка: ' + data.message);
            }
        });
    });

    calendar.render();
    
});