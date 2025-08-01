import { useUserStore } from "../stores/userStore";

export const ROLE_SUPORTE = 'suporte';
export const ROLE_SINDICO = 'sindico';
export const ROLE_MORADOR = 'morador';

export const ROLE_DEFINITIONS = {
    suporte: 'Suporte',
    sindico: 'Síndico',
    morador: 'Morador',
};

export function is_support() {
    const userStore = useUserStore();
    return userStore.user.role === ROLE_SUPORTE;
}

export function is_sindico() {
    const userStore = useUserStore();
    return [ROLE_SINDICO, ROLE_SUPORTE].includes(userStore.user.role);
}