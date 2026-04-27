<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';

interface ItemVenda {
  id: string | number;
  quantidade: number;
  preco_unitario: number;
  estoqueMax: number;
  custo_medio: number;
}

const router = useRouter();
const listaProdutos = ref<any[]>([]);
const carregando = ref(false);
const mostrarModal = ref(false);
const mensagemFeedback = ref('');

const venda = ref({
  cliente: '',
  produtos: [{ id: '', quantidade: 1, preco_unitario: 0, estoqueMax: 0, custo_medio: 0 }] as ItemVenda[]
});

onMounted(async () => {
  try {
    const res = await api.get('/produtos');
    listaProdutos.value = res.data;
  } catch (e) {
    console.error("Erro ao carregar produtos");
  }
});

const adicionarItem = () => {
  venda.value.produtos.push({ id: '', quantidade: 1, preco_unitario: 0, estoqueMax: 0, custo_medio: 0 });
};

const removerItem = (index: number) => {
  if (venda.value.produtos.length > 1) {
    venda.value.produtos.splice(index, 1);
  }
};

const atualizarDadosProduto = (index: number) => {
  const item = venda.value.produtos[index];
  const prodOriginal = listaProdutos.value.find(p => p.id === item.id);
  if (prodOriginal) {
    item.preco_unitario = prodOriginal.preco_venda;
    item.estoqueMax = prodOriginal.estoque;
    item.custo_medio = prodOriginal.custo_medio;
  }
};

const lucroTotalGeral = computed(() => {
  return venda.value.produtos.reduce((acc, item) => {
    const lucroUnitario = item.preco_unitario - item.custo_medio;
    return acc + (lucroUnitario * item.quantidade);
  }, 0);
});

const totalVendaGeral = computed(() => {
  return venda.value.produtos.reduce((acc, item) => acc + (item.preco_unitario * item.quantidade), 0);
});

const vendaValida = computed(() => {
  return venda.value.cliente.length > 2 && 
         venda.value.produtos.every(p => p.id && p.quantidade > 0 && p.quantidade <= p.estoqueMax);
});

const finalizarVenda = async () => {
  if (carregando.value || !vendaValida.value) return;

  carregando.value = true;
  try {
    await api.post('/vendas', {
      cliente: venda.value.cliente,
      produtos: venda.value.produtos
    });
    mensagemFeedback.value = "Venda concluída com sucesso!";
    mostrarModal.value = true;
  } catch (error: any) {
    const msg = error.response?.data?.error || 'Erro ao processar venda';
    alert(msg);
  } finally {
    carregando.value = false;
  }
};

const fecharModalEVoltar = () => {
  mostrarModal.value = false;
  router.push('/');
};
</script>

<template>
  <div class="max-w-5xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h2 class="text-3xl font-black text-slate-800">Ponto de Venda</h2>
        <p class="text-slate-500">Registre saídas de estoque e verifique o lucro em tempo real.</p>
      </div>
      <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center gap-3">
        <div class="bg-emerald-500 p-2 rounded-lg text-white">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <span class="block text-xs font-bold text-emerald-600 uppercase">Lucro Estimado</span>
          <span class="text-xl font-black text-emerald-700">R$ {{ lucroTotalGeral.toFixed(2) }}</span>
        </div>
      </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">
      <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wider">Identificação do Cliente</label>
      <input v-model="venda.cliente" type="text" 
             class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all"
             placeholder="Digite o nome completo do cliente">
    </div>

    <div class="space-y-4">
      <div v-for="(item, index) in venda.produtos" :key="index" 
           class="bg-white border border-slate-200 p-6 rounded-2xl shadow-sm flex flex-wrap gap-6 items-end group transition-hover hover:border-indigo-200">
        
        <div class="flex-1 min-w-[280px]">
          <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Produto</label>
          <select v-model="item.id" @change="atualizarDadosProduto(index)" 
                  class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
            <option disabled value="">Selecione para vender</option>
            <option v-for="p in listaProdutos" :key="p.id" :value="p.id">
              {{ p.nome }} — Disponível: {{ p.estoque }}
            </option>
          </select>
        </div>

        <div class="w-32">
          <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Qtd (Max: {{ item.estoqueMax }})</label>
          <input v-model.number="item.quantidade" type="number" 
                 class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl outline-none"
                 :class="item.quantidade > item.estoqueMax ? 'border-red-500 bg-red-50 text-red-600' : 'focus:border-indigo-500'">
        </div>

        <div class="w-40">
          <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Preço Venda (R$)</label>
          <input v-model.number="item.preco_unitario" type="number" step="0.01" 
                 class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl focus:border-indigo-500 outline-none">
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
      <button @click="adicionarItem" class="flex items-center gap-2 text-indigo-600 font-bold hover:text-indigo-800 transition">
        <span class="text-2xl">+</span> Adicionar outro produto
      </button>

      <div class="flex-1"></div>

      <div class="w-full md:w-96 bg-indigo-900 p-6 rounded-3xl text-white shadow-xl shadow-indigo-200 relative overflow-hidden">
        <div class="relative z-10">
          <span class="block text-indigo-300 text-xs font-bold uppercase tracking-widest mb-1">Total a Pagar</span>
          <div class="flex justify-between items-end">
            <span class="text-4xl font-black">R$ {{ totalVendaGeral.toFixed(2) }}</span>
            <button @click="finalizarVenda" 
                    :disabled="!vendaValida || carregando"
                    class="bg-white text-indigo-900 px-6 py-3 rounded-2xl font-bold hover:bg-indigo-50 transition-all flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
              <span v-if="carregando" class="animate-spin rounded-full h-5 w-5 border-b-2 border-indigo-900"></span>
              <span v-else>Finalizar</span>
            </button>
          </div>
        </div>
        <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-800 rounded-full"></div>
      </div>
    </div>

    <div v-if="mostrarModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-3xl p-8 max-w-sm w-full shadow-2xl text-center">
        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h3 class="text-2xl font-black text-slate-800 mb-2">{{ mensagemFeedback }}</h3>
        <p class="text-slate-500 mb-8">A transação foi registrada e o estoque atualizado com sucesso.</p>
        <button @click="fecharModalEVoltar" 
                class="w-full bg-slate-900 text-white py-4 rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-lg shadow-slate-200">
          Entendido, voltar à lista
        </button>
      </div>
    </div>
  </div>
</template>