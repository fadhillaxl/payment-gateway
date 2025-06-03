<template>
    <Head title="Create MQTT Configuration" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Create MQTT Configuration</h2>
            </div>

            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="host" value="Host" />
                        <TextInput
                            id="host"
                            type="text"
                            class="mt-1 block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:border-primary focus:ring-primary rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                            v-model="form.host"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.host" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="port" value="Port" />
                        <TextInput
                            id="port"
                            type="number"
                            class="mt-1 block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:border-primary focus:ring-primary rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                            v-model="form.port"
                            required
                        />
                        <InputError :message="form.errors.port" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="username" value="Username" />
                        <TextInput
                            id="username"
                            type="text"
                            class="mt-1 block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:border-primary focus:ring-primary rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                            v-model="form.username"
                        />
                        <InputError :message="form.errors.username" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Password" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:border-primary focus:ring-primary rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                            v-model="form.password"
                        />
                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="client_id" value="Client ID" />
                        <TextInput
                            id="client_id"
                            type="text"
                            class="mt-1 block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:border-primary focus:ring-primary rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                            v-model="form.client_id"
                            required
                        />
                        <InputError :message="form.errors.client_id" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <Checkbox id="is_active" v-model:checked="form.is_active" />
                        <InputLabel for="is_active" value="Active" class="ml-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <Link
                            :href="route('dashboard.mqtt-configs.index')"
                            class="text-muted-foreground hover:text-foreground mr-4 transition-colors"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Create
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import Checkbox from '@/components/Checkbox.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'MQTT Configurations',
        href: route('dashboard.mqtt-configs.index'),
    },
    {
        title: 'Create',
        href: route('dashboard.mqtt-configs.create'),
    },
];

const form = useForm({
    host: '',
    port: '',
    username: '',
    password: '',
    client_id: '',
    is_active: true
});

const submit = () => {
    form.post(route('dashboard.mqtt-configs.store'));
};
</script> 