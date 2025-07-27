import { useUserStore } from "../stores/userStore";

export function is_support() {
    const userStore = useUserStore();
    return userStore.user.role === 'suporte';
}

export function is_sindico() {
    const userStore = useUserStore();
    return ['sindico', 'suporte'].includes(userStore.user.role);
}