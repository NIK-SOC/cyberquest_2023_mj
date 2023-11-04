import { createRouter, createWebHistory } from 'vue-router';
import PairDevice from './components/PairDevice.vue';
import DeviceList from './components/DeviceList.vue';

const routes = [
    { path: '/', component: DeviceList },
    { path: '/pair-device', component: PairDevice },
    { path: '/device-list', component: DeviceList },
];

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
});

export default router;
