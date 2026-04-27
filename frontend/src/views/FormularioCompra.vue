<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

// Interface para manter o contrato de dados limpo
interface ItemCompra {
  id: string | number;
  quantidade: number;
  preco_unitario: number;
}

const router = useRouter();
const listaProdutos = ref<any[]>([]);
const carregando = ref(false);
const mostrarModal = ref(false);
const buscandoProdutos = ref(true);

const compra = ref({
  fornecedor: '',
  produtos: [{ id: '', quantidade: 1, preco_unitario: 0 }] as ItemCompra[]
});

onMounted(async () => {
  try {
    const res = await api.get('/produtos');
    listaProdutos.value = res.data;
  } catch (error) {
    console.error("Erro ao buscar produtos:", error);
  } finally {
    buscandoProdutos.value = false;
  }
});

const adicionarItem = () => {
  compra.value.produtos.push({ id: '', quantidade: 1, preco_unitario: 0 });
};

const removerItem = (index: number) => {
  if (compra.value.produtos.length > 1) {
    compra.value.produtos.splice(index, 1);
  }
};

const totalCompra = computed(() => {
  return compra.value.produtos.reduce((acc, item) => acc + (item.preco_unitario * item.quantidade), 0);
});

const formularioValido = computed(() => {
  return compra.value.fornecedor.length > 2 && 
         compra.value.produtos.every(p => p.id && p.quantidade > 0 && p.preco_unitario > 0);
});

const enviarCompra = async () => {
  if (carregando.value || !formularioValido.value) return;

  carregando.value = true;
  try {
    await api.post('/compras', compra.value);
    mostrarModal.value = true;
  } catch (error) {
    alert('Erro ao registrar compra. Verifique a conexão com o servidor.');
  } finally {
    carregando.value = false;
  }
};

const concluirEVoltar = () => {
  mostrarModal.value = false;
  router.push('/');
};
</script>

<template>
  <div class="max-w-5xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h2 class="text-3xl font-black text-slate-800">Entrada de Mercadoria</h2>
        <p class="text-slate-500">Registre novas compras para atualizar o custo médio e estoque.</p>
      </div>
      <div class="bg-blue-50 border border-blue-100 p-4 rounded-2xl flex items-center gap-3">
        <div class="bg-blue-600 p-2 rounded-lg text-white font-bold">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 100-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
          </svg>
        </div>
        <div>
          <span class="block text-xs font-bold text-blue-600 uppercase">Investimento Total</span>
          <span class="text-xl font-black text-blue-700">R$ {{ totalCompra.toFixed(2) }}</span>
        </div>
      </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">
      <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Fornecedor ou Origem</label>
      <input v-model="compra.fornecedor" type="text" 
             class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 outline-none transition-all"
             placeholder="Ex: Distribuidora Oficial de Hardware">
    </div>

    <div v-if="buscandoProdutos" class="text-center py-10">
       <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
    </div>

    <div v-else class="space-y-4">
      <div v-for="(item, index) in compra.produtos" :key="index" 
           class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm flex flex-wrap gap-6 items-end group transition-all hover:shadow-md">
        
        <div class="flex-1 min-w-[280px]">
          <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Produto Selecionado</label>
          <select v-model="item.id" 
                  class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
            <option disabled value="">Selecione para dar entrada</option>
            <option v-for="p in listaProdutos" :key="p.id" :value="p.id">
              {{ p.nome }} — (Atual: {{ p.estoque }})
            </option>
          </select>
        </div>

        <div class="w-32">
          <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Qtd Comprada</label>
          <input v-model.number="item.quantidade" type="number" min="1"
                 class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:border-blue-500 outline-none">
        </div>

        <div class="w-40">
          <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Preço de Custo (R$)</label>
          <input v-model.number="item.preco_unitario" type="number" step="0.01" 
                 class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:border-blue-500 outline-none">
        </div>

        <button @click="removerItem(index)" 
                class="bg-slate-100 text-slate-400 p-3 rounded-xl hover:bg-red-50 hover:text-red-500 transition-all">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
        </button>
      </div>
    </div>

    <div class="mt-8 flex flex-col md:flex-row gap-6 items-center">
      <button @click="adicionarItem" class="flex items-center gap-2 text-blue-600 font-bold hover:text-blue-800 transition">
        <span class="text-2xl text-blue-500">+</span> Adicionar outro item à nota
      </button>

      <div class="flex-1"></div>

      <button @click="enviarCompra" 
              :disabled="!formularioValido || carregando"
              class="w-full md:w-80 py-4 rounded-2xl font-bold text-white shadow-xl transition-all flex justify-center items-center gap-3"
              :class="formularioValido && !carregando ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-200' : 'bg-slate-300 cursor-not-allowed'">
        <span v-if="carregando" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
        <span v-else>Confirmar Entrada</span>
      </button>
    </div>

    <div v-if="mostrarModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 text-center">
      <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl scale-100 transition-transform">
        <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
        </div>
        <h3 class="text-2xl font-black text-slate-800 mb-2">Entrada Concluída!</h3>
        <p class="text-slate-500 mb-8">O estoque foi abastecido e os custos médios foram recalculados.</p>
        <button @click="concluirEVoltar" 
                class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
          Ok, ver estoque
        </button>
      </div>
    </div>
  </div>
</template>