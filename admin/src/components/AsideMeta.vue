<script>
  export default {
    props: {
      'item': {type: Object, required: true},
      'state': {type: [Boolean, null], required: true}
    },

    emits: ['update:state'],

    data: () => ({
      width: window.innerWidth
    }),

    computed: {
      values() {
        const map = {
          'id': this.item.id,
          'editor': this.item.editor
        }

        if(this.item.created_at) {
          map['created'] = (new Date(this.item.created_at)).toLocaleString()
        }

        if(this.item.updated_at) {
          map['updated'] = (new Date(this.item.updated_at)).toLocaleString()
        }

        if(this.item.deleted_at) {
          map['deleted'] = (new Date(this.item.deleted_at)).toLocaleString()
        }

        return map
      }
    },
  }
</script>

<template>
  <v-navigation-drawer location="end" rail-width="220" :modelValue="state" @update:modelValue="$emit('update:state', $event)" :rail="width > 1200 ? false : true" expand-on-hover>

    <div class="title">Meta data</div>

    <v-list>
      <v-list-item v-for="(value, key) in values" :key="key">
        <v-list-item-title class="name">{{ key }}</v-list-item-title>
        <div>{{ value }}</div>
      </v-list-item>
    </v-list>

  </v-navigation-drawer>
</template>

<style scoped>
  .title {
    padding: 10px 16px;
    font-size: 1.25rem;
    background-color: rgb(var(--v-theme-surface-light));
  }

  .v-list-item {
    margin-bottom: 0.25rem;
  }

  .v-list-item .name {
    text-transform: capitalize;
  }

  .v-list-item .name::after {
    content: ':';
  }
</style>
