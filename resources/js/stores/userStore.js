// stores/userStore.js
import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';

export const useUserStore = defineStore('user', () => {
    const logged = ref(false);
    const token = ref(null);
    const currentCondominiumId = ref(null);

    const user = reactive({
        id: null,
        name: "",
        last_name: null,
        email: "",
        password: "",
        role: "morador",
        account_status: true,
        description: null,
        phone: null,
        date_of_birth: null,
        address: null,
        complement: null,
        city: null,
        zip_code: null,
        state: null,
        country: null,
        avatar: null,
        accepts_emails: true,
        last_login_at: null,
        remember_token: null,
        created_at: null,
        updated_at: null,
    });

    function update(data) {
        const booleanFields = ['accepts_emails', 'account_status'];
        const processedData = {};

        for (const key in data) {
            if (booleanFields.includes(key)) {
                processedData[key] = !!data[key];
            } else {
                processedData[key] = data[key];
            }
        }

        Object.assign(user, processedData);
        logged.value = true;
    }

    function clean() {
        logged.value = false;
        token.value = null;
        currentCondominiumId.value = null;

        for (let key in user) {
            user[key] = key === 'role' ? 'morador' : null;
        }
    }

    function setToken(newToken) {
        token.value = newToken;
    }

    function setCurrentCondominiumId(id) {
        currentCondominiumId.value = id;
    }

    function setAvatar(newAvatar) {
        user.avatar = newAvatar;
    }

    return {
        user,
        logged,
        token,
        currentCondominiumId,
        update,
        clean,
        setToken,
        setCurrentCondominiumId,
        setAvatar
    };
});
