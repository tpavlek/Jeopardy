import {createStore} from "vuex";
import game from "./store/game";

const store = createStore({
    modules: {
        game,
    }
})

export default store;
