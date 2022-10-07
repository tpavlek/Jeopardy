import './bootstrap';
import {createApp} from 'vue';
import store from "./store";

const app = createApp({});

const components = import.meta.glob('./**/*.vue', { eager: true })

Object.entries(components).forEach(([path, definition]) => {
    const componentName = path.split('/').pop().replace(/\.\w+$/, '')
    app.component(componentName, definition.default)
});

app.use(store);
app.mount('#app');
