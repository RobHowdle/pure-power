import "../css/app.css";
import "./bootstrap";

import {createApp} from "vue";
import App from "./App.vue";
import router from "./router";
import axios from "axios";

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

createApp(App).use(router).mount("#app");
