<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    text: {
        type: String,
        required: true,
    },
    icon: {
        type: [Object, Function],
        required: true,
    },
    sidebarExpanded: {
        type: Boolean,
        default: true,
    },
    isActive: {
        type: Boolean,
        default: false,
    },
});

</script>

<template>
    <Link :href="href" :class="{
        'justify-center': !sidebarExpanded,
        'bg-green-700 text-white': isActive
    }" class="group flex items-center p-2 mt-1 rounded-md
                 text-gray-600 hover:bg-green-700
                 hover:text-white transition-colors duration-200 focus:bg-green-700 focus:text-white">
    <component :is="icon" :class="{
        'group-hover:text-yellow-300': true,
        'group-focus:text-yellow-300': true,
        'text-yellow-300': isActive
    }" />
    <span v-if="sidebarExpanded" class="ml-2 whitespace-nowrap">
        {{ text }}
    </span>
    </Link>
</template>