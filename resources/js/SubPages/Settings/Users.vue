<script setup>

import DataTable from "../../Components/DataTable.vue";
import {inject, useTemplateRef} from "vue";

const notify = inject('notify');
const table = useTemplateRef('dt');
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
    <DataTable ref="dt" :computed="[{key: 'actions', title: 'Azioni'}]" url="/settings/api/users/list">
        <template v-slot:item.phone_number="item">
            <v-chip v-if="!item.phone_number" color="warning">Telefono non definito</v-chip>
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
                <v-btn v-tooltip="'Modifica'" size="small" class="mr-1" variant="text" icon>
                    <v-icon color="warning">mdi-pencil</v-icon>
                </v-btn>
                <v-btn @click="toggleUserActivation(item)" size="small" variant="text"
                       v-tooltip="item.active ? 'Utente attivo' : 'Utente disattivato'" icon class="mr-1">
                    <v-icon :color="item.active ? 'green' : 'red'">mdi-power</v-icon>
                </v-btn>
                <v-btn v-tooltip="'Elimina'" size="small" class="mr-1" variant="text" icon>
                    <v-icon color="error">mdi-delete</v-icon>
                </v-btn>
            </div>
        </template>
    </DataTable>

</template>

<style scoped>

</style>
