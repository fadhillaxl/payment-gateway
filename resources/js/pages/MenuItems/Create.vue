<template>
    <Head title="Create Menu Item" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Create Menu Item</h2>
            </div>

            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="name" value="Name" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:border-primary focus:ring-primary rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                            v-model="form.name"
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="price" value="Price" />
                        <TextInput
                            id="price"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:border-primary focus:ring-primary rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                            v-model="form.price"
                            required
                        />
                        <InputError :message="form.errors.price" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="image_url" value="Image URL" />
                        <TextInput
                            id="image_url"
                            type="url"
                            class="mt-1 block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:border-primary focus:ring-primary rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                            v-model="form.image_url"
                        />
                        <InputError :message="form.errors.image_url" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Description" />
                        <TextArea
                            id="description"
                            class="mt-1 block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 focus:border-primary focus:ring-primary rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                            v-model="form.description"
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <div class="flex items-center">
                        <Checkbox id="is_available" v-model:checked="form.is_available" />
                        <InputLabel for="is_available" value="Available" class="ml-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <Link
                            :href="route('dashboard.menu-items.index')"
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
import { type BreadcrumbItem } from '@/types';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import TextInput from '@/components/TextInput.vue';
import TextArea from '@/components/TextArea.vue';
import Checkbox from '@/components/Checkbox.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Menu Items',
        href: route('dashboard.menu-items.index'),
    },
    {
        title: 'Create',
        href: route('dashboard.menu-items.create'),
    },
];

const form = useForm({
    name: '',
    price: '',
    image_url: '',
    description: '',
    is_available: true
});

const submit = () => {
    form.post(route('dashboard.menu-items.store'));
};
</script> 