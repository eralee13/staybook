import { Calendar } from '@fullcalendar/core'
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'
import ruLocale from '@fullcalendar/core/locales/ru'
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const today = new Date();
    const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);

    const calendar = new Calendar(calendarEl, {
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        plugins: [resourceTimelinePlugin],
        locale: ruLocale,
        initialView: 'resourceTimelineMonth',
        initialDate: nextMonth,
        headerToolbar: {
            center: 'customHtml',
            right: 'prev,next'
        },
        customButtons: {
            customHtml: {
                text: 'Забронировать', // скрываем текст
                click: () => {},
            }
        },
        eventRender: function(info) {
            info.el.innerHTML = info.event.title; // Убедись, что FullCalendar отображает HTML
        },
        resourceLabelContent: function(info) {
            // Проверяем, есть ли title в info.resource
            const title = info.resource ? info.resource.title : ''; // Безопасный доступ к title
            
            const titleElement = document.createElement('div');
            
            // Вставляем как HTML
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
                <div style="margin-bottom: 10px;">${title}</div>
                <div style="text-align: right">${price} ${currency}</div>
            `;
        
            return { domNodes: [containerEl] };
        },
        resourceAreaHeaderContent: 'Номер и тариф',
        resources: window.resourcesData, 
        events: window.eventsData,
        height: 'auto',
        nowIndicator: true,
        eventDidMount: function(info) {
            const event = info.event;
            const el = info.el;
        
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
        
        
        
    });

    calendar.render();

    document.getElementById('daterange').addEventListener('change', function (e) {
        const selectedDate = e.target.value;

        if (selectedDate) {
            calendar.gotoDate(selectedDate);

            fetch(`/bookcalendar?date=${selectedDate}`)
                .then(response => response.json())
                .then(data => {
                    calendar.removeAllEvents();
                    calendar.addEventSource(data.events);

                    if (data.resources) {
                        calendar.setOption('resources', data.resources);
                    }
                })
                .catch(error => {
                    console.error('Ошибка загрузки событий:', error);
                });
        }
    });

});
