<script setup>
import Logged from "../Layouts/Logged.vue";
import {ref, useTemplateRef} from "vue";
import DataTable from "../../Components/DataTable.vue";
import Dictionary from "../../SubPages/Settings/Dictionary.vue";
import Users from "../../SubPages/Settings/Users.vue";

const tab = ref("");
const layout = useTemplateRef('layout');
</script>

<template>
    <Logged ref="layout">
        <v-tabs v-model="tab" align-tabs="center">
            <v-tab prepend-icon="mdi-book-alphabet" value="dictionary">Dizionario</v-tab>
            <v-tab prepend-icon="mdi-account-group" value="users">Utenti</v-tab>
            <v-tab prepend-icon="mdi-account-cog" value="users_mgmt">Gestione utenti</v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab">
            <v-tabs-window-item
                value="dictionary"
            >
                <Dictionary/>
            </v-tabs-window-item>
            <v-tabs-window-item
                value="users">
                <DataTable :computed="[{key: 'actions', title: 'Azioni'}]" url="/settings/api/users/list">
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
                    <template #item.actions="item">
                        <div class="d-flex align-center justify-center">
                            <v-btn v-tooltip="'Modifica'" size="small" class="mr-1" variant="text" icon>
                                <v-icon color="warning">mdi-pencil</v-icon>
                            </v-btn>
                            <v-btn v-tooltip="'Elimina'" size="small" class="mr-1" variant="text" icon>
                                <v-icon color="error">mdi-delete</v-icon>
                            </v-btn>
                        </div>
                    </template>
                </DataTable>
            </v-tabs-window-item>
            <v-tabs-window-item
                value="users_mgmt">
                <Users/>
            </v-tabs-window-item>
        </v-tabs-window>
    </Logged>
</template>

<style scoped>

</style>
