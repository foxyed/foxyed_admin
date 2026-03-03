<script setup>
import {inject, onMounted, ref, useTemplateRef} from 'vue'
import DataTable from "../../Components/DataTable.vue";

const notify = inject('notify')

// shared meta for selects
const meta = ref({ categories: [], teachers: [] })

const loadMeta = async () => {
  const { data } = await axios.get('/courses/api/meta')
  meta.value = data
}

onMounted(loadMeta)

// -------- Categories --------
const catDt = useTemplateRef('catDt')
const catModal = ref(false)
const catEditing = ref(null)
const catFields = ref({ name: '', slug: '', icon: '', active: true })

const openCreateCategory = () => {
  catEditing.value = null
  catFields.value = { name: '', slug: '', icon: '', active: true }
  catModal.value = true
}

const openEditCategory = (row) => {
  catEditing.value = row
  catFields.value = { name: row.name, slug: row.slug, icon: row.icon ?? '', active: !!row.active }
  catModal.value = true
}

const saveCategory = async () => {
  try {
    if (!catEditing.value) {
      const res = await axios.post('/courses/api/categories/create', catFields.value)
      if (res.data.success) {
        notify({ type: 'success', message: 'Categoria creata' })
        catModal.value = false
        catDt.value.reload()
        await loadMeta()
      }
      return
    }
    const res = await axios.post(`/courses/api/categories/${catEditing.value.id}/update`, catFields.value)
    if (res.data.success) {
      notify({ type: 'success', message: 'Categoria aggiornata' })
      catModal.value = false
      catDt.value.reload()
      await loadMeta()
    }
  } catch (e) {
    notify({ type: 'error', message: e?.response?.data?.message ?? 'Errore' })
  }
}

const deleteCategory = async (row) => {
  if (!confirm(`Eliminare la categoria ${row.name}?`)) return
  const res = await axios.post(`/courses/api/categories/${row.id}/delete`)
  if (res.data.success) {
    notify({ type: 'success', message: 'Categoria eliminata' })
    catDt.value.reload()
    await loadMeta()
  }
}

const toggleCategoryActive = async (row) => {
  const res = await axios.post(`/courses/api/categories/${row.id}/toggle-active`)
  if (res.data.success) {
    notify({ type: 'success', message: res.data.active ? 'Categoria attivata' : 'Categoria disattivata' })
    catDt.value.reload()
    await loadMeta()
  }
}

// -------- Courses --------
const courseDt = useTemplateRef('courseDt')
const courseModal = ref(false)
const courseEditing = ref(null)
const courseFields = ref({
  course_category_id: null,
  teacher_user_id: null,
  title: '',
  code: '',
  description: '',
  price: 0,
  active: true,
})

const openCreateCourse = () => {
  courseEditing.value = null
  courseFields.value = {
    course_category_id: meta.value.categories?.[0]?.id ?? null,
    teacher_user_id: meta.value.teachers?.[0]?.id ?? null,
    title: '',
    code: '(auto)',
    description: '',
    price: 0,
    active: true,
  }
  courseModal.value = true
}

const openEditCourse = (row) => {
  courseEditing.value = row
  courseFields.value = {
    course_category_id: row.course_category_id,
    teacher_user_id: row.teacher_user_id,
    title: row.title,
    code: row.code,
    description: row.description ?? '',
    price: Number(row.price ?? 0),
    active: !!row.active,
  }
  courseModal.value = true
}

const saveCourse = async () => {
  try {
    if (!courseEditing.value) {
      const res = await axios.post('/courses/api/create', courseFields.value)
      if (res.data.success) {
        notify({ type: 'success', message: 'Corso creato' })
        courseModal.value = false
        courseDt.value.reload()
      }
      return
    }

    const res = await axios.post(`/courses/api/${courseEditing.value.id}/update`, courseFields.value)
    if (res.data.success) {
      notify({ type: 'success', message: 'Corso aggiornato' })
      courseModal.value = false
      courseDt.value.reload()
    }
  } catch (e) {
    notify({ type: 'error', message: e?.response?.data?.message ?? 'Errore' })
  }
}

const deleteCourse = async (row) => {
  if (!confirm(`Eliminare il corso ${row.title}?`)) return
  const res = await axios.post(`/courses/api/${row.id}/delete`)
  if (res.data.success) {
    notify({ type: 'success', message: 'Corso eliminato' })
    courseDt.value.reload()
  }
}

const toggleCourseActive = async (row) => {
  const res = await axios.post(`/courses/api/${row.id}/toggle-active`)
  if (res.data.success) {
    notify({ type: 'success', message: res.data.active ? 'Corso attivato' : 'Corso disattivato' })
    courseDt.value.reload()
  }
}
</script>

<template>
  <v-container fluid>
    <v-row>
      <v-col cols="12" md="5">
        <div class="d-flex align-center mb-3">
          <h3 class="mr-3">Categorie</h3>
          <v-spacer />
          <v-btn @click="openCreateCategory">
            <v-icon>mdi-plus</v-icon>
            Nuova categoria
          </v-btn>
        </div>

        <DataTable ref="catDt" :computed="[{key: 'actions', title: 'Azioni'}]" url="/courses/api/categories/list">
          <template #item.active="{ item }">
            <div class="d-flex align-center justify-center">
              <v-chip :color="item.active ? 'green' : 'red'" size="small">
                {{ item.active ? 'Attiva' : 'Off' }}
              </v-chip>
            </div>
          </template>
          <template #item.icon="{ value }">
            <div class="d-flex align-center justify-center">
              <v-icon v-if="value">{{ value }}</v-icon>
              <span v-else>-</span>
            </div>
          </template>
          <template #item.actions="{ item }">
            <div class="d-flex align-center justify-center">
              <v-btn v-tooltip="'Modifica'" size="small" class="mr-1" variant="text" icon @click="openEditCategory(item)">
                <v-icon color="warning">mdi-pencil</v-icon>
              </v-btn>
              <v-btn v-tooltip="item.active ? 'Disattiva' : 'Attiva'" size="small" class="mr-1" variant="text" icon @click="toggleCategoryActive(item)">
                <v-icon :color="item.active ? 'error' : 'green'">mdi-power</v-icon>
              </v-btn>
              <v-btn v-tooltip="'Elimina'" size="small" class="mr-1" variant="text" icon @click="deleteCategory(item)">
                <v-icon color="error">mdi-delete</v-icon>
              </v-btn>
            </div>
          </template>
        </DataTable>
      </v-col>

      <v-col cols="12" md="7">
        <div class="d-flex align-center mb-3">
          <h3 class="mr-3">Corsi</h3>
          <v-spacer />
          <v-btn @click="openCreateCourse">
            <v-icon>mdi-plus</v-icon>
            Nuovo corso
          </v-btn>
        </div>

        <DataTable ref="courseDt" :computed="[{key: 'actions', title: 'Azioni'}]" url="/courses/api/list">
          <template #item.active="{ item }">
            <div class="d-flex align-center justify-center">
              <v-chip :color="item.active ? 'green' : 'red'" size="small">
                {{ item.active ? 'Attivo' : 'Off' }}
              </v-chip>
            </div>
          </template>
          <template #item.price="{ value }">
            <div class="d-flex align-center justify-center">
              <span>€ {{ value }}</span>
            </div>
          </template>
          <template #item.teacher_lastname="{ item }">
            <div class="d-flex align-center justify-center">
              <span>{{ item.teacher_firstname }} {{ item.teacher_lastname }}</span>
            </div>
          </template>
          <template #item.actions="{ item }">
            <div class="d-flex align-center justify-center">
              <v-btn v-tooltip="'Modifica'" size="small" class="mr-1" variant="text" icon @click="openEditCourse(item)">
                <v-icon color="warning">mdi-pencil</v-icon>
              </v-btn>
              <v-btn v-tooltip="item.active ? 'Disattiva' : 'Attiva'" size="small" class="mr-1" variant="text" icon @click="toggleCourseActive(item)">
                <v-icon :color="item.active ? 'error' : 'green'">mdi-power</v-icon>
              </v-btn>
              <v-btn v-tooltip="'Elimina'" size="small" class="mr-1" variant="text" icon @click="deleteCourse(item)">
                <v-icon color="error">mdi-delete</v-icon>
              </v-btn>
            </div>
          </template>
        </DataTable>
      </v-col>
    </v-row>

    <!-- Category modal -->
    <v-dialog v-model="catModal" max-width="600">
      <v-card>
        <v-card-actions>
          <v-card-title>{{ catEditing ? 'Modifica categoria' : 'Crea categoria' }}</v-card-title>
          <v-spacer />
          <v-btn @click="catModal = false" icon><v-icon>mdi-close</v-icon></v-btn>
        </v-card-actions>
        <v-container>
          <v-text-field v-model="catFields.name" label="Nome" />
          <v-text-field v-model="catFields.slug" label="Slug" hint="Se vuoto viene generato automaticamente" persistent-hint />
          <v-text-field v-model="catFields.icon" label="Icona (mdi-...)" />
          <v-switch v-model="catFields.active" label="Attiva" color="green" />
        </v-container>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="tonal" color="green" @click="saveCategory">Conferma</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Course modal -->
    <v-dialog v-model="courseModal" max-width="800">
      <v-card>
        <v-card-actions>
          <v-card-title>{{ courseEditing ? 'Modifica corso' : 'Crea corso' }}</v-card-title>
          <v-spacer />
          <v-btn @click="courseModal = false" icon><v-icon>mdi-close</v-icon></v-btn>
        </v-card-actions>
        <v-container>
          <v-row>
            <v-col cols="12" md="6">
              <v-select
                :items="meta.categories"
                item-title="name"
                item-value="id"
                v-model="courseFields.course_category_id"
                label="Categoria"
              />
            </v-col>
            <v-col cols="12" md="6">
              <v-select
                :items="meta.teachers"
                :item-title="(t) => `${t.firstname} ${t.lastname}`"
                item-value="id"
                v-model="courseFields.teacher_user_id"
                label="Docente"
              />
            </v-col>
          </v-row>

          <v-text-field v-model="courseFields.title" label="Titolo" />
          <v-text-field v-model="courseFields.code" label="Codice" disabled hint="Generato automaticamente dalla categoria" persistent-hint />
          <v-textarea v-model="courseFields.description" label="Descrizione" />

          <v-row>
            <v-col cols="12" md="4">
              <v-text-field v-model.number="courseFields.price" label="Prezzo" type="number" prefix="€" />
            </v-col>
            <v-col cols="12" md="4">
              <v-switch v-model="courseFields.active" label="Attivo" color="green" />
            </v-col>
          </v-row>
        </v-container>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="tonal" color="green" @click="saveCourse">Conferma</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

  </v-container>
</template>
