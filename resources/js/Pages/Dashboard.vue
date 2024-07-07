<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref } from "vue";
import CircleLoading from "@/Components/CircleLoading.vue";
import InputSelect from "@/Components/InputSelect.vue";
import RefreshButton from "@/Components/RefreshButton.vue";
import ChartComponent from "@/Components/Chart.vue"
import { axiosPost } from "@/api.js";

const userRepositories = ref();
const selectedRepository = ref(null);
const since = ref(new Date(Date.now() - 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);
const until = ref(new Date().toISOString().split('T')[0]);
const loadingRepositories = ref(false);
const loadingCommitData = ref(false);
const { props } = usePage();

const chartDataset = ref(null);

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

    loadingCommitData.value = false;
}

onMounted(async () => {
    await getRepositories();
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
                <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg shadow-xl">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form class="max-w">
                            <label class="block mb-2 text-sm font-medium text-gray-600 dark:text-gray-200">Selecione um repositório:</label>
                            <div class="flex flex-wrap items-center gap-y-2">
                                <button class="hidden sm:inline-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 bg-gray-100 border border-gray-300 rounded-l-lg hover:bg-gray-200 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-gray-300 dark:border-gray-600" type="button">
                                    {{ $page.props.auth.user.nickname }} /
                                </button>
                                <InputSelect v-model="selectedRepository" :options="userRepositories" :disabled="loadingRepositories"></InputSelect>
                                <p class="pr-1.5 text-gray-500 dark:text-gray-300">De</p>
                                <input v-model="since" class="rounded-lg bg-gray-50 border-gray-300 text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" type="date">
                                <p class="px-1.5 text-gray-500 dark:text-gray-300">Até</p>
                                <input v-model="until" class="rounded-lg bg-gray-50 border-gray-300 text-gray-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300" type="date">
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

        <ChartComponent v-if="chartDataset" :chartDataset="chartDataset"></ChartComponent>

    </AuthenticatedLayout>
</template>
