import './bootstrap';

import Chart from 'chart.js/auto';
window.Chart = Chart;


import Alpine from 'alpinejs';

import noUiSlider from 'nouislider';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function () {
    const slider = document.getElementById('price-slider');
    const inputMin = document.getElementById('min-price');
    const inputMax = document.getElementById('max-price');

    if (slider) {
        noUiSlider.create(slider, {
            start: [10, 5000],
            connect: true,
            step: 10,
            range: {
                min: 0,
                max: 5000
            },
            format: {
                to: value => Math.round(value),
                from: value => parseInt(value)
            }
        });

        slider.noUiSlider.on('update', function (values, handle) {
            if (handle === 0) inputMin.value = values[0];
            else inputMax.value = values[1];
        });

        inputMin.addEventListener('change', () => {
            slider.noUiSlider.set([inputMin.value, null]);
        });
        inputMax.addEventListener('change', () => {
            slider.noUiSlider.set([null, inputMax.value]);
        });
    }
});