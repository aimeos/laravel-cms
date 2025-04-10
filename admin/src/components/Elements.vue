<script>
  export default {
    props: ['ce', 'index'],
    emits: ['add'],
    data: () => ({
      tab: ['basic']
    }),
    computed: {
      groups() {
        const map = {}

        for(const code in this.ce) {
          const name = this.ce[code].group || 'uncategorized'
          map[name] = map[name] || []
          map[name].push(this.ce[code])
        }

        return map
      }
    }
  }
</script>

<template>
  <v-container class="rounded-lg">
    <v-tabs v-model="tab">
      <v-tab v-for="(group, name) in groups" :key="name" :value="name">{{ name }}</v-tab>
    </v-tabs>

    <v-tabs-window v-model="tab">
      <v-tabs-window-item v-for="name in Object.keys(groups)" :key="name" :value="name">

        <v-card flat>
          <v-btn v-for="item in groups[name]" :key="item.type" variant="text" stacked
            @click="$emit('add', {type: item.type, index: index})">
            <template v-slot:prepend>
                <span class="icon" v-html="item.icon"></span>
            </template>
            {{ item.type }}
          </v-btn>
        </v-card>

      </v-tabs-window-item>
    </v-tabs-window>
  </v-container>
</template>

<style scoped>
  .v-container {
    background-color: rgb(var(--v-theme-background));
    max-width: 100%;
    width: 50vw;
  }

  .icon {
    width: 2rem;
  }
</style>
