<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from "axios";
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref } from "vue";

const userRepositories = ref();
const { props } = usePage();

async function getRepositories() {
    let repositories;
    try {
        const response = await axios.get("http://127.0.0.1:8000/api/repositories", {
            params: { user: props.auth.user.nickname }
        });

        if (response.status === 200) {
            repositories = response.data;
        }
    } catch (error) {
        console.error("Error fetching repositories:", error);
    }

    return repositories || [];
}

onMounted(async () => {
    userRepositories.value = await getRepositories();
    console.log(userRepositories.value);
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
                        <form class="max-w-sm">
                            <label for="countries" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Selecione um repositório:</label>
                            <span class="inline-flex items-center px-3 text-sm text-gray-900 bg-gray-200 border border-e-0 border-gray-300 rounded-s-md dark:bg-gray-600 dark:text-gray-400 dark:border-gray-600">
                                  {{ props.auth.user.nickname }}
                                </span>
                            <select id="countries" class="flex bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option selected>Selecione um </option>
                                <option v-for="repository in userRepositories">{{ repository.name }}</option>
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
