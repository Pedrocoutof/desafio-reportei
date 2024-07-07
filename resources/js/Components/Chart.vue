<script setup>
import { onMounted, ref, defineProps, watch } from 'vue';
import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';

const props = defineProps({
    chartDataset: {
        type: Object,
        required: true
    }
});

let chartInstance = null;

const formatThousands = (value) => Intl.NumberFormat('en-US', {
    maximumSignificantDigits: 3,
    notation: 'compact',
}).format(value);

onMounted(async () => {
    await createChart();
});

watch(() => props.chartDataset, async () => {
    await createChart();
}, { deep: true });

async function createChart() {
    if (props.chartDataset) {
        const ctx = await document.getElementById('chart')?.getContext('2d');

        if (ctx) {
            if (chartInstance) {
                chartInstance.destroy(); // Destruir instância existente do gráfico
            }

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: props.chartDataset.labels,
                    datasets: props.chartDataset.datasets
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
                                parser: 'yyyy-MM-dd',
                                unit: 'day',
                                displayFormats: {
                                    day: 'dd MM',
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
                                title: (context) => {
                                    const date = new Date(context[0].parsed.x);
                                    return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
                                },
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
        }
    }
}

</script>

<template>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg shadow-xl">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="px-5 py-1">
                    <div class="flex flex-wrap gap-4">
                        <div v-if="chartDataset" class="flex items-center py-2">
                            <div class="mr-5">
                                <div class="flex items-center">
                                    <div class="text-3xl font-bold text-gray-800 mr-2 dark:text-gray-200">{{ chartDataset.totalContributors }}</div>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Colaborador(es)</div>
                            </div>
                            <div class="hidden md:block w-px h-8 bg-gray-200 dark:bg-gray-800 mr-5" aria-hidden="true"></div>
                        </div>
                        <!-- Total de commits -->
                        <div v-if="chartDataset" class="flex items-center py-2">
                            <div class="mr-5">
                                <div class="flex items-center">
                                    <div class="text-3xl font-bold text-gray-800 mr-2 dark:text-gray-200">{{ chartDataset.totalCommits }}</div>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Total de commits</div>
                            </div>
                            <div class="hidden md:block w-px h-8 bg-gray-200 dark:bg-gray-800 mr-5" aria-hidden="true"></div>
                        </div>
                        <!-- Media commits/colaborador -->
                        <div v-if="chartDataset" class="flex items-center py-2">
                            <div class="mr-5">
                                <div class="flex items-center">
                                    <div class="text-3xl font-bold text-gray-800 mr-2 dark:text-gray-200">{{ chartDataset.avgCommitsDay }}</div>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Média de commits por dia</div>
                            </div>
                        </div>
                        <div v-if="chartDataset" class="ml-auto flex items-center py-2">
                            <div class="mr-5">
                                <div class="flex items-center">
                                    <div class="text-3xl font-bold text-gray-800 mr-2 dark:text-gray-200">{{ chartDataset.since }} - {{ chartDataset.until }}</div>
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 text-end">Período analisado</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-grow">
                    <canvas class="bg-white dark:bg-gray-800" id="chart" ref="chartCanvas" width="800" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</template>

