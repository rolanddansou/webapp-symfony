<template>
  <div class="min-h-screen flex flex-col">
    <!-- Navigation -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-40">
      <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center">
          <Link href="/" class="flex items-center group">
            <span class="text-2xl font-bold text-primary-600 group-hover:text-primary-700 transition-colors">App</span>
          </Link>

          <div class="hidden sm:ml-8 sm:flex sm:space-x-8">
            <Link v-for="link in menuLinks" :key="link.name" :href="link.link"
                  class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors"
                  :class="[isUrlActive(link.link) ? 'border-primary-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700']">
              {{ link.name }}
            </Link>
          </div>
        </div>

        <div class="flex items-center space-x-4">
          <slot name="header-right" />
          <Link href="/login" class="text-sm font-medium text-gray-500 hover:text-gray-700">Se connecter</Link>
          <Link href="/" class="btn btn-primary">S'inscrire</Link>
        </div>
      </nav>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
      <slot />
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-auto">

    </footer>
  </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import menuLinks from '@/navlinks';

const page = usePage();

const isUrlActive = (url) => {
    if (!url) return false;
    return page.url === url || page.url.startsWith(url + '?');
}
</script>
