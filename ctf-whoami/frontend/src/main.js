import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import './assets/styles.css';

const app = createApp(App);

const backendPort = 53499;
const proxyPort = 25998;
const backendUrl = `${window.location.protocol}//${window.location.hostname}:${backendPort}`;
const proxyUrl = `${window.location.protocol}//${window.location.hostname}:${proxyPort}`;
app.config.globalProperties.siteDomain = "whoami.honeylab.hu";

app.config.globalProperties.$http = axios.create({
    baseURL: backendUrl,
});

app.config.globalProperties.$proxy = axios.create({
    baseURL: proxyUrl,
});

app.use(router);
app.mount('#app');
