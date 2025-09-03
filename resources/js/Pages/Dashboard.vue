<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
const props = defineProps({
  problems: Array,
});

// форма для добавления новой проблемы
const form = useForm({
  slug: '',
  title: '',
  description: '',
  metadata: '',
});

// forms на каждую проблему
const forms = reactive(
  Object.fromEntries(
    (props.problems ?? []).map(p => [
      p.id,
      useForm({ content: '', pdf: null })
    ])
  )
);

// разворот/сворачивание блоков "Решения" и "Добавить решение"
const openSolutions = ref({});
const openAddForm   = ref({});

const toggle = (obj, id) => { obj[id] = !obj[id]; };

const onFileChange = (problemId, e) => {
  const file = e.target.files?.[0] ?? null;
  forms[problemId].pdf = file;
};

const submitSolution = (problemId) => {
  forms[problemId].post(route('solutions.store', problemId), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      forms[problemId].reset();
      openAddForm.value[problemId] = false;
    },
  });
};
</script>

<template>
  <Head title="MyProgrammPedia" />

  <AuthenticatedLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

        <!-- Список проблем -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-4">
            📌 Список проблем и решений
          </h2>

          <ul class="space-y-4">
            <li
              v-for="prb in problems"
              :key="prb.id"
              class="border rounded-lg p-4 bg-gray-50 hover:shadow-md transition"
            >
              <div class="flex items-start justify-between gap-4">
                <div>
                  <h3 class="text-lg font-semibold text-cyan-700">
                    {{ prb.slug ?? 'Без названия' }}
                  </h3>
                  <p class="text-sm text-gray-800 mt-2">
                    {{ prb.content }}
                  </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                  <button
                    class="px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-100"
                    @click="toggle(openSolutions.value, prb.id)"
                  >
                    {{ (openSolutions.value?.[prb.id]) ? 'Скрыть решения' : 'Показать решения' }}
                    <span v-if="prb.solutions?.length" class="ml-1 text-xs text-gray-500">
                      ({{ prb.solutions.length }})
                    </span>
                  </button>

                  <button
                    class="px-3 py-2 text-sm rounded-lg bg-cyan-600 text-white hover:bg-cyan-700"
                    @click="toggle(openAddForm.value, prb.id)"
                  >
                    {{ (openAddForm.value?.[prb.id]) ? 'Отменить' : '➕ Добавить решение' }}
                  </button>
                </div>
              </div>

              <!-- Список решений -->
              <div v-if="openSolutions.value?.[prb.id]" class="mt-4 space-y-3">
                <h4 class="text-sm font-semibold text-gray-700">Решения:</h4>

                <div
                  v-if="prb.solutions?.length"
                  class="space-y-3"
                >
                  <div
                    v-for="sol in prb.solutions"
                    :key="sol.id"
                    class="rounded-md border bg-white p-3"
                  >
                    <p class="text-sm text-gray-800 whitespace-pre-wrap">
                      {{ sol.content ?? '—' }}
                    </p>
                    <div v-if="sol.pdf_path" class="mt-2">
                      <a
                        :href="route('solutions.download', sol.id)"
                        target="_blank"
                        class="inline-flex items-center text-sm underline hover:no-underline"
                      >
                        Скачать PDF
                      </a>
                    </div>
                    <div class="text-xs text-gray-500 mt-2">
                      {{ new Date(sol.created_at).toLocaleString() }}
                    </div>
                  </div>
                </div>

                <div v-else class="text-sm text-gray-500">
                  Пока нет решений.
                </div>
              </div>

              <!-- Форма добавления решения -->
              <div v-if="openAddForm.value?.[prb.id]" class="mt-5 rounded-md border bg-white p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Добавить решение</h4>

                <form @submit.prevent="submitSolution(prb.id)" class="space-y-4" enctype="multipart/form-data">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Текст решения</label>
                    <textarea
                      v-model="forms[prb.id].content"
                      rows="4"
                      placeholder="Опиши решение…"
                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
                    ></textarea>
                    <div v-if="forms[prb.id].errors.content" class="text-sm text-red-500 mt-1">
                      {{ forms[prb.id].errors.content }}
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PDF-файл (необязательно)</label>
                    <input
                      type="file"
                      accept="application/pdf"
                      @change="onFileChange(prb.id, $event)"
                      class="w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-cyan-600 file:px-3 file:py-2 file:text-white hover:file:bg-cyan-700"
                    />
                    <div v-if="forms[prb.id].errors.pdf" class="text-sm text-red-500 mt-1">
                      {{ forms[prb.id].errors.pdf }}
                    </div>
                  </div>

                  <div class="flex items-center gap-3">
                    <button
                      type="submit"
                      class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700"
                      :disabled="forms[prb.id].processing"
                    >
                      Сохранить
                    </button>
                    <span v-if="forms[prb.id].processing" class="text-sm text-gray-500">
                      Сохраняем…
                    </span>
                    <div v-if="forms[prb.id].recentlySuccessful" class="text-sm text-green-600">
                      Готово!
                    </div>
                  </div>
                </form>
              </div>
            </li>
          </ul>
        </div>

        <!-- Форма добавления -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-4">
            ➕ Добавить проблему
          </h2>

          <form @submit.prevent="form.post(route('problems.store'))" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Проблема</label>
              <input
                v-model="form.slug"
                type="text"
                placeholder="Краткое название проблемы"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
              />
              <div v-if="form.errors.slug" class="text-sm text-red-500 mt-1">{{ form.errors.slug }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Общее</label>
              <textarea
                v-model="form.title"
                rows="4"
                placeholder="Опиши решение проблемы"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
              ></textarea>
              <div v-if="form.errors.title" class="text-sm text-red-500 mt-1">{{ form.errors.title }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Описание проблемы</label>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="Опиши решение проблемы"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
              ></textarea>
              <div v-if="form.errors.description" class="text-sm text-red-500 mt-1">{{ form.errors.description }}</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Метаданные</label>
              <textarea
                v-model="form.metadata"
                rows="4"
                placeholder="Опиши решение проблемы"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500"
              ></textarea>
              <div v-if="form.errors.metadata" class="text-sm text-red-500 mt-1">{{ form.errors.metadata }}</div>
            </div>
            <div>
              <button
                type="submit"
                class="px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition"
              >
                Добавить
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
