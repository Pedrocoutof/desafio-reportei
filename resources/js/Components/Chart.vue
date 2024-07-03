<script setup>
import { onMounted } from 'vue';
import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';

const obj = [
    {
        "Pedrocoutof": {
            "2024-05-17": 4,
            "2024-05-18": 2,
            "2024-05-19": 10,
            "2024-05-29": 1,
            "2024-06-03": 1,
            "2024-06-06": 1,
            "2024-06-07": 9,
            "2024-06-08": 1,
            "2024-06-09": 2,
            "2024-06-10": 4,
            "2024-06-11": 1,
            "2024-06-14": 1,
            "2024-06-15": 2,
            "2024-06-16": 7,
            "2024-06-17": 3,
            "2024-07-02": 2
        },
        "web-flow": {
            "2024-05-19": 1
        }
    }
];

console.log(obj)
function generateDateLabels(days) {
    const labels = [];
    const currentDate = new Date(); // Data atual
    currentDate.setHours(0, 0, 0, 0); // Ajusta para meia-noite para evitar problemas com fuso horário

    for (let i = 0; i < days; i++) {
        const newDate = new Date(currentDate);
        newDate.setDate(currentDate.getDate() + i);

        const day = String(newDate.getDate()).padStart(2, '0');
        const month = String(newDate.getMonth() + 1).padStart(2, '0'); // getMonth() retorna de 0 a 11
        const year = newDate.getFullYear();

        labels.push(`${day}-${month}-${year}`);
    }

    return labels;
}
function generateFakeData(number) {
    let arr = [];
    for (let i = 0; i < number ; i++) {
        arr.push(Math.floor(Math.random() * 10))
    }
    return arr;
}

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
            labels: generateDateLabels(90),
            datasets: [
                // Indigo line
                {
                    label: 'Commits',
                    data: generateFakeData(90),
                    fill: true,
                    backgroundColor: 'rgba(59, 130, 246, 0.08)',
                    borderColor: 'rgb(99, 102, 241)',
                    borderWidth: 2,
                    tension: 0.15,
                    pointRadius: 0,
                    pointHoverRadius: 3,
                    pointBackgroundColor: 'rgb(99, 102, 241)',
                },
                // Indigo line
                {
                    label: 'Teste',
                    data: generateFakeData(90),
                    fill: true,
                    backgroundColor: 'rgba(246,59,59,0.08)',
                    borderColor: 'rgb(241,99,99)',
                    borderWidth: 2,
                    tension: 0.15,
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
                        parser: 'dd-MM-yyyy',
                        unit: 'day',
                        displayFormats: {
                            day: 'dd MMM',
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
