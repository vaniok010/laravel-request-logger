import axios from 'axios';
import 'bootstrap';
import { createApp } from 'vue/dist/vue.esm-bundler.js';
import { createRouter, createWebHistory } from 'vue-router';
import Base from './base';
import Routes from './routes';

let token = document.head.querySelector("meta[name='csrf-token']");

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

const app = createApp({
    data() {
        return {};
    },
});

app.config.globalProperties.$http = axios.create();
const router = createRouter({
    history: createWebHistory(window.RequestLogger.basePath),
    routes: Routes,
});

app.use(router);
app.mixin(Base);
app.mount('#requestLogger');