<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white flex flex-col">
    <!-- Header -->
   
    

    <!-- Hero Section -->
    <section class="flex-1 flex items-center">
      <div class="container mx-auto px-4 py-20 grid grid-cols-1 md:grid-cols-2 gap-10 items-center">
        <div>
          <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6">
            База знаний для программистов <span class="text-indigo-600">Laravel</span>
          </h2>
          <p class="text-lg text-gray-600 mb-6">
            ProgrammPedia — это практичные инструкции, решения ошибок и готовые примеры кода.
            Быстрый поиск, только проверенные ответы.
          </p>
          <div class="flex gap-4">
            <Link
              href="/search"
              class="px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium shadow hover:bg-indigo-500"
            >
              Начать поиск
            </Link>
            <Link
              href="/about"
              class="px-6 py-3 rounded-lg bg-gray-200 text-gray-700 font-medium hover:bg-gray-300"
            >
              Узнать больше
            </Link>
          </div>
        </div>

        <div class="relative">
         
          <div class="absolute -bottom-6 -left-6 bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg shadow">
            🚀 Решения для Laravel, Vue, Docker
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="bg-gray-50 py-16">
      <div class="container mx-auto px-4 grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="p-6 bg-white rounded-xl shadow">
          <h3 class="font-bold text-lg mb-2">🔎 Умный поиск</h3>
          <p class="text-gray-600">
            Находит нужное решение по ошибке или ключевым словам, с подсказками и синонимами.
          </p>
        </div>
        <div class="p-6 bg-white rounded-xl shadow">
          <h3 class="font-bold text-lg mb-2">📚 Реальные примеры</h3>
          <p class="text-gray-600">
            Решения из практики: код, миграции, конфиги. Минимум теории — максимум пользы.
          </p>
        </div>
        <div class="p-6 bg-white rounded-xl shadow">
          <h3 class="font-bold text-lg mb-2">⚡ Быстрый старт</h3>
          <p class="text-gray-600">
            Просто копируй и запускай. Всё протестировано на Laravel 11/12.
          </p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-6 bg-white border-t">
      <div class="container mx-auto px-4 text-center text-gray-500 text-sm">
        © 2025 ProgrammPedia. Сделано для программистов ❤
      </div>
    </footer>
  </div>
</template>
