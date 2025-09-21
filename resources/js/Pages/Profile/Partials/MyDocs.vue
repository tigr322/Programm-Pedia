<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm,usePage } from '@inertiajs/vue3';
import { reactive, ref, watchEffect,computed } from 'vue';
import { QuillEditor } from '@vueup/vue-quill'
import openFloatingWindow from '@/windowsopen'
const props = defineProps({
  problems: { type: Array, default: () => [] },
  personalyDocs: { type: Number, default: 0, },
})
const page = usePage()

const expanded = reactive({})

function isExpanded(id) {
  return !!(id && expanded[id])
}

function toggleExpand(id) {
  if (!id) return
  expanded[id] = !expanded[id]
}
const isAuthed    = computed(() => !!page.props.auth?.user)
/* -------------------- ФОРМЫ -------------------- */
// форма для добавления новой ПРОБЛЕМЫ
const problemForm = useForm({
  slug: '',
  title: '',
  description: '',
  metadata: '',
  personaly: false,
});

// формы для добавления РЕШЕНИЙ (по проблеме)
const solutionForms = reactive({});

const form = useForm({})

// если props.problems меняются, гарантируем наличие формы на каждую проблему
watchEffect(() => {
  (props.problems ?? []).forEach((p) => {
    if (!solutionForms[p.id]) {
      solutionForms[p.id] = useForm({slug: '', title: '', content: '', pdf: null });
    }
  });
});

/* ------------- РЕАКТИВНЫЕ ПЕРЕКЛЮЧАТЕЛИ UI ------------- */
const openSolutions = reactive({});
const openAddForm   = reactive({});
const editForms = reactive({});
const openEditForm = reactive({});

const openEdit = (sol, problemId) => {
  const k = String(sol.id);
  if (!editForms[k]) {
    editForms[k] = useForm({
      solution_id: sol.id,          // ключ для серверной логики
      content: sol.content || '',
      pdf: null,
      title: sol.title ?? '',
      slug: sol.slug ?? '',
      summary: sol.summary ?? '',
      markdown: sol.markdown ?? '',
      code: sol.code ?? '',
      language: sol.language ?? 'plaintext',
    });
  }
  openEditForm[k] = !openEditForm[k];
};

const onEditFileChange = (solutionId, e) => {
  const k = String(solutionId);
  if (!editForms[k]) return;
  editForms[k].pdf = e.target.files?.[0] ?? null;
};

const submitEdit = (problemId, solutionId) => {
  const k = String(solutionId);
  editForms[k].post(route('solutions.store', { problem: problemId }), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      openEditForm[k] = false;
      editForms[k].reset(); // очистит временный PDF; контент перезагрузится с сервера
    },
  });
};


const toggleObjKey = (obj, id) => {
  // приведи ключ к строке — иногда это помогает при странных id
  const key = String(id);
  // инициализация и переключение
  obj[key] = !obj[key];
};
function downloadPdf(sol) {
  const href = route ? route('solutions.download', sol.id) : `/solutions/${sol.id}/download`
  window.open(href, '_blank')
}
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
  <section class="space-y-6">
    <header>
      <h2 class="text-lg font-medium text-gray-900">Мои личные документации</h2>
      <p class="mt-1 text-sm text-gray-600">
        Здесь вы можете просмотреть и управлять своими личными документациями.
      </p>
    </header>

   
        Всего личных: <strong>{{ props.personalyDocs }}</strong>

        
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-4">
            📌 Список личных проблем и решений
          </h2>

          <ul class="space-y-4">
            <li
               v-for="prb in props.problems.filter(pr => pr.personaly)"
        :key="prb.id"
              class="border rounded-lg p-4 bg-gray-50 hover:shadow-md transition"
            >
              <div class="flex items-start justify-between gap-4">
                <div>
                  <h3 class="text-lg font-semibold text-indigo-900">
                   {{ prb.slug ?? 'Без названия' }}
                  </h3>

                  <h3 v-if="prb.slug !== prb.description"  class="text-sm font-semibold text-gray-700">
                    Описание: {{ prb.description ?? 'Без описания' }}
                  </h3>


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

                  <button   v-if="isAuthed"
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
                    <div class="text-sm text-gray-500">
                      {{ sol.slug ?? 'Без названия' }}
                  </div>
                    <h3 class="text-lg font-semibold text-indigo-700">
                    Название: {{ sol.title ?? 'Без названия' }}
                  </h3>
                <div class="flex items-center justify-between mb-2">
                  <button
                v-if="sol.pdf_path"
                class="px-3 py-2 text-sm rounded-lg bg-indigo-700 text-white hover:bg-indigo-900"
                @click="downloadPdf(sol)">
                Скачать PDF
                  </button>


                    <button class="px-3 py-2 text-sm rounded-lg border bg-white hover:bg-gray-100"
                            @click="openFloatingWindow({ ...sol, problem: { id: prb.id } })">
                        Открыть в отдельном окне
                    </button>
                  <button   v-if="isAuthed"
                    class="px-3 py-2 text-sm rounded-lg bg-indigo-700 text-white hover:bg-indigo-900"
                    @click="openEdit(sol, prb.id)"
                  >
                    {{ openEditForm[String(sol.id)] ? 'Отменить' : 'Редактировать' }}
                  </button>
                </div>

                <div
      :class="[
        'prose max-w-none ql-editor overflow-hidden pr-3 custom-scroll transition-all duration-300',
        isExpanded(sol.id) ? 'max-h-[700px] overflow-auto' : 'max-h-[100px]'
      ]"
      v-html="sol.content"
    ></div>

    <button
      class="mt-2 text-indigo-600 text-sm hover:underline"
      @click="toggleExpand(sol.id)"
      type="button"
    >
      {{ isExpanded(sol.id) ? 'Свернуть' : 'Показать больше' }}
    </button>

              <div class="flex gap-2 items-center">

              <span class="text-xs text-gray-500">
                {{ new Date(sol.created_at).toLocaleString() }}
              </span>
            </div>

  <!-- Форма редактирования -->
  <div v-if="openEditForm[String(sol.id)]" class="mt-4 rounded-md border p-4 bg-white">
    <h4 class="text-sm font-semibold text-gray-700 mb-3">Редактировать решение</h4>
    <input
          v-model="editForms[String(sol.id)].title"
          type="text"
                placeholder="Краткое название решения"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              required/>
    <div class="space-y-3">
      <label class="block text-sm font-medium text-gray-700">Текст решения</label>
      <QuillEditor
        v-model:content="editForms[String(sol.id)].content"
        contentType="html"
        theme="snow"
        toolbar="full"
        style="height: 240px;"
      />
      <div v-if="editForms[String(sol.id)].errors?.content" class="text-sm text-red-500">
        {{ editForms[String(sol.id)].errors.content }}
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">PDF (необязательно, заменит старый)</label>
        <input
          type="file"
          accept="application/pdf"
          @change="onEditFileChange(sol.id, $event)"
          class="w-full text-sm file:mr-4 file:rounded-md file:border-0 file:bg-indigo-700 file:px-3 file:py-2 file:text-white hover:file:bg-indigo-700"
        />
        <div v-if="editForms[String(sol.id)].errors?.pdf" class="text-sm text-red-500 mt-1">
          {{ editForms[String(sol.id)].errors.pdf }}
        </div>
      </div>

      <div class="flex items-center gap-3 pt-2">
        <button
          type="button"
          class="px-4 py-2 bg-white border rounded-lg hover:bg-gray-50"
          @click="openEditForm[String(sol.id)] = false"
        >
          Отмена
        </button>
        <button
          type="button"
          class="px-4 py-2 bg-indigo-700 text-white rounded-lg hover:bg-indigo-900"
          :disabled="editForms[String(sol.id)].processing"
          @click="submitEdit(prb.id, sol.id)"
        >
          Сохранить изменения
        </button>
      </div>
    </div>
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
                    <input
        v-model="solutionForms[prb.id].title"
        type="text"
        placeholder="Краткое название решения"
        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
      />
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
  </section>
</template>
