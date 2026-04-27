<script setup lang="ts">
</script>

<template>
  <div class="min-h-screen bg-slate-50 font-sans antialiased text-slate-900">
    
    <nav class="bg-indigo-700 shadow-lg shadow-indigo-100 border-b border-indigo-800 p-4 sticky top-0 z-40">
      <div class="container mx-auto flex flex-col md:flex-row gap-6 items-center justify-between">
        
        <div class="flex items-center gap-3">
          <div class="bg-white p-2 rounded-xl shadow-inner rotate-3">
            <span class="text-indigo-700 font-black text-xl">DI</span>
          </div>
          <h1 class="font-black text-2xl tracking-tighter text-white">
            ERP <span class="text-indigo-200">DOIS IRMÃOS</span>
          </h1>
        </div>
        
        <div class="flex items-center gap-1 bg-indigo-800/50 p-1 rounded-2xl border border-indigo-600/30">
          <router-link 
            to="/" 
            class="nav-link" 
            active-class="nav-active"
          >
            Estoque
          </router-link>
          
          <router-link 
            to="/compras" 
            class="nav-link" 
            active-class="nav-active"
          >
            Compras
          </router-link>
          
          <router-link 
            to="/vendas" 
            class="nav-link" 
            active-class="nav-active"
          >
            Vendas
          </router-link>
          <router-link 
            to="/historico" 
            class="nav-link" 
            active-class="nav-active"
          >
            Histórico
        </router-link>
        </div>

      </div>
    </nav>

    <main class="container mx-auto p-4 md:p-8">
      <router-view v-slot="{ Component }">
        <transition 
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="transform translate-y-4 opacity-0"
          enter-to-class="transform translate-y-0 opacity-100"
        >
          <component :is="Component" />
        </transition>
      </router-view>
    </main>

    <footer class="text-center py-10 text-slate-400 text-xs font-medium uppercase tracking-widest">
      &copy; 2026 Dois Irmãos — Sistema de Gestão Interna
    </footer>
  </div>
</template>

<style scoped>
@reference "./assets/main.css";

.nav-link {
  @apply px-5 py-2 rounded-xl text-indigo-100 font-bold text-sm transition-all duration-200 hover:bg-indigo-600 hover:text-white;
}

.nav-active {
  @apply bg-white text-indigo-700 shadow-md;
}

:global(::-webkit-scrollbar) {
  width: 8px;
}
:global(::-webkit-scrollbar-track) {
  @apply bg-slate-100;
}
:global(::-webkit-scrollbar-thumb) {
  @apply bg-slate-300 rounded-full border-2 border-slate-100 hover:bg-slate-400;
}
</style>