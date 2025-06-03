<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'test',
        href: '/test',
    },
];

const props = defineProps({
    menuItems: Array
});

console.log('Menu Items:', props.menuItems);

const navItems = ref([
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

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const deleteItem = (item) => {
    if (confirm(`Are you sure you want to delete ${item.name}?`)) {
        router.delete(route('dashboard.menu-items.destroy', item.id));
    }
};
</script>
<template>
    <Head title="Menu Items" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Menu Items</h2>
                <Link
                    :href="route('dashboard.menu-items.create')"
                    class="inline-flex items-center justify-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                >
                    Add New Item
                </Link>
            </div>

            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Price
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="item in props.menuItems" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10" v-if="item.image_url">
                                            <img class="h-10 w-10 rounded-full object-cover" :src="item.image_url" :alt="item.name">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ item.name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400" v-if="item.description">
                                                {{ item.description }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        ${{ formatPrice(item.price) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            item.is_available
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full'
                                        ]"
                                    >
                                        {{ item.is_available ? 'Available' : 'Unavailable' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-3">
                                        <Link
                                            :href="route('dashboard.menu-items.edit', { menu_item: item.id })"
                                            class="text-primary hover:text-primary/90 transition-colors"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="deleteItem(item)"
                                            class="text-destructive hover:text-destructive/90 transition-colors"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 