import { ElNotification } from "element-plus"

export const showAlert = (type, title = '', errorResponse) => {
    let message = '';
    let titleResponse = '';

    if(errorResponse.errors) {
        titleResponse = errorResponse.message;
        message = Object.values(errorResponse.errors)
                    .flat()
                    .map(error => `- ${error} <br>`)
                    .join('\n');
        
    }

    ElNotification({
        type: type,
        title: titleResponse ? titleResponse : title,
        message: message ? message : errorResponse,
        dangerouslyUseHTMLString: true,
    });
}