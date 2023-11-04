import { createRouter, createWebHashHistory } from 'vue-router';

import HomePage from '@/views/HomePage.vue';
import PricingPage from '@/views/PricingPage.vue';
import AboutCompany from '@/views/AboutCompany.vue';

const routes = [
    { path: '/', component: HomePage },
    { path: '/pricing', component: PricingPage },
    { path: '/about', component: AboutCompany },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;
