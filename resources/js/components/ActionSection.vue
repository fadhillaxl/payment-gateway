<template>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1 flex justify-between">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    <slot name="title" />
                </h3>

                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    <slot name="description" />
                </p>
            </div>

            <div class="px-4 sm:px-0">
                <slot name="aside" />
            </div>
        </div>

        <div class="mt-5 md:mt-0 md:col-span-2">
            <div class="px-4 py-5 bg-white dark:bg-gray-800 sm:p-6 shadow sm:rounded-md">
                <slot name="content" />
            </div>
        </div>
    </div>
</template> 