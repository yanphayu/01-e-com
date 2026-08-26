import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'
import { getLocale } from './i18n'

document.documentElement.setAttribute('lang', getLocale())

createApp(App).use(router).mount('#app')
