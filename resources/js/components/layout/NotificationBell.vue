<script setup>
import { ref, onMounted, computed } from 'vue';
import { Button, Badge, Drawer, Divider } from 'primevue';
import { useToast } from 'primevue/usetoast';
import { createAlert } from '../../helpers/alert';
import notificationService from '../../services/notification.service';
import ViewDelivery from '../modals/notifications/ViewDelivery.vue';

const showAlert = createAlert(useToast());

const emptyImage = new URL('@assets/svg/empty.svg', import.meta.url).href;

const notifications = ref([]);
const visibleNotifications = ref(false);
const clearingNotifications = ref(false);

const related_id = ref(0);

const modalViewDeliveryVisible = ref(false);

onMounted(() => {
    getNotifications();

    setInterval(() => {
        getNotifications();
    }, 60000);
});

const getNotifications = async () => {
    try {
        const response = await notificationService.getAll(10);
        notifications.value = response.data;
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    }
};

const notificationUnreadCount = computed(() => {
    const unread = notifications.value.filter(n => !n.is_read).length;
    return unread > 9 ? '9+' : unread;
});

const markAsRead = async (notification) => {
    openModal(notification.type, notification.related_id);
    notification.is_read = true;

    try {
        await notificationService.markAsRead(notification);
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    }
};

const markAllAsRead = async () => {
    try {
        clearingNotifications.value = true;
        const response = await notificationService.markAllAsRead();

        notifications.value = notifications.value.map(n => ({
            ...n,
            is_read: true
        }));

        showAlert('success', 'Sucesso', response.data.message);
    } catch (error) {
        showAlert('error', 'Erro', error.response?.data);
    } finally {
        clearingNotifications.value = false;
    }
};

const openModal = (type, item_id) => {
    related_id.value = null;

    switch (type) {
        case 'entrega':
            related_id.value = item_id;
            modalViewDeliveryVisible.value = true;
            break;
        default:
            break;
    }
}
</script>

<template>
    <div class="position-relative inline-block">
        <Button 
            icon="pi pi-bell" 
            variant="text" 
            aria-label="Filter" 
            severity="secondary"
            size="large"
            rounded
            @click="visibleNotifications = true"
        />

        <Badge v-if="notificationUnreadCount" 
            :value="notificationUnreadCount" 
            severity="danger"
            class="notification-badge" 
            size="small" 
            @click="visibleNotifications = true" 
        />

        <Drawer v-model:visible="visibleNotifications" position="right">
            <template #header>
                <h4>Notificações</h4>
            </template>

            <ul class="list-group list-group-flush overflow-auto">
                <template v-for="(n, index) in notifications" :key="n.id">
                    <li class="list-group-item list-group-item-action d-flex align-items-start gap-2 mb-2 p-2 drawer-list"
                        :class="{ unreadBg: !n.is_read }" @click="markAsRead(n)">
                        <i class="pi pi-box text-success mt-1" v-if="n.type === 'entrega'"></i>
                        <i class="pi pi-info-circle text-primary mt-1" v-else-if="n.type === 'aviso'"></i>
                        <div>
                            <p class="mb-1 fw-semibold">{{ n.title }}</p>
                            <small>{{ n.message }}</small>
                        </div>
                    </li>
                    <Divider v-if="index < notifications.length - 1" />
                </template>

                <div v-if="!notifications.length" class="empty-image">
                    <img :src="emptyImage" alt="Vazio">
                    <p class="text-center mt-4">Ops... nada por aqui no momento!</p>
                </div>
            </ul>

            <template #footer>
                <div class="text-center small text-muted py-1">
                    <template v-if="notificationUnreadCount">
                        <div v-if="notifications.filter(n => !n.is_read).length > 1">
                            {{ notifications.filter(n => !n.is_read).length }} notificações não lidas
                        </div>
                        <div v-else>
                            {{ notifications.filter(n => !n.is_read).length }} notificação não lida
                        </div>
                        <div>
                            <Button 
                                label="Marcar todas como lidas" 
                                icon="pi pi-check" 
                                class="p-button-text p-button-sm"
                                @click="markAllAsRead" 
                                :loading="clearingNotifications" 
                            />
                        </div>
                    </template>
                    <template v-else>
                        Não há novas notificações
                    </template>
                </div>
            </template>
        </Drawer>
    </div>

    <ViewDelivery
        v-model="modalViewDeliveryVisible"
        :deliveryId="related_id"
    />
</template>

<style scoped>
.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    padding: 4px !important;
}

.drawer-list {
    border-left: 4px solid transparent;
    cursor: pointer;
}

.unreadBg {
    border-left: 4px solid var(--p-primary-300);
}

.empty-image {
    margin-top: 50px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.empty-image img{
    width: 200px;
}
</style>
