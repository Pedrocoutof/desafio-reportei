<script setup>
import { onMounted } from 'vue';
import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';

onMounted(() => {
    const formatThousands = (value) => Intl.NumberFormat('en-US', {
        maximumSignificantDigits: 3,
        notation: 'compact',
    }).format(value);

    Chart.defaults.font.family = '"Inter", sans-serif';
    Chart.defaults.font.weight = '500';
    Chart.defaults.color = 'rgb(148, 163, 184)';
    Chart.defaults.scale.grid.color = 'rgb(241, 245, 249)';
    Chart.defaults.plugins.tooltip.titleColor = 'rgb(30, 41, 59)';
    Chart.defaults.plugins.tooltip.bodyColor = 'rgb(30, 41, 59)';
    Chart.defaults.plugins.tooltip.backgroundColor = '#FFF';
    Chart.defaults.plugins.tooltip.borderWidth = 0.5;
    Chart.defaults.plugins.tooltip.borderColor = 'rgb(226, 232, 240)';
    Chart.defaults.plugins.tooltip.displayColors = false;
    Chart.defaults.plugins.tooltip.mode = 'nearest';
    Chart.defaults.plugins.tooltip.intersect = false;
    Chart.defaults.plugins.tooltip.position = 'nearest';
    Chart.defaults.plugins.tooltip.caretSize = 0;
    Chart.defaults.plugins.tooltip.caretPadding = 20;
    Chart.defaults.plugins.tooltip.cornerRadius = 4;
    Chart.defaults.plugins.tooltip.padding = 8;

    const ctx = document.getElementById('chart');
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                '12-01-2020', '01-01-2021', '02-01-2021',
                '03-01-2021', '04-01-2021', '05-01-2021',
                '06-01-2021', '07-01-2021', '08-01-2021',
                '09-01-2021', '10-01-2021', '11-01-2021',
                '12-01-2021', '01-01-2022', '02-01-2022',
                '03-01-2022', '04-01-2022', '05-01-2022',
                '06-01-2022', '07-01-2022', '08-01-2022',
                '09-01-2022', '10-01-2022', '11-01-2022',
                '12-01-2022', '01-01-2023',
            ],
            datasets: [
                // Indigo line
                {
                    label: 'Commits',
                    data: [
                        5000, 8700, 7500, 12000, 11000, 9500, 10500,
                        10000, 15000, 9000, 10000, 7000, 22000, 7200,
                        9800, 9000, 10000, 8000, 15000, 12000, 11000,
                        13000, 11000, 15000, 17000, 18000,
                    ],
                    fill: true,
                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 2,
                    tension: 0,
                    pointRadius: 0,
                    pointHoverRadius: 3,
                    pointBackgroundColor: 'rgb(99, 102, 241)',
                },
            ],
        },
        options: {
            layout: {
                padding: 20,
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                    },
                    ticks: {
                        callback: (value) => formatThousands(value),
                    },
                },
                x: {
                    type: 'time',
                    time: {
                        parser: 'MM-dd-yyyy',
                        unit: 'month',
                        displayFormats: {
                            month: 'MMM yy',
                        },
                    },
                    grid: {
                        display: false,
                        drawBorder: false,
                    },
                    ticks: {
                        autoSkipPadding: 48,
                        maxRotation: 0,
                    },
                },
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    align: 'end',
                    display: true,
                    labels: {
                        color: 'rgb(99, 102, 241)'
                    }
                },
                tooltip: {
                    callbacks: {
                        title: () => false,
                        label: (context) => formatThousands(context.parsed.y),
                    },
                },
            },
            interaction: {
                intersect: false,
                mode: 'nearest',
            },
            maintainAspectRatio: false,
        },
    });
});
</script>

<template>
    <!-- Chart widget -->
    <div class="flex flex-col col-span-full xl:col-span-8 bg-white dark:bg-gray-800 rounded-xl shadow-2xl ">
        <div class="px-5 py-1">
            <div class="flex flex-wrap">
                <!-- Total de commits -->
                <div class="flex items-center py-2">
                    <div class="mr-5">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-gray-800 mr-2 dark:text-gray-100">24.7K</div>
                            <div class="text-sm font-medium text-green-500">+49%</div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Colaboradores</div>
                    </div>
                    <div class="hidden md:block w-px h-8 bg-gray-200 dark:bg-gray-800 mr-5" aria-hidden="true"></div>
                </div>
                <!-- Total Pageviews -->
                <div class="flex items-center py-2">
                    <div class="mr-5">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-gray-800 mr-2  dark:text-gray-100">56.9K</div>
                            <div class="text-sm font-medium text-green-500">+7%</div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total de commits</div>
                    </div>
                    <div class="hidden md:block w-px h-8 bg-gray-200 dark:bg-gray-800 mr-5" aria-hidden="true"></div>
                </div>
                <!-- Media commits/colborador -->
                <div class="flex items-center py-2">
                    <div class="mr-5">
                        <div class="flex items-center">
                            <div class="text-3xl font-bold text-gray-800 mr-2 dark:text-gray-100">54%</div>
                            <div class="text-sm font-medium text-yellow-500">-7%</div>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Média de commits/colaborador</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-grow">
            <canvas class="bg-white dark:bg-gray-800" id="chart" width="800" height="300"></canvas>
        </div>
    </div>
</template>

<style scoped>

</style>
