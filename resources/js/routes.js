import ListOfLogs from './pages/ListOfLogs.vue';
import OneLog from './pages/OneLog.vue';

export default [
    {
        path: '/',
        name: 'list',
        component: ListOfLogs,
    },
    {
        path: '/:id',
        name: 'one',
        component: OneLog,
    },
];
