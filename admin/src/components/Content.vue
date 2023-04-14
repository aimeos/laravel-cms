<script>
  import Element from '../components/Element.vue'
  import History from '../components/History.vue'

  export default {
    components: {
      Element,
      History
    },
    props: ['clip', 'checked', 'content', 'type'],
    emits: ['copy', 'cut', 'insert', 'paste', 'remove', 'status', 'update:checked', 'update:data', 'update:draft', 'update:type'],
    data: () => ({
      data: {},
      history: false
    }),
    computed: {
      isDraft() {
        return !this.content.id || this.content.versions && (
            this.content.versions[0]?.data !== this.content.data
            || this.content.versions[0]?.data !== JSON.stringify(this.data)
          )
      }
    },
    methods: {
      use(data) {
        this.data = data
        this.history = false
        this.$emit('update:data', JSON.stringify(this.data))
      },
    },
    watch: {
      data: {
        deep: true,
        handler(data, old) {
/*          if(Object.keys(old) !== [] && data !== old) {
console.log('data', old, data)
            this.$emit('update:data', JSON.stringify(data))
          }
*/        }
      },

      content: {
        immediate: true,
        handler(content) {
          this.data = JSON.parse(content.versions ? content.versions[0]?.data : (content.data || '{}'))
          this.$emit('update:type', this.data.type)
          this.$emit('update:draft', this.isDraft)
        }
      }
    }
  }
</script>

<template>
  <v-expansion-panel :class="{'status-enabled': content.ref.status, 'status-disabled': !content.ref.status}" elevation="1">
    <v-expansion-panel-title collapse-icon="mdi-pencil">
      <v-checkbox-btn :class="{draft: isDraft}"
        :model-value="checked" @click.stop="$emit('update:checked', !checked)">
      </v-checkbox-btn>
      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
        </template>
        <v-list>
          <v-list-item v-if="content.ref.status">
            <v-btn prepend-icon="mdi-eye-off" variant="text" @click="$emit('status', 0)">Disable</v-btn>
          </v-list-item>
          <v-list-item v-if="!content.ref.status">
            <v-btn prepend-icon="mdi-eye" variant="text" @click="$emit('status', 1)">Enable</v-btn>
          </v-list-item>
          <v-list-item>
            <v-btn prepend-icon="mdi-content-copy" variant="text" @click="$emit('copy')">Copy</v-btn>
          </v-list-item>
          <v-list-item>
            <v-btn prepend-icon="mdi-content-cut" variant="text" @click="$emit('cut')">Cut</v-btn>
          </v-list-item>
          <v-list-item>
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click="$emit('insert', 0)">Insert before</v-btn>
          </v-list-item>
          <v-list-item>
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click="$emit('insert', 1)">Insert after</v-btn>
          </v-list-item>
          <v-list-item v-if="clip">
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click="$emit('paste', 0)">Paste before</v-btn>
          </v-list-item>
          <v-list-item v-if="clip">
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click="$emit('paste', 1)">Paste after</v-btn>
          </v-list-item>
          <v-list-item>
            <v-btn prepend-icon="mdi-delete" variant="text" @click="$emit('remove')">Delete</v-btn>
          </v-list-item>
          <v-list-item v-if="content.versions">
            <v-btn prepend-icon="mdi-history" variant="text" @click="history = true">
              History
            </v-btn>
          </v-list-item>
        </v-list>
      </v-menu>


      <div class="panel-heading">
        {{ data.type }}
        <span class="subtext">{{ data.title || data.text || '' }}</span>
      </div>
    </v-expansion-panel-title>
    <v-expansion-panel-text>

      <Element v-model:data="data" />

      <v-container>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field type="datetime-local" v-model="content.ref.start" label="Start date"
              variant="underlined"></v-text-field>
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field type="datetime-local" v-model="content.ref.end" label="End date"
              variant="underlined"></v-text-field>
          </v-col>
        </v-row>
      </v-container>

      <Teleport to="body">
        <v-dialog v-model="history" scrollable width="auto">
          <History type="content" :data="data" :versions="content.versions"
            @use="use($event)" @hide="history = false" />
        </v-dialog>
      </Teleport>

    </v-expansion-panel-text>
  </v-expansion-panel>
</template>

<style scoped>
  .status-disabled .panel-heading {
    text-decoration: line-through;
  }

  .draft {
    background-color: #ffe0c0;
    border-radius: 50%;
  }
</style>
