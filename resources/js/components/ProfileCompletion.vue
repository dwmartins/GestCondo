<script setup>
import Chart from 'primevue/chart';
import { useUserStore } from '../stores/userStore';
import { computed, ref } from 'vue';

const userStore = useUserStore();
const user = userStore.user;

const relevantFields = [
    'name',
    'last_name',
    'email',
    'phone',
    'date_of_birth',
    'address',
    'complement',
    'city',
    'zip_code',
    'state',
    'country',
    'avatar'
];

const completionPercentage = computed(() => {
    const totalFields = relevantFields.length;
    let filledFields = 0;

    relevantFields.forEach(field => {
        if (userStore.user[field] !== null && userStore.user[field] !== '') {
            filledFields++;
        }
    });

    return Math.round((filledFields / totalFields) * 100);
});

const chartData = computed(() => ({
    datasets: [{
        data: [completionPercentage.value, 100 - completionPercentage.value],
        backgroundColor: ['#409EFF', '#F0F0F0'],
        borderWidth: 0
    }]
}));

const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    cutout: '80%',
    rotation: 360,
    circumference: 360,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            enabled: false
        }
    }
});
</script>

<template>
    <section>
        <p class="text-center mb-2">Conclusão do perfil</p>
        <div class="profile-completion-chart">
            <div class="chart-wrapper">
                <div class="chart-container">
                    <Chart type="doughnut" :data="chartData" :options="chartOptions" class="chart-canvas" />
                    <div class="completion-text">
                        {{ completionPercentage }}%
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.profile-completion-chart {
    width: 100px;
    margin: 0 auto;
}

.chart-wrapper {
    position: relative;
    width: 100%;
    padding-top: 100%;
}

.chart-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.chart-canvas {
    display: block;
    width: 100% !important;
    height: 100% !important;
}

.completion-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-weight: bold;
    font-size: 1rem;
    text-align: center;
    pointer-events: none;
}
</style>