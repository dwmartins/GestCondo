import { useUserStore } from "../stores/userStore";

export function is_support() {
    const userStore = useUserStore();
    return userStore.user.role === 'suporte';
}