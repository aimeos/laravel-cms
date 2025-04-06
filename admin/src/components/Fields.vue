<script>
  export default {
    props: ['data', 'fields'],
    emits: ['update:data'],
    data: () => ({
      img: {}
    }),
    methods: {
      image(code, file) {
        if(this.img[code]) {
          URL.revokeObjectURL(this.img[code])
          this.img[code] = null
        }
        if(file) {
          this.img[code] = URL.createObjectURL(file)
        }
        return this.img[code]
      },


      images(code, files) {
        if(this.img[code] && this.img[code]?.length) {
          this.img[code].map(file => URL.revokeObjectURL(file))
          this.img[code] = []
        }
        if(files && files.length) {
          this.img[code] = files.map(file => URL.createObjectURL(file))
        }
        return this.img[code]
      },
    },
  }
</script>

<template>
  <v-container>
    <v-form @submit.prevent>

      <v-row v-for="(field, code) in fields" :key="code">

        <v-col cols="12" sm="3" class="form-label">
          <v-label>{{ field.label || code }}</v-label>
        </v-col>

        <v-col cols="12" sm="9">

          <v-autocomplete v-if="field.type === 'autocomplete'"
            :items="field.options || []"
            :label="field.label || ''"
            v-model="data[code]"
            variant="underlined"
          ></v-autocomplete>

          <v-checkbox v-if="field.type === 'checkbox'"
            :label="field.label || ''"
            v-model="data[code]"
          ></v-checkbox>

          <v-combobox v-if="field.type === 'combobox'"
            :items="field.options || []"
            :label="field.label || ''"
            :multiple="field.multiple"
            :chips="field.multiple"
            v-model="data[code]"
            variant="underlined"
            clearable
          ></v-combobox>

          <v-file-input v-if="field.type === 'file'"
            :label="field.label || ''"
            v-model="data[code]"
            variant="underlined"
            clearable
          ></v-file-input>

          <div v-if="field.type === 'image'">
            <v-file-input
              :label="field.label || ''"
              @update:model-value="data[code] = image(code, $event)"
              variant="underlined"
              show-size="1024"
              clearable
            ></v-file-input>

            <v-progress-linear
              color="primary"
              height="6"
              indeterminate
              rounded
            ></v-progress-linear>

            <v-img v-if="data[code]"
              :src="data[code]"
              max-width="50vw"
              max-height="50vh"
            ></v-img>
          </div>

          <div v-if="field.type === 'images'">
            <v-file-input
              :label="field.label || ''"
              @update:model-value="data[code] = images(code, $event)"
              variant="underlined"
              show-size="1024"
              clearable
              multiple
              counter
            ></v-file-input>

            <v-progress-linear
              color="primary"
              height="6"
              indeterminate
              rounded
            ></v-progress-linear>

            <v-carousel v-if="data[code]?.length">
              <v-carousel-item v-for="image in data[code]"
                :src="image"
              ></v-carousel-item>
            </v-carousel>
          </div>

          <v-number-input v-if="field.type === 'number'"
            :label="field.label || ''"
            v-model="data[code]"
            variant="outlined"
          ></v-number-input>

          <v-radio-group v-if="field.type === 'radio'"
            v-model="data[code]">
            <v-radio v-for="(label, value) in (field.options || [])"
              :label="label"
              :value="value">
            </v-radio>
          </v-radio-group>

          <v-range-slider v-if="field.type === 'range'"
            v-model="data[code]"
          ></v-range-slider>

          <v-select v-if="field.type === 'select'"
            :items="field.options || []"
            :label="field.label || ''"
            :multiple="field.multiple"
            :chips="field.multiple"
            v-model="data[code]"
            variant="underlined"
            item-title="label"
          ></v-select>

          <v-slider v-if="field.type === 'slider'"
            v-model="data[code]"
          ></v-slider>

          <v-switch v-if="field.type === 'switch'"
            :label="field.label || ''"
            v-model="data[code]"
            inset
          ></v-switch>

          <v-text-field v-if="field.type === 'string'"
            :label="field.label || ''"
            v-model="data[code]"
            variant="underlined"
            clearable
          ></v-text-field>

          <v-textarea v-if="field.type === 'text'"
            :label="field.label || ''"
            v-model="data[code]"
            variant="underlined"
            clearable
          ></v-textarea>

          <v-date-picker v-if="field.type === 'date'"
            v-model="data[code]"
            show-adjacent-months
          ></v-date-picker>

          <v-color-picker v-if="field.type === 'color'"
            v-model="data[code]"
          ></v-color-picker>

        </v-col>

      </v-row>

    </v-form>
  </v-container>
</template>

<style scoped>
  .v-form label {
    font-weight: bold;
    text-transform: capitalize;
  }
</style>
