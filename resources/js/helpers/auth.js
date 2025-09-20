import { useUserStore } from "../stores/userStore";

export const ROLE_SUPORTE = 'suporte';
export const ROLE_SUB_SINDICO = 'sub_sindico';
export const ROLE_SINDICO = 'sindico';
export const ROLE_MORADOR = 'morador';

export const ROLE_DEFINITIONS = {
    sindico: 'Síndico',
    sub_sindico: 'Sub Síndico',
    morador: 'Morador',
};

export const defaultPermissions = {
    moradores: {
        label: 'Moradores',
        visualizar: false,
        criar: false,
        editar: false,
        excluir: false,
    },
    funcionarios: {
        label: 'Funcionários',
        visualizar: false,
        criar: false,
        editar: false,
        excluir: false,
    },
    entregas: {
        label: 'Entregas',
        visualizar: false,
        criar: false,
        editar: false,
        excluir: false,
    },
    espacosComuns: {
        label: 'Espaços Comuns',
        visualizar: false,
        criar: false,
        editar: false,
        excluir: false,
    }
}

export function is_support() {
    const userStore = useUserStore();
    return userStore.user.role === ROLE_SUPORTE;
}

export function is_sindico() {
    const userStore = useUserStore();
    return [ROLE_SINDICO, ROLE_SUPORTE].includes(userStore.user.role);
}

export function checkPermission(module, action) {
    const userStore = useUserStore();
    const user = userStore.user;

    if([ROLE_SINDICO, ROLE_SUPORTE].includes(user.role)) {
        return true;
    }

    return user.permissions?.[module]?.[action] ?? false;
}

export function showUserRole(userRole) {
    return ROLE_DEFINITIONS[userRole];
}