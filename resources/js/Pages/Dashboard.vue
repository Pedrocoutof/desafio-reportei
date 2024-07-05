<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from "axios";
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from "vue";
import CircleLoading from "@/Components/CircleLoading.vue";
import RefreshButton from "@/Components/RefreshButton.vue";

const userRepositories = ref();
const selectedRepository = ref(null);
const loadingRepositories = ref(false);
const loadingCommitData = ref(false);
const { props } = usePage();

const chartDataset = ref([]);
const chartKey = ref(0);

async function getRepositories() {
    loadingRepositories.value = true;
    try {
        const response = await axios.get("http://127.0.0.1:8000/api/repositories/" + props.auth.user.nickname);

        if (response.status === 200) {
            userRepositories.value = response.data;
        }
    } catch (error) {
        console.error("Error fetching repositories:", error);
    }
    loadingRepositories.value = false;
}

async function generateInsights() {
    loadingCommitData.value = true;

    try {
        const response = await axios.get("http://127.0.0.1:8000/api/chart/" + props.auth.user.nickname + "/"+ selectedRepository.value);

        if (response.status === 200) {
            chartDataset.value = response.data;
            console.log(chartDataset.value)
            chartKey.value++;
        }
    } catch (error) {
        console.error("Error fetching chart data:", error);
    }

    loadingCommitData.value = false;
}

onMounted(async () => {
    await getRepositories();
});

watch(selectedRepository, () => {
    if (selectedRepository.value) {
        generateInsights();
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form class="max-w">
                            <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-white">Selecione um repositório:</label>
                            <div class="flex items-center">

                                <div class="relative z-10 flex-shrink-0">
                                    <button id="states-button" data-dropdown-toggle="dropdown-states" class="inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 bg-gray-100 border border-gray-300 rounded-l-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-gray-300 dark:border-gray-600" type="button">
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

                                <select :disabled="loadingRepositories" v-model="selectedRepository" id="countries" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-r-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 mr-2">
                                    <option class="text-sm text-gray-600" v-for="repository in userRepositories" :key="repository.name" :value="repository.name">{{ repository.name }}</option>
                                </select>
                                <CircleLoading v-if="loadingCommitData"></CircleLoading>
                                <RefreshButton></RefreshButton>
                                <button @click="generateInsights" type="button" :disabled="!selectedRepository" class="mx-2 disabled:pointer-events-none disabled:dark:bg-green-900 disabled:bg-green-400 focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800">Gerar Insights</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="chartDataset.data" class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Chart widget -->
                    <div v-if="chartDataset" class="flex flex-col col-span-full xl:col-span-8 bg-white dark:bg-gray-800 rounded-xl shadow-2xl">
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
                                            <div class="text-3xl font-bold text-gray-800 mr-2 dark:text-gray-100">56.9K</div>
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
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
