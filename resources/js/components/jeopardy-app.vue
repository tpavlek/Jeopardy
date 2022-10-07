<template>
    <div>{{ title }}</div>
    <button @click="changeTitle">Change title</button>
</template>
<script>
import {computed} from "vue";
import {useStore} from "vuex";

export default {
    setup() {
        const store = useStore();

        return {
            title: computed(() => store.getters["game/title"]),
            changeTitle: () => store.dispatch("game/changeTitle", "new title"),
        }
    }
}
</script>
