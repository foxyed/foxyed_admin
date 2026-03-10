<script setup>

import Logged from "./Logged.vue";
</script>

<template>
    <Logged>
        <template #sidebar>
            <v-list-item elevation="0">
               <v-menu>
                   <template #activator="{props}">
                       <v-btn v-bind="props" block prepend-icon="mdi-plus">Nuovo</v-btn>
                   </template>
                   <v-list density="compact">
                       <v-list-item
                           density="compact"
                           title="Nuova cartella"
                           prepend-icon="mdi-folder-plus"
                           />
                       <v-list-item
                           density="compact"
                           title="Carica file"
                           prepend-icon="mdi-upload"
                       />
                   </v-list>
               </v-menu>
            </v-list-item>
        </template>

        <template
            v-for="(_, slotName) in $slots"
            #[slotName]="slotProps"
        >
            <slot :name="slotName" v-bind="slotProps"/>
        </template>
    </Logged>
</template>

<style scoped>

</style>
