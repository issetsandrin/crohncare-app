import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './styles/global.css'
import { useAuthStore } from './stores/auth'
import { aplicarTema } from './composables/useTheme'

aplicarTema(localStorage.getItem('tema') || 'verde')

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

const auth = useAuthStore()
auth.initAuth()

app.mount('#app')
