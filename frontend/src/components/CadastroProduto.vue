<script setup lang="ts">
import { ref } from 'vue';
import api from '../services/api';

const emit = defineEmits(['produtoCriado']);

const carregando = ref(false);
const mensagem = ref('');

const form = ref({
  nome: '',
  preco_venda: 0,
  custo_medio: 0,
  estoque: 0
});

const salvarProduto = async () => {
  if (carregando.value || !form.value.nome) return;

  carregando.value = true;
  mensagem.value = '';

  try {
    await api.post('/produtos', form.value);
    
    mensagem.value = "✓ Produto adicionado ao catálogo!";
    
    form.value = { nome: '', preco_venda: 0, custo_medio: 0, estoque: 0 };
    
    emit('produtoCriado');

    setTimeout(() => mensagem.value = '', 3000);
  } catch (error: any) {
    console.error("Erro ao salvar:", error.response?.data || error.message);
    alert("Erro ao salvar produto. Verifique a conexão com o servidor.");
  } finally {
    carregando.value = false;
  }
};
</script>

<template>
  <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm mb-8 transition-all hover:shadow-md">
    <div class="flex items-center gap-3 mb-6">
      <div class="bg-indigo-600 p-2 rounded-xl text-white shadow-lg shadow-indigo-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
      </div>
      <div>
        <h3 class="text-xl font-black text-slate-800">Novo Produto</h3>
        <p class="text-xs text-slate-400 uppercase font-bold tracking-widest">Cadastro de Catálogo</p>
      </div>
    </div>

    <form @submit.prevent="salvarProduto" class="grid grid-cols-1 md:grid-cols-12 gap-6 items-end">
      <div class="md:col-span-7">
        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Nome do Item</label>
        <input 
          v-model="form.nome" 
          type="text" 
          class="w-full bg-slate-50 border border-slate-200 p-3 rounded-2xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all placeholder:text-slate-300" 
          placeholder="Ex: Teclado Mecânico Gamer" 
          required
        >
      </div>

      <div class="md:col-span-3">
        <label class="block text-xs font-bold text-slate-500 uppercase mb-2 ml-1">Preço Sugerido (R$)</label>
        <input 
          v-model.number="form.preco_venda" 
          type="number" 
          step="0.01" 
          class="w-full bg-slate-50 border border-slate-200 p-3 rounded-2xl focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 outline-none transition-all" 
          required
        >
      </div>

      <div class="md:col-span-2">
        <button 
          type="submit" 
          :disabled="carregando || !form.nome"
          class="w-full bg-indigo-600 text-white py-3 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95"
        >
          <span v-if="carregando" class="animate-spin rounded-full h-4 w-4 border-2 border-white/30 border-t-white"></span>
          <span>Cadastrar</span>
        </button>
      </div>
    </form>

    <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0">
      <p v-if="mensagem" class="mt-4 text-sm font-bold text-emerald-600 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        {{ mensagem }}
      </p>
    </transition>
  </div>
</template>