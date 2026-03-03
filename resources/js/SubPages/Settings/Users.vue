<script setup>
import {useTemplateRef, inject, ref} from "vue";
import DataTable from "../../Components/DataTable.vue";

const notify = inject('notify');
const dt = useTemplateRef('dt');

const showModal = ref(false);
const editing = ref(null);

const fields = ref({
  firstname: '',
  lastname: '',
  email: '',
  phone_number: '',
  password: '',
  active: true,
  groups: [],
});

const openCreate = () => {
  editing.value = null;
  fields.value = {
    firstname: '',
    lastname: '',
    email: '',
    phone_number: '',
    password: '',
    active: true,
    groups: [],
  };
  showModal.value = true;
}

const openEdit = (row) => {
  editing.value = row;
  fields.value = {
    firstname: row.firstname,
    lastname: row.lastname,
    email: row.email,
    phone_number: row.phone_number ?? '',
    password: '',
    active: !!row.active,
    groups: row.groups ?? [],
  };
  showModal.value = true;
}

const save = async () => {
  try {
    if (!editing.value) {
      const res = await axios.post('/users/api/create', fields.value);
      if (res.data.success) {
        notify({type: 'success', message: 'Utente creato correttamente'});
        showModal.value = false;
        dt.value.reload();
      }
      return;
    }

    const res = await axios.post(`/users/api/${editing.value.id}/update`, fields.value);
    if (res.data.success) {
      notify({type: 'success', message: 'Utente aggiornato correttamente'});
      showModal.value = false;
      dt.value.reload();
    }
  } catch (e) {
    notify({type: 'error', message: e?.response?.data?.message ?? 'Errore di validazione'});
  }
}

const removeUser = async (row) => {
  if (!confirm(`Eliminare l'utente ${row.firstname} ${row.lastname}?`)) return;
  const res = await axios.post(`/users/api/${row.id}/delete`);
  if (res.data.success) {
    notify({type: 'success', message: 'Utente eliminato'});
    dt.value.reload();
  }
}

const toggleActive = async (row) => {
  const res = await axios.post(`/users/api/${row.id}/toggle-active`);
  if (res.data.success) {
    notify({type: 'success', message: res.data.active ? 'Utente sbloccato' : 'Utente bloccato'});
    dt.value.reload();
  }
}
</script>

<template>
  <v-btn class="float-end mb-3" @click="openCreate">
    <v-icon>mdi-plus</v-icon>
    Nuovo utente
  </v-btn>

  <DataTable
    ref="dt"
    :computed="[{key: 'actions', title: 'Azioni'}]"
    url="/users/api/list"
  >
    <template #item.active="{ item }">
      <div class="d-flex align-center justify-center">
        <v-chip :color="item.active ? 'green' : 'red'" size="small">
          {{ item.active ? 'Attivo' : 'Bloccato' }}
        </v-chip>
      </div>
    </template>

    <template #item.groups="{ value }">
      <v-chip
        v-for="(group, i) in (value ?? [])"
        :key="i"
        class="ma-1"
        size="small"
      >
        {{ group }}
      </v-chip>
    </template>

    <template #item.actions="{ item }">
      <div class="d-flex align-center justify-center">
        <v-btn v-tooltip="'Modifica'" size="small" class="mr-1" variant="text" icon @click="openEdit(item)">
          <v-icon color="warning">mdi-pencil</v-icon>
        </v-btn>
        <v-btn v-tooltip="item.active ? 'Blocca' : 'Sblocca'" size="small" class="mr-1" variant="text" icon @click="toggleActive(item)">
          <v-icon :color="item.active ? 'error' : 'green'">mdi-account-lock</v-icon>
        </v-btn>
        <v-btn v-tooltip="'Elimina'" size="small" class="mr-1" variant="text" icon @click="removeUser(item)">
          <v-icon color="error">mdi-delete</v-icon>
        </v-btn>
      </div>
    </template>

  </DataTable>

  <v-dialog v-model="showModal" max-width="600">
    <v-card>
      <v-card-actions>
        <v-card-title>{{ editing ? 'Modifica utente' : 'Crea utente' }}</v-card-title>
        <v-spacer />
        <v-btn @click="showModal = false" icon>
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-card-actions>

      <v-container>
        <v-row>
          <v-col cols="6">
            <v-text-field v-model="fields.firstname" label="Nome" />
          </v-col>
          <v-col cols="6">
            <v-text-field v-model="fields.lastname" label="Cognome" />
          </v-col>
        </v-row>

        <v-text-field v-model="fields.email" label="Email" />
        <v-text-field v-model="fields.phone_number" label="Telefono" />

        <v-text-field
          v-model="fields.password"
          :label="editing ? 'Nuova password (opzionale)' : 'Password'"
          type="password"
          autocomplete="new-password"
        />

        <v-switch v-model="fields.active" label="Attivo" color="green" />
      </v-container>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="tonal" color="green" @click="save">Conferma</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
