import './bootstrap';
import { createApp, h } from 'vue'
import { createInertiaApp, Head, Link } from '@inertiajs/vue3'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import AuthenticatedLayout from '../../resources/js/Layouts/AuthenticatedLayout.vue';
import VueApexCharts from "vue3-apexcharts";

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    let page = pages[`./Pages/${name}.vue`]
    page.default.layout = name.startsWith('Auth/') ? undefined : AuthenticatedLayout
    return page
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(VueApexCharts)
      
      .component('Head', Head)
      .component('Link', Link)      

      .mount(el)
  },
})