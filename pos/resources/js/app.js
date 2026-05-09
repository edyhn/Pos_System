import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

window.formatRupiah = (value) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
};

const initCharts = () => {
    document.querySelectorAll('[data-chart]').forEach((canvas) => {
        try {
            const config = JSON.parse(canvas.dataset.chart);
            const existing = Chart.getChart(canvas);
            if (existing) existing.destroy();
            new Chart(canvas, config);
        } catch (e) {
            console.warn('Chart init error:', e);
        }
    });
};

document.addEventListener('livewire:initialized', () => {
    initCharts();
    Livewire.hook('morph.updated', () => initCharts());
    Livewire.dispatch('check-notifications');
});
