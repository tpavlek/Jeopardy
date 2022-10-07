export default {
    namespaced: true,
    state: {
        categories: [],
    },
    getters: {
        categories(state) {
            return state.categories;
        }
    },
    mutations: {
        setCategories(state, categories) {
            state.categories = categories;
        }
    },
    actions: {
        async load({commit}, slug) {
            const {data} = await axios.get(`/game/${slug}/board`);

            commit('setCategories', data.categories);
        }
    }
}
