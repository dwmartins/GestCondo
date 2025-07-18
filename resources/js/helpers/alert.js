export const createAlert = (toast) => {
    return (type, title = '', errorResponse, time = 5000) => {
        let message = ''
        let titleResponse = ''

        if (errorResponse?.errors) {
            titleResponse = errorResponse.message
            message = Object.values(errorResponse.errors)
                .flat()
                .map(error => `- ${error}`)
                .join('\n')
        } else if (errorResponse?.message) {
            message = errorResponse.message
        }

        toast.add({
            severity: type,
            summary: titleResponse || title,
            detail: message || errorResponse,
            life: time
        })
    }
}