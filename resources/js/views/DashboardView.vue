<script setup>
import { onMounted } from 'vue';

onMounted(() => {
    document.title = 'Dashboard';
});
</script>
<template>
    <section class="container">
        <h1 class="page-title">Dashboard</h1>
        <!-- Stats Grid -->
        <div class="row g-4">
            <div class="col-md-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="stat-title">ORDERS</div>
                    <div class="stat-value">1,587</div>
                    <div class="stat-change">$118 <span>From previous period</span></div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="stat-title">REVENUE</div>
                    <div class="stat-value">$46,782</div>
                    <div class="stat-change">$433 <span>From previous period</span></div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="stat-title">AVERAGE PRICE</div>
                    <div class="stat-value">$15.9</div>
                    <div class="stat-change negative">$25 <span>From previous period</span></div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="stat-card h-100">
                    <div class="stat-title">PRODUCT SOLD</div>
                    <div class="stat-value">1,890</div>
                    <div class="stat-change">$13.8 <span>Last year</span></div>
                </div>
            </div>
        </div>
    </section>
</template>
<style scoped>
.stat-card {
    background-color: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-title {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 600;
    color: var(--text-title);
    margin-bottom: 5px;
}

.stat-change {
    font-size: 0.9rem;
    color: #10b981;
}

.stat-change.negative {
    color: #ef4444;
}

.stat-change span {
    color: #6b7280;
    display: block;
}
</style>