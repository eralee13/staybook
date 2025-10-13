// resources/js/bookcalendarprice.js
import { Calendar } from '@fullcalendar/core'
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'
import interactionPlugin from '@fullcalendar/interaction'
import ruLocale from '@fullcalendar/core/locales/ru'
import tippy from 'tippy.js'
import 'tippy.js/dist/tippy.css'

let isRefetching = false
let selectedHotel = ''
let selectedStart = ''
let selectedEnd = ''

// ------------------- helpers -------------------

const toYMD = (v) => {
    if (!v) return ''
    if (v instanceof Date) {
        const y = v.getFullYear()
        const m = String(v.getMonth() + 1).padStart(2, '0')
        const d = String(v.getDate()).padStart(2, '0')
        return `${y}-${m}-${d}`
    }
    const dt = new Date(v)
    const y = dt.getFullYear()
    const m = String(dt.getMonth() + 1).padStart(2, '0')
    const d = String(dt.getDate()).padStart(2, '0')
    return `${y}-${m}-${d}`
}

const ymdToDate = (s) => {
    const [y, m, d] = s.split('-').map(Number)
    return new Date(y, m - 1, d)
}

const addDays = (ymd, n = 1) => {
    const [y, m, d] = ymd.split('-').map(Number)
    const dt = new Date(y, m - 1, d)
    dt.setDate(dt.getDate() + n)
    const yy = dt.getFullYear()
    const mm = String(dt.getMonth() + 1).padStart(2, '0')
    const dd = String(dt.getDate()).padStart(2, '0')
    return `${yy}-${mm}-${dd}`
}

const toDMY = (ymd) => {
    const [y, m, d] = ymd.split('-')
    return `${d}.${m}.${y}`
}

// идентификаторы из resourceId
const extractRoom = (rid) => (String(rid || '').match(/room_(\d+)/)?.[1] ?? '')
const extractRate = (rid) => (String(rid || '').match(/rate_(\d+(?:_p[1-4])?)/)?.[1] ?? '')

// ВСЕГДА работает с ИНКЛЮЗИВНЫМИ датами
function putDatesInc(startYMD, endInclusiveYMD) {
    // hidden
    window.$('#start').val(startYMD)
    window.$('#end').val(endInclusiveYMD)

    // поле
    const same = startYMD === endInclusiveYMD
    const text = same ? `${toDMY(startYMD)}` : `${toDMY(startYMD)} - ${toDMY(endInclusiveYMD)}`
    const $range = window.$('#modalDateRange')
    $range.val(text)

    // sync с DRP
    const drp = $range.data('daterangepicker')
    if (drp) {
        drp.setStartDate(toDMY(startYMD))
        drp.setEndDate(toDMY(endInclusiveYMD))
    }
}

// ------------------- openModalFilled (ожидает ИНКЛЮЗИВНЫЙ конец!) -------------------

function openModalFilled({ hotelId, hotelName, resource, startIncYMD, endIncYMD }) {
    const rid    = resource?.id ?? ''
    const roomId = extractRoom(rid)
    const rateId = extractRate(rid)

    window.$('#modalHotelId').val(hotelId)
    window.$('#modalHotelName').text(hotelName)
    window.$('#modalRoomId').val(roomId)
    window.$('#modalRateId').val(rateId)

    // названия
    const roomResId = `room_${roomId}`
    const rateResId = `room_${roomId}_rate_${rateId}`
    window.$('#modalRoomName').text(resource?.title ?? '—')
    window.$('#modalRateName').text(resource?.title ?? '—')

    // один раз выставили инклюзивные даты
    putDatesInc(startIncYMD, endIncYMD)

    // DRP без авто-апдейта инпута
    window.$('#modalDateRange').data('daterangepicker')?.remove()
    window.$('#modalDateRange').daterangepicker({
        singleDatePicker: false,
        showDropdowns: true,
        autoApply: true,
        autoUpdateInput: false,
        startDate: toDMY(startIncYMD),
        endDate: toDMY(endIncYMD),
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
    }, function (start, end) {
        // DRP даёт ИНКЛЮЗИВНЫЕ
        const sInc = start.format('YYYY-MM-DD')
        const eInc = end.format('YYYY-MM-DD')
        putDatesInc(sInc, eInc)
    })

    // страховочно ещё раз руками (гарантия одной даты при первом открытии)
    putDatesInc(startIncYMD, endIncYMD)

    new window.bootstrap.Modal(document.getElementById('createBookingModal')).show()
}

// ------------------- Calendar -------------------

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar')
    if (!calendarEl) return

    const calendar = new Calendar(calendarEl, {
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        plugins: [resourceTimelinePlugin, interactionPlugin],
        timeZone: 'local',
        locale: ruLocale,

        // корректные типы view
        initialView: 'resourceTimelineTwoMonths',
        views: {
            resourceTimelineTwoMonths: {
                type: 'resourceTimeline',
                duration: { months: 2 },
                slotDuration: { days: 1 },
                buttonText: '2 месяца'
            }
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'resourceTimelineMonth,resourceTimelineTwoMonths'
        },

        validRange: { start: toYMD(new Date()) },
        slotMinWidth: 80,
        resourceAreaHeaderContent: 'Номера / Тарифы',
        height: 'auto',
        selectable: true,
        editable: false,
        nowIndicator: true,

        resources: window.resourcesData ?? [],
        events: window.eventsData ?? [],

        eventContent(arg) {
            const title = arg.event?.title ?? ''
            return { html: `<div style="padding:5px;text-align:center;font-weight:500">${title}</div>` }
        },

        // клик по событию: конвертируем FC-end (эксклюзив) -> инклюзив
        eventClick(info) {
            const hotelId = window.$('#hotel_id').val()
            const hotelName = window.$('#hotel_id option:selected').text()

            const startInc = toYMD(info.event.start)
            const endInc = info.event.end ? addDays(toYMD(info.event.end), -1) : startInc

            const res = info.event.getResources?.()[0] || null
            openModalFilled({ hotelId, hotelName, resource: res, startIncYMD: startInc, endIncYMD: endInc })
        },

        // клик по пустой ячейке -> один день
        dateClick(info) {
            const hotelId = window.$('#hotel_id').val()
            const hotelName = window.$('#hotel_id option:selected').text()

            const startInc = toYMD(info.date)
            const endInc = startInc

            openModalFilled({ hotelId, hotelName, resource: info.resource, startIncYMD: startInc, endIncYMD: endInc })
        },

        // выделение диапазона: info.end эксклюзив -> делаем инклюзив
        select(info) {
            const hotelId = window.$('#hotel_id').val()
            const hotelName = window.$('#hotel_id option:selected').text()

            const startInc = toYMD(info.start)
            const endInc = addDays(toYMD(info.end), -1)

            openModalFilled({ hotelId, hotelName, resource: info.resource, startIncYMD: startInc, endIncYMD: endInc })
        },

        eventDidMount(info) {
            const event = info.event
            const el = info.el
            const color = event.backgroundColor || event._def?.ui?.backgroundColor
            const title = event.title

            if (event.extendedProps.description && color === '#d95d5d') {
                tippy(el, {
                    content: `<div style="padding:4px 8px;font-size:14px;"><strong>${title}</strong><br>${event.extendedProps.description}</div>`,
                    allowHTML: true,
                    theme: 'light-border',
                    placement: 'right',
                    zIndex: 999999
                })
            }
            if (color) {
                el.style.backgroundColor = color
                el.style.borderColor = color
            }
        },

        datesSet(info) {
            if (!isRefetching) {
                selectedStart = info.startStr.split('T')[0]
                selectedEnd = info.endStr.split('T')[0]
                refetchCalendar(calendar)
            }
        }
    })

    calendar.render()

    // ------------------- reload -------------------
    function refetchCalendar(cal) {
        if (isRefetching) return
        isRefetching = true
        selectedHotel = window.$('#hotel_id').val()

        fetch(`/auth/bookcalendarprice/books/events?hotel_id=${selectedHotel}&start=${selectedStart}&end=${selectedEnd}`)
            .then(res => res.json())
            .then(data => {
                cal.removeAllEventSources()
                cal.setOption('resources', data.resources || [])
                cal.addEventSource(data.events || [])
            })
            .catch(() => showToast('Ошибка загрузки календаря', 'danger'))
            .finally(() => { isRefetching = false })
    }

    // ------------------- submit -------------------
    document.getElementById('createBookingForm')?.addEventListener('submit', async (e) => {
        e.preventDefault()

        // отправляем ИНКЛЮЗИВНЫЕ даты (ровно то, что видит пользователь)
        const startInc = window.$('#start').val()
        const endInc   = window.$('#end').val()

        const payload = {
            hotel_id: window.$('#modalHotelId').val(),
            rate_id:  window.$('#modalRateId').val(), // "5" или "5_p4" — ок
            room_id:  window.$('#modalRoomId').val(),
            start:    startInc,
            end:      endInc
        }

        // только заполненные цены — чтобы не затирать другие
        const p1 = window.$('#price1').val(); if (p1 !== '' && p1 != null) payload.price  = Number(p1)
        const p2 = window.$('#price2').val(); if (p2 !== '' && p2 != null) payload.price2 = Number(p2)
        const p3 = window.$('#price3').val(); if (p3 !== '' && p3 != null) payload.price3 = Number(p3)
        const p4 = window.$('#price4').val(); if (p4 !== '' && p4 != null) payload.price4 = Number(p4)

        try {
            const resp = await fetch('/auth/bookcalendarprice/books/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            })
            const data = await resp.json()

            if (!resp.ok || data.error) {
                showToast(data.message || 'Ошибка при сохранении', 'danger')
                return
            }

            // закрываем модалку стабильно
            const modalEl = document.getElementById('createBookingModal')
            window.bootstrap.Modal.getOrCreateInstance(modalEl).hide()

            // чистим цены (необязательно)
            window.$('#price1,#price2,#price3,#price4').val('')

            showToast('Сохранено', 'success')
            refetchCalendar(calendar)
        } catch (err) {
            console.error(err)
            showToast('Сетевая ошибка', 'danger')
        }
    })

    // ------------------- toast helper -------------------
    function showToast(message, type = 'success') {
        const toast = document.createElement('div')
        toast.textContent = message
        toast.className = 'toast-message'
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
        })
        document.body.appendChild(toast)
        setTimeout(() => toast.remove(), 3500)
    }

    document.getElementById('hotel_id')?.addEventListener('change', () => refetchCalendar(calendar))
})