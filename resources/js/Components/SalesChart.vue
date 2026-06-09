<script setup>
import { computed, ref } from 'vue';
import { TrendingUp } from 'lucide-vue-next';
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';

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

// Hitung rentang tanggal untuk deskripsi (e.g., 1 Juni - 7 Juni 2024)
const dateRangeDescription = computed(() => {
    if (!props.data || props.data.length === 0) return '';
    const first = new Date(props.data[props.data.length - 1].full_date || new Date().toISOString()); 
    const last = new Date(props.data[0].full_date || new Date().toISOString());
    
    // Check which one is older
    const startDate = first < last ? first : last;
    const endDate = first > last ? first : last;

    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return `${startDate.toLocaleDateString('id-ID', options)} - ${endDate.toLocaleDateString('id-ID', options)}`;
});

const totalRevenue = computed(() => {
    return props.data.reduce((sum, item) => sum + item.revenue, 0);
});

const tooltip = ref({ visible: false, x: 0, y: 0, data: null });

const onMouseMove = (event, item) => {
    tooltip.value.visible = true;
    tooltip.value.data = item;
    tooltip.value.x = event.clientX;
    tooltip.value.y = event.clientY;
};

const onMouseLeave = () => {
    tooltip.value.visible = false;
};
</script>

<template>
    <Card class="border-0 shadow-none">
        <CardHeader class="px-0 pt-6">
            <CardTitle>Pendapatan 7 Hari Terakhir</CardTitle>
            <CardDescription>{{ dateRangeDescription }}</CardDescription>
        </CardHeader>
        
        <CardContent class="px-0 pb-4 relative">
            <!-- Fixed Tooltip following cursor -->
            <div 
                v-if="tooltip.visible && tooltip.data"
                class="fixed pointer-events-none z-50 w-max rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm shadow-md transition-opacity duration-150"
                :style="{ top: `${tooltip.y - 70}px`, left: `${tooltip.x}px`, transform: 'translateX(-50%)' }"
            >
                <div class="flex flex-col gap-1">
                    <span class="text-xs text-slate-500 uppercase font-semibold">{{ tooltip.data.day }}</span>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-[#001e2b]"></div>
                        <span class="font-bold text-slate-900">{{ formatRupiah(tooltip.data.revenue) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex h-[250px] w-full items-end justify-between gap-3" @mouseleave="onMouseLeave">
                <div 
                    v-for="item in data" 
                    :key="item.date"
                    class="group relative flex flex-1 flex-col items-center justify-end h-full cursor-default"
                    @mousemove="onMouseMove($event, item)"
                >
                    
                    <!-- Bar Track -->
                    <div class="relative w-full max-w-[48px] rounded-t-md h-full flex items-end">
                        <div 
                            class="w-full rounded-t-md transition-all duration-500 ease-in-out"
                            :class="[
                                item.revenue === 0 
                                    ? 'bg-slate-100 group-hover:bg-slate-200' 
                                    : 'bg-[#001e2b] group-hover:bg-[#1c2d38]'
                            ]"
                            :style="{ height: getBarHeight(item.revenue) }"
                        ></div>
                    </div>
                    
                    <!-- Label -->
                    <div class="mt-3 flex flex-col items-center">
                        <span class="text-xs text-slate-500">{{ item.day.slice(0, 3) }}</span>
                    </div>
                </div>
            </div>
        </CardContent>
        
        <CardFooter class="flex-col items-start gap-2 text-sm px-0 pt-0 pb-0">
            <div class="flex gap-2 leading-none font-medium">
                Total pendapatan: {{ formatRupiah(totalRevenue) }} <TrendingUp class="h-4 w-4" />
            </div>
            <div class="leading-none text-muted-foreground text-slate-500">
                Menampilkan total pendapatan untuk 7 hari terakhir
            </div>
        </CardFooter>
    </Card>
</template>
