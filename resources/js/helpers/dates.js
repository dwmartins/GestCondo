export function formatDateTime(value) {
    if (!value) return '';

    return new Date(value).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

export function formatDate(value) {
    if (!value) return '';

    return new Date(value).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

export function isDateInFuture(dateString) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const inputDate = new Date(dateString);
    inputDate.setHours(0, 0, 0, 0);

    return inputDate > today;
}

export function toMysqlDateTime(dateTime) {
    if (!dateTime) return null;
    if (!(dateTime instanceof Date)) return dateTime;

    return new Intl.DateTimeFormat('sv-SE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
    }).format(dateTime).replace('T', ' ');
}