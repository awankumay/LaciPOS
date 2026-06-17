<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Input } from '@/Components/ui/input';
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import { Switch } from '@/Components/ui/switch';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui/card';
import { useToast } from '@/composables/useToast';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { Check, ChevronsUpDown } from 'lucide-vue-next';

const props = defineProps({
    profile: {
        type: Object,
        required: true,
    },
    printers: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    printer_name: props.profile?.printer_name || '',
    paper_size: props.profile?.paper_size || '80mm',
    auto_print: props.profile?.auto_print ? true : false,
});

const toast = useToast();
const openAutoPrintBox = ref(false);

const submit = () => {
    form.post('/settings/printer', {
        preserveScroll: true,
    });
};

const isTesting = ref(false);

const testPrint = () => {
    isTesting.value = true;
    router.post('/settings/printer/test', {}, {
        preserveScroll: true,
        onFinish: () => {
            isTesting.value = false;
        }
    });
};
</script>

<template>
    <SettingsLayout title="Pengaturan Printer">
        <div class="flex items-center justify-between space-y-2">
            <h2 class="text-2xl font-bold tracking-tight text-[#001e2b]">Pengaturan Printer</h2>
        </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card class="col-span-2">
                    <form @submit.prevent="submit">
                        <CardHeader>
                            <CardTitle>Printer Thermal</CardTitle>
                            <CardDescription>
                                Konfigurasi printer thermal untuk mencetak struk transaksi.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="space-y-2">
                                <Label for="printer_name">Pilih Printer</Label>
                                <div class="flex gap-2 items-center">
                                    <Select v-if="printers.length > 0" v-model="form.printer_name">
                                        <SelectTrigger class="w-full">
                                            <SelectValue placeholder="Pilih printer OS..." />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="printer in printers" :key="printer.name" :value="printer.name">
                                                {{ printer.displayName }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <Input v-else
                                        id="printer_name"
                                        v-model="form.printer_name"
                                        placeholder="Nama printer (misal: POS-80C)"
                                        class="w-full"
                                    />
                                </div>
                                <p class="text-sm text-muted-foreground mt-1">
                                    <span v-if="printers.length > 0">Atau ketik manual jika tidak ada dalam daftar.</span>
                                    <span v-else>NativePHP belum mendeteksi daftar printer, silakan ketik nama printer sesuai di OS.</span>
                                </p>
                                <Input v-if="printers.length > 0"
                                    id="printer_name_manual"
                                    v-model="form.printer_name"
                                    placeholder="Input nama printer manual..."
                                    class="w-full mt-2"
                                />
                                <p v-if="form.errors.printer_name" class="text-sm text-destructive mt-1">{{ form.errors.printer_name }}</p>
                            </div>

                            <div class="space-y-3">
                                <Label>Ukuran Kertas</Label>
                                <RadioGroup v-model="form.paper_size" class="flex flex-col space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem id="size-58" value="58mm" />
                                        <Label for="size-58">58 mm (Kecil)</Label>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <RadioGroupItem id="size-80" value="80mm" />
                                        <Label for="size-80">80 mm (Standar)</Label>
                                    </div>
                                </RadioGroup>
                                <p v-if="form.errors.paper_size" class="text-sm text-destructive mt-1">{{ form.errors.paper_size }}</p>
                            </div>

                            <div class="flex flex-row items-center justify-between rounded-lg border p-4">
                                <div class="space-y-0.5">
                                    <Label class="text-base" for="auto_print">Auto Print</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Otomatis cetak struk setelah transaksi berhasil.
                                    </p>
                                </div>
                                <div class="w-[160px]">
                                    <Popover v-model:open="openAutoPrintBox">
                                        <PopoverTrigger as-child>
                                            <button
                                                type="button"
                                                role="combobox"
                                                :aria-expanded="openAutoPrintBox"
                                                class="flex items-center justify-between h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                                            >
                                                <span class="truncate">{{ form.auto_print ? 'Aktif' : 'Nonaktif' }}</span>
                                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                                            </button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-[160px] p-0 bg-white" align="start">
                                            <Command>
                                                <CommandList>
                                                    <CommandGroup>
                                                        <CommandItem
                                                            value="Aktif"
                                                            @select="() => {
                                                                form.auto_print = true;
                                                                openAutoPrintBox = false;
                                                            }"
                                                            class="text-sm cursor-pointer"
                                                        >
                                                            Aktif
                                                            <Check
                                                                :class="['ml-auto h-4 w-4', form.auto_print === true ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                            />
                                                        </CommandItem>
                                                        <CommandItem
                                                            value="Nonaktif"
                                                            @select="() => {
                                                                form.auto_print = false;
                                                                openAutoPrintBox = false;
                                                            }"
                                                            class="text-sm cursor-pointer"
                                                        >
                                                            Nonaktif
                                                            <Check
                                                                :class="['ml-auto h-4 w-4', form.auto_print === false ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                                            />
                                                        </CommandItem>
                                                    </CommandGroup>
                                                </CommandList>
                                            </Command>
                                        </PopoverContent>
                                    </Popover>
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="flex justify-between">
                            <Button type="button" variant="outline" @click="testPrint" :disabled="isTesting">
                                {{ isTesting ? 'Mencetak...' : 'Test Print' }}
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="bg-[#001e2b] text-white hover:bg-[#1c2d38]">Simpan Pengaturan</Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
    </SettingsLayout>
</template>
