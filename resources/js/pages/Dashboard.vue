<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const menuItems = ref([
    {
        title: 'Menu Items',
        description: 'Manage vending machine menu items',
        href: route('dashboard.menu-items.index'),
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'
    },
    {
        title: 'MQTT Configurations',
        description: 'Manage MQTT connection settings',
        href: route('dashboard.mqtt-configs.index'),
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'
    },
    {
        title: 'MQTT Test',
        description: 'Test MQTT connections and messages',
        href: route('dashboard.mqtt.test.index'),
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'
    }
]);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Link 
                    v-for="item in menuItems" 
                    :key="item.title"
                    :href="item.href"
                    class="group relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 bg-card p-6 transition-colors hover:border-primary dark:border-sidebar-border dark:hover:border-primary"
                >
                    <div class="flex h-full flex-col justify-between">
                        <div class="flex items-center gap-4">
                            <div class="rounded-lg bg-primary/10 p-2 text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold">{{ item.title }}</h3>
                        </div>
                        <p class="text-sm text-muted-foreground">{{ item.description }}</p>
                    </div>
                </Link>
            </div>
            
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border md:min-h-min">
                <h2 class="mb-4 text-xl font-semibold">Welcome to the Admin Dashboard</h2>
                <p class="text-muted-foreground">
                    Use the cards above to manage your vending machine settings, menu items, and MQTT configurations.
                </p>
            </div>
        </div>
    </AppLayout>
</template>
