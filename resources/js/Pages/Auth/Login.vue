<script setup>
import Default from "../Layouts/Default.vue";
import logoImg from '@image/logo.png';
import {useForm} from '@inertiajs/vue3'

const form = useForm({
    email: '',
    password: ''
});

const submit = () => {
    form.post("/auth/login")
}
</script>

<template>
    <Default>
        <v-container class="fill-height d-flex align-center justify-center">
            <v-row align="center" justify="center">
                <v-col sm="12" lg="6">
                    <p class="text-center">
                        <img alt="logo" :src="logoImg" style="height: 150px"/>
                    </p>
                    <form @submit.prevent="submit" method="post">
                        <v-text-field v-model="form.email" label="Email"/>
                        <v-text-field v-model="form.password" type="password" label="Password"/>
                        <v-btn type="submit" color="primary" block>Accedi</v-btn>
                    </form>
                </v-col>
            </v-row>
        </v-container>
    </Default>
</template>

<style scoped>

</style>
