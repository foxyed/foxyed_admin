<script setup>
import Logged from "../Layouts/Logged.vue";
import {ref, useTemplateRef} from "vue";
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
            <v-tab prepend-icon="mdi-book-open-variant" value="courses">Corsi</v-tab>
        </v-tabs>
        <v-tabs-window v-model="tab">
            <v-tabs-window-item
                value="dictionary"
            >
                <Dictionary/>
            </v-tabs-window-item>
            <v-tabs-window-item
                value="users">
                    <Users/>
            </v-tabs-window-item>
            <v-tabs-window-item
                value="users_mgmt">
                <Users/>
            </v-tabs-window-item>
            <v-tabs-window-item
                value="courses">
                <Courses/>
            </v-tabs-window-item>
        </v-tabs-window>
    </Logged>
</template>

<style scoped>

</style>
