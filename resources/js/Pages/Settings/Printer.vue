<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Input } from '@/Components/ui/input';
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import { Switch } from '@/Components/ui/switch';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui/card';
import { useToast } from '@/Components/ui/toast/use-toast';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';

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

const { toast } = useToast();

const submit = () => {
    form.post(route('settings.printer.store'), {
        preserveScroll: true,
        onSuccess: () => {
            toast({
                title: 'Berhasil',
                description: 'Pengaturan printer berhasil disimpan.',
            });
        },
    });
};

const testPrint = () => {
    toast({
        title: 'Info',
        description: 'Fungsionalitas test print akan tersedia di task selanjutnya (T040).',
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Pengaturan Printer" />

        <div class="flex-1 space-y-4 p-4 md:p-8 pt-6">
            <div class="flex items-center justify-between space-y-2">
                <h2 class="text-3xl font-bold tracking-tight">Pengaturan Printer</h2>
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
                                    <Label class="text-base">Auto Print</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Otomatis cetak struk setelah transaksi berhasil.
                                    </p>
                                </div>
                                <div>
                                    <Switch
                                        :checked="form.auto_print"
                                        @update:checked="form.auto_print = $event"
                                    />
                                </div>
                            </div>
                        </CardContent>
                        <CardFooter class="flex justify-between">
                            <Button type="button" variant="outline" @click="testPrint">Test Print</Button>
                            <Button type="submit" :disabled="form.processing">Simpan Pengaturan</Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
