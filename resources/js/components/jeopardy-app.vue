<template>
    <div>Loaded categories:</div>
    <ul>
        <li v-for="category in categories">{{ category }}</li>
    </ul>
</template>
<script>
import {computed} from "vue";
import {useStore} from "vuex";

export default {
    setup() {
        const store = useStore();
        store.dispatch("game/load", "default");

        return {
            categories: computed(() => store.getters["game/categories"]),
        }
    }
}
</script>
