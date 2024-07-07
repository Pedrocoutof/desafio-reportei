<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref } from "vue";
import CircleLoading from "@/Components/CircleLoading.vue";
import Chart from 'chart.js/auto';
import 'chartjs-adapter-date-fns';
import InputSelect from "@/Components/InputSelect.vue";
import { axiosPost } from "@/api.js";
import RefreshButton from "@/Components/RefreshButton.vue";
const userRepositories = ref();
const selectedRepository = ref(null);
const since = ref(new Date(Date.now() - 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);
const until = ref(new Date().toISOString().split('T')[0]);
const loadingRepositories = ref(false);
const loadingCommitData = ref(false);
const { props } = usePage();

const chartDataset = ref(null);
const chartCanvas = ref(null);

async function getRepositories() {
    loadingRepositories.value = true;
    try {
        let params = {user: props.auth.user.nickname}
        const response = await axiosPost('repositories', params)

        if (response.status === 200) {
            userRepositories.value = response.data;
        }
    } catch (error) {
        console.error("Erro ao obter repositorios", error);
    }
    loadingRepositories.value = false;
}
async function updateRepositories() {
    loadingRepositories.value = true;
    let params = {user: props.auth.user.nickname}
    await axiosPost('clear-repositories-cache', params)
        .then(async (response) => response.status === 200 ? await getRepositories() : console.error(response));
    loadingRepositories.value = false;
}
async function generateInsights() {
    loadingCommitData.value = true;

    try {
        let params = {
            user: props.auth.user.nickname,
            repository: selectedRepository.value,
            since: since.value,
            until: until.value
        };
        let response = await axiosPost('chart', params)

        if (response.status === 200) {
            chartDataset.value = response.data;
        }
    } catch (error) {
        console.error("Erro ao obter dados do gráfico:", error);
    }

    await createChart();
    loadingCommitData.value = false;
}

onMounted(async () => {
    await getRepositories();

    Chart.defaults.font.family = '"Inter", sans-serif';
    Chart.defaults.font.weight = '500';
    Chart.defaults.color = 'rgb(148, 163, 184)';
    Chart.defaults.scale.grid.color = 'rgba(140,140,140,0.45)';
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

    createChart()
});

const formatThousands = (value) => Intl.NumberFormat('en-US', {
    maximumSignificantDigits: 3,
    notation: 'compact',
}).format(value);

async function createChart()  {
    if (chartDataset.value) {
        const ctx = await document.getElementById('chart')?.getContext('2d');

        if(chartCanvas.value) {
            chartCanvas.value.destroy();
        }
        chartCanvas.value = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartDataset.value.labels,
                datasets: chartDataset.value.datasets
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

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg shadow-xl">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form class="max-w">
                            <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">Selecione um repositório:</label>
                            <div class="flex flex-wrap items-center">

                                <div class="relative z-10 flex-shrink-0">
                                    <button class="hidden sm:inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 bg-gray-100 border border-gray-300 rounded-l-lg hover:bg-gray-200 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-gray-300 dark:border-gray-600" type="button">
                                        {{ $page.props.auth.user.nickname }} /
                                    </button>

                                    <div id="dropdown-org" class="absolute mt-2 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                                        <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="states-button">
                                            <li>
                                                <button type="button" class="inline-flex w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white">
                                                    <div class="inline-flex items-center">United States</div>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <InputSelect v-model="selectedRepository" :options="userRepositories" :disabled="loadingRepositories"></InputSelect>
                                <input v-model="since" class="rounded-lg bg-gray-50 border-gray-300 text-gray-500 text" type="date">
                                <input v-model="until" class="rounded-lg bg-gray-50 border-gray-300 text-gray-500 text" type="date">
                                <CircleLoading v-if="loadingCommitData || loadingRepositories"></CircleLoading>
                                <RefreshButton :disabled="loadingRepositories" @click="updateRepositories"></RefreshButton>
                                <button @click="generateInsights" type="button" :disabled="!selectedRepository || loadingCommitData" class="w-full sm:w-auto mx-2 my-2 sm:my-0 disabled:pointer-events-none disabled:dark:bg-green-900 disabled:bg-green-400 focus:outline-none text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-700 dark:hover:bg-green-600 dark:focus:ring-green-800">
                                    Gerar Insights
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div :hidden="!chartDataset" class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="px-5 py-1">
                        <div class="flex flex-wrap">
                            <!-- Total de commits -->
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
                            <!-- Media commits/colborador -->
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
                        <canvas class="bg-white dark:bg-gray-800" id="chart" width="800" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
