import { reactive } from "vue";
import { createAlert } from "./alert";

export function validateFields (requiredFields, formData, toast){
    const showAlert = createAlert(toast); 

    const fieldErrors = reactive({});

    Object.keys(fieldErrors).forEach(key => {
        fieldErrors[key] = null;
    });

    let isValid = true;
    const newErrors = {};

    requiredFields.forEach(({id, label}) => {
        if(!formData[id]) {
            isValid = false;
            newErrors[id] = [`O campo "${label}" é obrigatório`];
        }
    });

    Object.assign(fieldErrors, newErrors);

    if(!isValid) {
        const filteredErrors = Object.entries(fieldErrors).reduce((acc, [key, value]) => {
            if (value !== null) {
                acc[key] = value;
            }
            return acc;
        }, {});

        showAlert('error', 'Campos inválidos', {
            errors: filteredErrors
        }, 6000);
    }

    return isValid;
}