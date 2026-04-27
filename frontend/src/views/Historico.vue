<script setup lang="ts">
import { ref, onMounted } from 'vue';
import api from '../services/api';

const abaAtiva = ref<'compras' | 'vendas'>('compras');
const carregando = ref(true);
const registros = ref<any[]>([]);

const carregarDados = async () => {
  carregando.value = true;
  registros.value = [];
  try {
    const rota = abaAtiva.value === 'compras' ? '/compras' : '/vendas';
    const response = await api.get(rota);
    registros.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar histórico:", error);
  } finally {
    carregando.value = false;
  }
};

const mudarAba = (novaAba: 'compras' | 'vendas') => {
  abaAtiva.value = novaAba;
  carregarDados();
};

onMounted(carregarDados);

const formatarData = (dataStr: string) => {
  return new Date(dataStr).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h2 class="text-3xl font-black text-slate-800">Histórico de Operações</h2>
        <p class="text-slate-500">Rastreie todas as entradas de fornecedores e vendas para clientes.</p>
      </div>

      <div class="flex bg-slate-200/50 p-1 rounded-2xl border border-slate-200">
        <button 
          @click="mudarAba('compras')"
          :class="abaAtiva === 'compras' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
          class="px-6 py-2 rounded-xl text-sm font-bold transition-all"
        >
          Compras
        </button>
        <button 
          @click="mudarAba('vendas')"
          :class="abaAtiva === 'vendas' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
          class="px-6 py-2 rounded-xl text-sm font-bold transition-all"
        >
          Vendas
        </button>
      </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm transition-all">
      <div v-if="carregando" class="p-24 flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
        <p class="mt-4 text-slate-400 font-medium animate-pulse">Sincronizando registros...</p>
      </div>

      <div v-else-if="registros.length === 0" class="p-24 text-center">
        <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
           <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <p class="text-slate-500 font-bold">Nenhuma {{ abaAtiva }} registrada ainda.</p>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Data / Hora</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">
                {{ abaAtiva === 'compras' ? 'Fornecedor' : 'Cliente' }}
              </th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Produtos Movimentados</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Valor Total</th>
              <th v-if="abaAtiva === 'vendas'" class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Lucro Estimado</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="item in registros" :key="item.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-5 text-sm text-slate-400 font-mono">
                {{ formatarData(item.created_at) }}
              </td>

              <td class="px-6 py-5 font-bold text-slate-700 capitalize">
                {{ abaAtiva === 'compras' ? item.fornecedor : item.cliente }}
              </td>

              <td class="px-6 py-5">
                <div class="flex flex-wrap gap-1">
                  <span v-for="p in item.produtos" :key="p.id" class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-lg border border-slate-200 font-bold">
                    {{ p.pivot.quantidade }}x {{ p.nome }}
                  </span>
                </div>
              </td>

              <td class="px-6 py-5 text-right font-bold text-slate-700">
               R$ {{ 
                    abaAtiva === 'compras' 
                    ? Number(item.valor_total || 0).toFixed(2) 
                    : Number(item.total || 0).toFixed(2) 
                }}
              </td>

              <td v-if="abaAtiva === 'vendas'" class="px-6 py-5 text-right font-black text-emerald-500">
                + R$ {{ Number(item.lucro || 0).toFixed(2) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>