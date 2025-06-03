<template>
    <Head title="Edit MQTT Configuration" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Edit MQTT Configuration
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="host" value="Host" />
                                <TextInput
                                    id="host"
                                    type="text"
                                    class="mt-1 block w-full"
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
                                    class="mt-1 block w-full"
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
                                    class="mt-1 block w-full"
                                    v-model="form.username"
                                />
                                <InputError :message="form.errors.username" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password" value="Password" />
                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="form.password"
                                    placeholder="Leave blank to keep current password"
                                />
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="client_id" value="Client ID" />
                                <TextInput
                                    id="client_id"
                                    type="text"
                                    class="mt-1 block w-full"
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
                                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import Checkbox from '@/components/Checkbox.vue';

const props = defineProps({
    config: Object
});

const form = useForm({
    host: props.config.host,
    port: props.config.port,
    username: props.config.username,
    password: '',
    client_id: props.config.client_id,
    is_active: props.config.is_active
});

const submit = () => {
    form.put(route('dashboard.mqtt-configs.update', props.config.id));
};
</script> 