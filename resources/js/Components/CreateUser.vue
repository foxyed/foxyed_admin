<script setup>

import {computed, inject, ref} from "vue";
import axios from "axios";


const props = defineProps(['callback'])

const firstname = ref("");
const lastname = ref("");
const email = ref("");
const phone = ref("");
const groups = ref([]);
const select = computed(() => {
    return [
        {
            title: "Amministratore",
            value: "admin"
        },
        {
            title: "Studente",
            value: "student"
        },
        {
            title: "Amministrazione",
            value: "administrative"
        },
        {
            title: "Docente",
            value: "teacher"
        },
    ]
});
const notify = inject('notify');

const onUserCreate = async () => {
    const res = await axios.post('/users/api/create', {
        firstname: firstname.value,
        lastname: lastname.value,
        email: email.value,
        phone: phone.value,
        groups: groups.value
    })
    if (res.data.success) {
        notify({
            type: 'success',
            message: "Utente correttamente creato"
        });
        firstname.value = "";
        lastname.value = "";
        email.value = "";
        phone.value = "";
        groups.value = [];
        props.callback();
    } else {
        notify({
            type: 'error',
            message: res.data.message
        });
    }
}

</script>

<template>
    <v-container>
        <v-row>
            <v-col cols="6">
                <v-text-field v-model="firstname" label="Nome"/>
            </v-col>
            <v-col cols="6">
                <v-text-field v-model="lastname" label="Cognome"/>
            </v-col>
            <v-col cols="12">
                <v-text-field v-model="email" label="Email"/>
            </v-col>
            <v-col cols="12">
                <v-text-field v-model="phone" label="Telefono"/>
            </v-col>
            <v-col cols="12">
                <v-select multiple label="Gruppi" :items="select" v-model="groups"/>
            </v-col>
        </v-row>
    </v-container>
    <v-card-actions>
        <v-spacer/>
        <v-btn @click="onUserCreate" color="green">Salva</v-btn>
    </v-card-actions>
</template>

<style scoped>

</style>
