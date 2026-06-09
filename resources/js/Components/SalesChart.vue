<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
});

const maxRevenue = computed(() => {
    if (!props.data || props.data.length === 0) return 0;
    return Math.max(...props.data.map((d) => d.revenue));
});

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const getBarHeight = (revenue) => {
    if (maxRevenue.value === 0 || revenue === 0) return '4px';
    return `${Math.max((revenue / maxRevenue.value) * 100, 2)}%`;
};
</script>

<template>
    <div class="flex h-56 w-full items-end justify-between gap-2 pt-6">
        <div 
            v-for="item in data" 
            :key="item.date"
            class="group relative flex flex-1 flex-col items-center justify-end h-full"
        >
            <!-- Tooltip -->
            <div class="absolute -top-12 left-1/2 -translate-x-1/2 opacity-0 transition-opacity group-hover:opacity-100 pointer-events-none z-10 whitespace-nowrap rounded-lg bg-[#001e2b] px-3 py-1.5 text-xs font-bold text-white shadow-lg">
                {{ formatRupiah(item.revenue) }}
                <!-- Little arrow -->
                <div class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-[#001e2b]"></div>
            </div>
            
            <!-- Bar Track -->
            <div class="relative w-full max-w-[40px] rounded-t-lg bg-[#f4f7f6] h-full flex items-end">
                <div 
                    class="w-full rounded-t-lg transition-all duration-500 ease-in-out"
                    :class="[
                        item.revenue === 0 
                            ? 'bg-[#e1e5e8] group-hover:bg-[#c1ccd6]' 
                            : 'bg-[#00ed64] group-hover:bg-[#00b545] opacity-90 group-hover:opacity-100'
                    ]"
                    :style="{ height: getBarHeight(item.revenue) }"
                ></div>
            </div>
            
            <!-- Label -->
            <div class="mt-3 flex flex-col items-center">
                <span class="text-xs font-bold text-[#3d4f5b]">{{ item.day }}</span>
                <span class="text-[10px] font-medium text-[#7c8c9a]">{{ item.date }}</span>
            </div>
        </div>
    </div>
</template>
