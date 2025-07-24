export const createAlert = (toast) => {
    return (type, title = '', errorResponse, time = 5000) => {
        let summary = title;
        let detail = '';
        let fallback = 'Ocorreu um erro inesperado.';

        // If you have a standard error structure with `message` and `errors`
        if (errorResponse?.errors && typeof errorResponse.errors === 'object') {
            summary = errorResponse.message || title || 'Erro de validação';
            detail = Object.values(errorResponse.errors)
                .flat()
                .map(error => `- ${error}`)
                .join('\n');
        } 
        
        // If you only have a message
        else if (errorResponse?.message) {
            summary = title || 'Erro';
            detail = errorResponse.message;
        }

        // If it is a direct string (ex: response.data = 'Unauthorized user')
        else if (typeof errorResponse === 'string') {
            summary = title || 'Erro';
            detail = errorResponse;
        }

        // Fallback
        else {
            summary = title || 'Erro';
            detail = fallback;
        }

        toast.add({
            severity: type,
            summary,
            detail,
            life: time
        });
    };
};
