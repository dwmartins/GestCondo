import { userStore } from "../stores/userStore";

export function is_support() {
    return userStore.user.role === 'support';
}