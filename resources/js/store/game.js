export default {
    namespaced: true,
    state: {
        title: "This is jeopardy!",
    },
    getters: {
        title(state) {
            return state.title;
        }
    },
    mutations: {
        setTitle(state, title) {
            state.title = title;
        }
    },
    actions: {
        changeTitle({commit}, title) {
            commit('setTitle', `New title: ${title}.`);
        }
    }
}
