<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { reactive, ref, watchEffect } from 'vue';
import { QuillEditor } from '@vueup/vue-quill'
import { QuillDeltaToHtmlConverter } from 'quill-delta-to-html';
const props = defineProps({
  // ожидаем уже подгруженные решения: Problem::with('solutions')
  problems: Array,
});

/* -------------------- ФОРМЫ -------------------- */
// форма для добавления новой ПРОБЛЕМЫ
const problemForm = useForm({
  slug: '',
  title: '',
  description: '',
  metadata: '',
});

// формы для добавления РЕШЕНИЙ (по проблеме)
const solutionForms = reactive({});

// если props.problems меняются, гарантируем наличие формы на каждую проблему
watchEffect(() => {
  (props.problems ?? []).forEach((p) => {
    if (!solutionForms[p.id]) {
      solutionForms[p.id] = useForm({ content: '', pdf: null });
    }
  });
});

/* ------------- РЕАКТИВНЫЕ ПЕРЕКЛЮЧАТЕЛИ UI ------------- */
const openSolutions = reactive({});
const openAddForm   = reactive({});


const toggleObjKey = (obj, id) => {
  // приведи ключ к строке — иногда это помогает при странных id
  const key = String(id);
  // инициализация и переключение
  obj[key] = !obj[key];
};

/* -------------------- ХЕНДЛЕРЫ -------------------- */
const onFileChange = (problemId, e) => {
  if (!solutionForms[problemId]) solutionForms[problemId] = useForm({ content: '', pdf: null });
  const file = e.target.files?.[0] ?? null;
  solutionForms[problemId].pdf = file;
};

const submitSolution = (problemId) => {
  if (!solutionForms[problemId]) solutionForms[problemId] = useForm({ content: '', pdf: null });
  solutionForms[problemId].post(route('solutions.store', { problem: problemId }), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      solutionForms[problemId].reset();
      openAddForm[problemId] = false;
    },
  });
};

const submitProblem = () => {
  problemForm.post(route('problems.store'), {
    preserveScroll: true,
    onSuccess: () => problemForm.reset(),
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
              :key="String(prb.id)"
              class="border rounded-lg p-4 bg-gray-50 hover:shadow-md transition"
            >
              <div class="flex items-start justify-between gap-4">
                <div>
                  <h3 class="text-lg font-semibold text-indigo-700">
                    {{ prb.slug ?? 'Без названия' }}
                  </h3>
                  <p class="text-sm text-gray-800 mt-2">
                    {{ prb.content }}
                  </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2">
                  <button
                    class="px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-100"
                    @click="toggleObjKey(openSolutions, prb.id)"
                  >
                    {{ openSolutions[prb.id] ? 'Скрыть решения' : 'Показать решения' }}
                    <span v-if="prb.solutions && prb.solutions.length" class="ml-1 text-xs text-gray-500">
                      ({{ prb.solutions.length }})
                    </span>
                  </button>

                  <button
                    class="px-3 py-2 text-sm rounded-lg bg-indigo-700 text-white hover:bg-indigo-900"
                   @click="toggleObjKey(openAddForm, prb.id)"
                  >
                    {{ openAddForm[prb.id] ? 'Отменить' : 'Добавить решение' }}
                  </button>
                </div>
              </div>

              <!-- Список решений -->
              <div v-if="openSolutions[String(prb.id)]" class="mt-4 space-y-3">
                <h4 class="text-sm font-semibold text-gray-700">Решения:</h4>

                <div v-if="prb.solutions && prb.solutions.length" class="space-y-3">
                  <div v-for="sol in prb.solutions" :key="String(sol.id)" class="rounded-md border bg-white p-3">
                    <div
                      class="prose max-w-none ql-editor max-h-[500px] overflow-auto pr-3 custom-scroll"
                      v-html="sol.content"
                    ></div>

                    <div v-if="sol.pdf_path" class="mt-2">
                      <a
                        :href="route('solutions.download', { solution: sol.id })"
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
              <div v-if="openAddForm[String(prb.id)]" class="mt-5 rounded-md border bg-white p-4">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Добавить решение</h4>

                <form @submit.prevent="submitSolution(prb.id, prb.slug)" class="space-y-4" enctype="multipart/form-data">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Текст решения</label>
                    <QuillEditor
                      v-model:content="solutionForms[prb.id].content"
                      contentType="html"
                      theme="snow"
                      toolbar="full"
                      style="height: 240px;"
                    />
                    <div v-if="solutionForms[prb.id].errors.content" class="text-sm text-red-500 mt-1">
                      {{ solutionForms[prb.id].errors.content }}
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PDF-файл (необязательно)</label>
                    <input
                      type="file"
                      accept="application/pdf"
                      @change="onFileChange(prb.id, $event)"
                      class="w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-700 file:px-3 file:py-2 file:text-white hover:file:bg-indigo-700"
                    />
                    <div v-if="solutionForms[prb.id].errors.pdf" class="text-sm text-red-500 mt-1">
                      {{ solutionForms[prb.id].errors.pdf }}
                    </div>
                  </div>

                  <div class="flex items-center gap-3">
                    <button
                      type="submit"
                      class="px-4 py-2 bg-indigo-700 text-white rounded-lg hover:bg-indigo-900"
                      :disabled="solutionForms[prb.id].processing"
                    >
                      Сохранить
                    </button>
                    <span v-if="solutionForms[prb.id].processing" class="text-sm text-gray-500">Сохраняем…</span>
                    <div v-if="solutionForms[prb.id].recentlySuccessful" class="text-sm text-green-600">Готово!</div>
                  </div>
                </form>
              </div>
            </li>
          </ul>
        </div>

        <!-- Форма добавления ПРОБЛЕМЫ -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-4">
            ➕ Добавить проблему
          </h2>

          <form @submit.prevent="submitProblem" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Проблема (slug)</label>
              <input
                v-model="problemForm.slug"
                type="text"
                placeholder="Краткое название проблемы"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              />
              <div v-if="problemForm.errors.slug" class="text-sm text-red-500 mt-1">{{ problemForm.errors.slug }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Общее (title)</label>
              <textarea
                v-model="problemForm.title"
                rows="3"
                placeholder="Краткое резюме"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              ></textarea>
              <div v-if="problemForm.errors.title" class="text-sm text-red-500 mt-1">{{ problemForm.errors.title }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Описание проблемы</label>
              <textarea
                v-model="problemForm.description"
                rows="4"
                placeholder="Опиши проблему подробнее"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              ></textarea>
              <div v-if="problemForm.errors.description" class="text-sm text-red-500 mt-1">{{ problemForm.errors.description }}</div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Метаданные</label>
              <textarea
                v-model="problemForm.metadata"
                rows="3"
                placeholder='JSON или свободный текст (например: {"os":"macOS","php":"8.3"})'
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              ></textarea>
              <div v-if="problemForm.errors.metadata" class="text-sm text-red-500 mt-1">{{ problemForm.errors.metadata }}</div>
            </div>

            <div>
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-700 text-white rounded-lg hover:bg-indigo-900 transition"
                :disabled="problemForm.processing"
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
<style>
.custom-scroll::-webkit-scrollbar { width: 8px; }
.custom-scroll::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 6px; }
.custom-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
</style>
