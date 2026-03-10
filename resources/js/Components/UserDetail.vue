<script setup>
import {computed, inject, onMounted, ref} from "vue";

const currentTab = ref("details");

const props = defineProps(['user']);

const groups = computed(() => {
    return [
        {
            title: "Amministratore",
            value: 'admin'
        },
        {
            title: "Studente",
            value: 'student'
        },
        {
            title: "Docente",
            value: 'teacher'
        },
        {
            title: "Amministrazione",
            value: 'administrative'
        }
    ]
});

const data = ref({});

const notify = inject('notify');

const getUserInfo = async () => {
    const res = await axios.get(`/users/api/${props.user}`);
    data.value = res.data.user;
}

const onDeleteAddress = async (address) => {
    const res = await axios.post(`/users/api/${props.user}/address/${address}/delete`);
    if (res.data.success) {
        notify({
            type: 'success',
            message: 'Indirizzo eliminato'
        });
    } else {
        notify({
            type: 'error',
            message: res.data.message
        });
    }
    await getUserInfo();

}


onMounted(async () => {
    await getUserInfo();
});

</script>

<template>
    <v-container>
        <v-tabs v-model="currentTab">
            <v-tab value="details">Informazioni</v-tab>
            <v-tab value="address">Indirizzi</v-tab>
            <v-tab value="payments">Pagamenti</v-tab>
            <v-tab value="orders">Ordini</v-tab>
            <v-tab value="subscriptions">Abbonamenti</v-tab>
        </v-tabs>
        <v-divider/>
        <v-tabs-window class="mt-6" v-model="currentTab">
            <v-tabs-window-item value="details">
                <v-row>
                    <v-col cols="6">
                        <v-text-field v-model="data.firstname" label="Nome"/>
                    </v-col>
                    <v-col cols="6">
                        <v-text-field v-model="data.lastname" label="Cognome"/>
                    </v-col>
                    <v-col cols="12">
                        <v-text-field v-model="data.email" label="Email"/>
                    </v-col>
                    <v-col cols="12">
                        <v-text-field v-model="data.phone_number" label="Telefono"/>
                    </v-col>
                    <v-col cols="12">
                        <v-select v-model="data.groups" multiple :items="groups"></v-select>
                    </v-col>
                </v-row>
            </v-tabs-window-item>
            <v-tabs-window-item value="address">
                <v-expansion-panels v-if="data.addresses.length > 0">
                    <v-expansion-panel elevation="0" v-for="address in data.addresses" :key="address.id">
                        <v-expansion-panel-title>
                            {{ address.label }}
                            <v-spacer/>
                            <v-chip v-if="address.type === 'billing'">Fatturazione</v-chip>
                            <v-chip v-else>Spedizione</v-chip>
                        </v-expansion-panel-title>
                        <v-expansion-panel-text>
                            <v-text-field disabled label="Indirizzo" v-model="address.line1"/>
                            <v-text-field disabled label="Indirizzo" v-model="address.line2"/>
                            <v-row>
                                <v-col cols="4">
                                    <v-text-field disabled label="Città" v-model="address.city"/>
                                </v-col>
                                <v-col cols="4">
                                    <v-text-field disabled label="CAP" v-model="address.zip"/>
                                </v-col>
                                <v-col cols="4">
                                    <v-text-field disabled label="Provincia" v-model="address.state"/>
                                </v-col>
                            </v-row>
                            <v-toolbar color="transparent" elevation="0">
                                <v-spacer/>
                                <v-btn @click="onDeleteAddress(address.id)" variant="flat" color="red">Elimina</v-btn>
                            </v-toolbar>
                        </v-expansion-panel-text>
                    </v-expansion-panel>
                </v-expansion-panels>
                <v-empty-state v-else icon="mdi-map-marker" title="Nessun indirizzo trovato"/>
            </v-tabs-window-item>
            <v-tabs-window-item value="payments"></v-tabs-window-item>
            <v-tabs-window-item value="orders"></v-tabs-window-item>
            <v-tabs-window-item value="subscriptions"></v-tabs-window-item>
        </v-tabs-window>
    </v-container>

</template>

<style scoped>

</style>
