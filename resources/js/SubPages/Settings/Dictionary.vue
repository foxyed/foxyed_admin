<script setup>
import DataTable from "../../Components/DataTable.vue";
import {inject, ref, useTemplateRef} from "vue";

const notify = inject('notify');
const showModal = ref(false);
const fields = ref({
    createDictionary: {
        label: "",
        name: "",
        value: "",
        is_live: ""
    }
});

const dt = useTemplateRef('dt');

const onCreateDictionaryItem = async () => {
    const res = await axios.post('/settings/api/dictionary/create', {
        ...fields.value.createDictionary
    });
    if (res.data.success) {
        notify({
            type: 'success',
            message: "Record creato correttamente"
        });
        dt.value.reload();
        fields.value.createDictionary = {
            label: '',
            name: '',
            value: '',
            is_live: ''
        };
        showModal.value = false;

        return;
    }
    notify({
        type: 'error',
        message: res.data.message
    })
}
</script>

<template>
    <v-btn @click="showModal = true" class="float-end mb-3">
        <v-icon>mdi-plus</v-icon>
        Nuovo record
    </v-btn>
    <DataTable ref="dt" :computed="[{key: 'actions', title: 'Azioni'}]"
               url="/settings/api/dictionary/list">
        <template v-slot:item.is_live="{ item }">
            <div class="d-flex align-center justify-center">
                <v-switch
                    disabled
                    :model-value="item.is_live"
                    color="green"
                    hide-details
                    density="compact"
                />
            </div>
        </template>
        <template v-slot:item.actions="item">
            <div class="d-flex align-center justify-center">
                <v-btn v-tooltip="'Modifica'" icon variant="text">
                    <v-icon color="warning">mdi-pencil</v-icon>
                </v-btn>
                <v-btn v-tooltip="'Elimina'" icon variant="text">
                    <v-icon color="error">mdi-delete</v-icon>
                </v-btn>
            </div>
        </template>
    </DataTable>

    <v-dialog v-model="showModal" max-width="500">
        <v-card>
            <v-card-actions>
                <v-card-title>Crea variabile</v-card-title>
                <v-spacer/>
                <v-btn @click="showModal = false" icon>
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-actions>
            <v-container>
                <v-text-field v-model="fields.createDictionary.label" label="Nome"/>
                <v-text-field v-model="fields.createDictionary.name" label="Codice parametro"/>
                <v-text-field v-model="fields.createDictionary.value" label="Valore"/>
                <v-switch value="on" v-model="fields.createDictionary.is_live" label="Attivo in produzione"/>
            </v-container>
            <v-card-actions>
                <v-spacer/>
                <v-btn @click="onCreateDictionaryItem" variant="tonal" color="green">Conferma</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>

</style>
