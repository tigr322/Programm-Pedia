<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })

const props = defineProps({
  q: String,
  problems: Array,
  solutions: Array,
})

const q = ref(props.q ?? '')

let t
watch(q, (v) => {
  clearTimeout(t)
  t = setTimeout(() => {
    router.get('/search', { q: v }, { preserveState: true, replace: true })
  }, 250)
})
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto">
    <input
      v-model="q"
      placeholder="Поиск по ProgrammPedia…"
      class="w-full border rounded p-3"
    />

    <div v-if="q" class="mt-6 space-y-8">
      <section v-if="problems?.length">
        <h2 class="font-semibold mb-2">Проблемы</h2>
        <ul class="space-y-2">
          <li v-for="p in problems" :key="p.id">
            <Link :href="route('problems.show', p.slug)">{{ p.title }}</Link>

            <div class="text-sm text-gray-500">
              {{ (p.description || '').slice(0, 140) }}<span v-if="(p.description || '').length > 140">…</span>
            </div>
          </li>
        </ul>
      </section>

      <section v-if="solutions?.length">
        <h2 class="font-semibold mb-2">Решения</h2>
        <ul class="space-y-2">
            <li v-for="s in solutions" :key="s.id">
                <Link :href="route('problems.show', [s.problem.slug, s.id])">
                    Решение #{{ s.id }}
                </Link>
                <div class="text-sm text-gray-500">
                    {{ (s.content || '').slice(0, 140) }}<span v-if="(s.content || '').length > 140">…</span>
                </div>
            </li>
        </ul>
      </section>

      <p v-if="!problems?.length && !solutions?.length" class="text-gray-500">
        Ничего не найдено.
      </p>
    </div>
  </div>
</template>
