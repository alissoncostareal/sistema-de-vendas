<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';
import CadastroProduto from '../components/CadastroProduto.vue';

interface Produto {
  id: number;
  nome: string;
  custo_medio: string | number;
  preco_venda: string | number;
  estoque: number;
}

const produtos = ref<Produto[]>([]);
const carregando = ref(true);
const editandoId = ref<number | null>(null);
const formEdit = ref({ nome: '', preco_venda: 0 });

const carregarProdutos = async () => {
  carregando.value = true;
  try {
    const response = await api.get('/produtos');
    produtos.value = response.data;
  } catch (error) {
    console.error("Erro ao carregar produtos:", error);
  } finally {
    carregando.value = false;
  }
};

const deletarProduto = async (id: number) => {
  if (!confirm("Tem certeza que deseja excluir este produto?")) return;
  
  try {
    await api.delete(`/produtos/${id}`);
    await carregarProdutos();
  } catch (error: any) {
    alert(error.response?.data?.message || "Erro ao deletar produto. Ele pode ter movimentações vinculadas.");
  }
};

const iniciarEdicao = (produto: Produto) => {
  editandoId.value = produto.id;
  formEdit.value = { 
    nome: produto.nome, 
    preco_venda: Number(produto.preco_venda) 
  };
};

const salvarEdicao = async (id: number) => {
  try {
    await api.put(`/produtos/${id}`, formEdit.value);
    editandoId.value = null;
    await carregarProdutos();
  } catch (error) {
    alert("Erro ao atualizar produto.");
  }
};

onMounted(carregarProdutos);

const valorTotalEstoque = computed(() => {
  return produtos.value.reduce((acc, p) => acc + (Number(p.custo_medio) * p.estoque), 0);
});

const itensSemEstoque = computed(() => {
  return produtos.value.filter(p => p.estoque <= 0).length;
});
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="text-3xl font-black text-slate-800">Painel de Estoque</h2>
        <p class="text-slate-500">Monitore níveis de produtos, custos e preços de venda.</p>
      </div>
      
      <div class="flex gap-3">
        <div class="bg-white border border-slate-200 px-4 py-2 rounded-xl shadow-sm">
          <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Valor em Estoque</span>
          <span class="text-lg font-bold text-slate-700">R$ {{ valorTotalEstoque.toFixed(2) }}</span>
        </div>
        <div class="bg-white border border-slate-200 px-4 py-2 rounded-xl shadow-sm">
          <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-tighter">Críticos (Zero)</span>
          <span class="text-lg font-bold text-red-500">{{ itensSemEstoque }}</span>
        </div>
      </div>
    </div>

    <CadastroProduto @produto-criado="carregarProdutos" />

    <div v-if="carregando && produtos.length === 0" class="bg-white rounded-3xl p-20 border border-slate-100 flex flex-col items-center justify-center shadow-sm">
      <div class="relative">
        <div class="w-12 h-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
      </div>
      <p class="mt-4 text-slate-400 font-medium animate-pulse">Sincronizando banco de dados...</p>
    </div>

    <div v-else class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm transition-all" :class="{'opacity-50 pointer-events-none': carregando}">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Produto</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Custo Médio</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Preço Venda</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Estoque Atual</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="produto in produtos" :key="produto.id" class="hover:bg-indigo-50/30 transition-colors group">
              <td class="px-6 py-5">
                <div v-if="editandoId === produto.id">
                  <input v-model="formEdit.nome" class="bg-white border border-indigo-300 rounded px-2 py-1 text-sm w-full outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div v-else>
                  <span class="block font-bold text-slate-700 group-hover:text-indigo-700 transition-colors">{{ produto.nome }}</span>
                  <span class="text-[10px] text-slate-400 uppercase tracking-tighter font-mono">ID: #{{ produto.id }}</span>
                </div>
              </td>
              
              <td class="px-6 py-5 text-center text-slate-600 font-medium">
                R$ {{ Number(produto.custo_medio).toFixed(2) }}
              </td>
              
              <td class="px-6 py-5 text-center text-slate-600 font-medium">
                <div v-if="editandoId === produto.id">
                  <input v-model.number="formEdit.preco_venda" type="number" step="0.01" class="bg-white border border-indigo-300 rounded px-2 py-1 text-sm w-24 text-center outline-none focus:ring-2 focus:ring-indigo-200">
                </div>
                <div v-else>
                  R$ {{ Number(produto.preco_venda).toFixed(2) }}
                </div>
              </td>
              
              <td class="px-6 py-5 text-center">
                <span 
                  class="inline-block px-3 py-1 rounded-full text-xs font-black transition-all"
                  :class="produto.estoque > 5 ? 'bg-emerald-100 text-emerald-700' : (produto.estoque > 0 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')"
                >
                  {{ produto.estoque }} unidades
                </span>
              </td>

              <td class="px-6 py-5 text-center">
                <div v-if="editandoId === produto.id" class="flex justify-center gap-2">
                  <button @click="salvarEdicao(produto.id)" class="text-emerald-600 hover:scale-110 transition-transform" title="Salvar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </button>
                  <button @click="editandoId = null" class="text-slate-400 hover:scale-110 transition-transform" title="Cancelar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
                <div v-else class="flex justify-center gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                  <button @click="iniciarEdicao(produto)" class="text-indigo-400 hover:text-indigo-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                  </button>
                  <button @click="deletarProduto(produto.id)" class="text-red-300 hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="produtos.length === 0" class="p-20 text-center">
        <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
        </div>
        <p class="text-slate-500 font-bold text-lg">Seu catálogo está vazio.</p>
      </div>
    </div>
  </div>
</template>