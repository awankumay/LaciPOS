import { ref, readonly } from 'vue';

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
    id: number;
    message: string;
    type: ToastType;
    title?: string;
    duration?: number;
}

const toasts = ref<Toast[]>([]);
let counter = 0;

const addToast = (message: string, type: ToastType = 'info', title?: string, duration = 4000) => {
    const id = ++counter;
    toasts.value.push({ id, message, type, title, duration });

    if (duration > 0) {
        setTimeout(() => removeToast(id), duration);
    }

    return id;
};

const removeToast = (id: number) => {
    const idx = toasts.value.findIndex((t) => t.id === id);
    if (idx !== -1) toasts.value.splice(idx, 1);
};

export const useToast = () => {
    const success = (message: string, title?: string, duration?: number) =>
        addToast(message, 'success', title, duration);

    const error = (message: string, title?: string, duration?: number) =>
        addToast(message, 'error', title, duration);

    const warning = (message: string, title?: string, duration?: number) =>
        addToast(message, 'warning', title, duration);

    const info = (message: string, title?: string, duration?: number) =>
        addToast(message, 'info', title, duration);

    return {
        toasts: readonly(toasts),
        success,
        error,
        warning,
        info,
        remove: removeToast,
    };
};
