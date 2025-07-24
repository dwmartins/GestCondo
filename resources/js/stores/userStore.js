
import { reactive } from "vue";

export const userStore = reactive({
    logged: false,
    token: null,
    currentCondominiumId: null,

    user: {
        id: null,
        name: "",
        last_name: null,
        email: "",
        password: "",
        role: "resident",
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
    },

    update(data) {
        this.user = { ...this.user, ...data };
        this.logged = true;

        if (data.token) {
            this.token = data.token;
        }
    },

    clean() {
        this.logged = false;
        
        for (let key in this.user) {
            this.user[key] = "";
        }
    },

    setToken(newToken) {
        this.token = newToken;
    }
});
