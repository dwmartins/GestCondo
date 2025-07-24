import { userStore } from "../stores/userStore";

export function setSelectedCondominiumId(condominiumId) {
    userStore.currentCondominiumId = condominiumId;
    localStorage.setItem('current_condominium_id', condominiumId);
}

export function getSelectedCondominiumId() {
    const condominiumId = localStorage.getItem('current_condominium_id');
    return condominiumId ?? null;
}

export function clearCondominiumIdSelection() {
    localStorage.removeItem('current_condominium_id');
    userStore.currentCondominiumId = null;
}

export function refreshSelectedCondominiumId() {
    const condominiumId = getSelectedCondominiumId();

    if(condominiumId) {
        setSelectedCondominiumId(condominiumId);
    }
}