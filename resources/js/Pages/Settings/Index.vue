<script setup>
import Logged from "../Layouts/Logged.vue";
import {ref} from "vue";
import Dictionary from "../../SubPages/Settings/Dictionary.vue";
import Users from "../../SubPages/Settings/Users.vue";

const tab = ref("");
</script>

<template>
    <Logged ref="layout">
        <v-tabs v-model="tab" align-tabs="center">
            <v-tab prepend-icon="mdi-book-alphabet" value="dictionary">Dizionario</v-tab>
            <v-tab prepend-icon="mdi-account-group" value="users">Utenti</v-tab>
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
        </v-tabs-window>
    </Logged>
</template>

<style scoped>

</style>
