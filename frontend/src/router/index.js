import { createRouter, createWebHistory } from 'vue-router';
import ListaProdutos from '../views/ListaProdutos.vue';
import FormularioCompra from '../views/FormularioCompra.vue';
import FormularioVenda from '../views/FormularioVenda.vue';
import Historico from '../views/Historico.vue';

const routes = [
  { 
    path: '/', 
    component: ListaProdutos 
  },
  { 
    path: '/compras', 
    component: FormularioCompra 
  },
  { 
    path: '/vendas', 
    component: FormularioVenda
  },
  { 
    path: '/historico', 
    component: Historico
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;