<script setup>
import DataTable from "../../Components/DataTable.vue";
import {inject, ref, useTemplateRef} from "vue";

const notify = inject('notify');
const table = useTemplateRef('dt');
const showDialog = ref(false);
const fields = ref({
    id: -1,
    firstname: "",
    lastname: "",
    email: "",
    phone_number: ""
});

const editUser = async (user = null, action = 'show') => {
    if (action === 'show') {
        fields.value.id = user.id;
        fields.value.firstname = user.firstname;
        fields.value.lastname = user.lastname;
        fields.value.email = user.email;
        fields.value.phone_number = user.phone_number;
        showDialog.value = true;
        return;
    }

    const res = await axios.post(`/settings/api/users/${fields.value.id}/edit`, {
        ...fields.value
    });
    if (res.data.success) {
        notify({
            message: "Utente aggiornato correttamente",
            type: 'success'
        });
    } else {
        notify({
            message: res.data.message,
            type: 'error'
        });
    }
    table.value.reload();

}

const deleteUser = async (user) => {
    const ok = confirm("Eliminare l'utente?")

    if (ok) {
        const res = await axios.post(`/settings/api/users/${user.id}/delete`);
        if (res.data.success) {
            table.value.reload();
            notify({
                message: "Utente eliminato",
                type: 'success'
            });
            return;
        }
        notify({
            message: "Impossibile eliminare l'utente",
            type: 'error'
        })
    }
}

const toggleUserActivation = async (user) => {
    const res = await axios.post(`/settings/api/users/${user.id}/edit`, {
        active: !user.active
    });
    if (res.data.success) {
        notify({
            message: "Attivazione aggiornata correttamente",
            type: "success"
        });
    } else {
        notify({
            message: "Impossibile aggiornare lo stato",
            type: "error"
        });
    }
    table.value.reload();
}
</script>

<template>
    <v-btn variant="tonal" class="float-end mb-1">
        <v-icon class="mr-2">mdi-plus</v-icon>
        Nuovo utente
    </v-btn>
    <DataTable ref="dt" :computed="[{key: 'actions', title: 'Azioni'}]" url="/settings/api/users/list">
        <template v-slot:item.phone_number="{item}">
            <v-chip v-if="item.phone_number === null" color="warning">Telefono non definito</v-chip>
            <span v-else>{{ item.phone_number }}</span>
        </template>
        <template #item.groups="{ value }">
            <v-chip
                v-for="(group, i) in value"
                :key="i"
                class="ma-1"
                size="small"
            >
                {{ group }}
            </v-chip>
        </template>
        <template #item.actions="{item}">
            <div class="d-flex align-center justify-center">
                <v-btn @click="editUser(item)" v-tooltip="'Modifica'" size="small" class="mr-1" variant="text" icon>
                    <v-icon color="warning">mdi-pencil</v-icon>
                </v-btn>
                <v-btn @click="toggleUserActivation(item)" size="small" variant="text"
                       v-tooltip="item.active ? 'Utente attivo' : 'Utente disattivato'" icon class="mr-1">
                    <v-icon :color="item.active ? 'green' : 'red'">mdi-power</v-icon>
                </v-btn>
                <v-btn @click="deleteUser(item)" v-tooltip="'Elimina'" size="small" class="mr-1" variant="text" icon>
                    <v-icon color="error">mdi-delete</v-icon>
                </v-btn>
            </div>
        </template>
    </DataTable>
    <v-dialog :z-index="2400" max-width="500" v-model="showDialog">
        <v-card>
            <v-card-actions>
                <v-card-title>Modifica utente</v-card-title>
                <v-spacer/>
                <v-btn @click="showDialog = false" icon variant="text">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-actions>
            <v-container>
                <v-text-field v-model="fields.firstname" label="Nome"/>
                <v-text-field v-model="fields.lastname" label="Cognome"/>
                <v-text-field v-model="fields.email" label="Email"/>
                <v-text-field v-model="fields.phone_number" label="Telefono"/>
            </v-container>
            <v-card-actions>
                <v-spacer/>
                <v-btn @click="editUser(null,'update')" variant="text">Conferma</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>

</style>
