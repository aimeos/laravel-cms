<script>
  import { ClassicEditor, Markdown, Essentials, PasteFromOffice, Fullscreen, Clipboard, FindAndReplace, RemoveFormat, Paragraph, Bold, Italic, Strikethrough, Code, AutoLink, Link } from 'ckeditor5';
  import { Ckeditor } from '@ckeditor/ckeditor5-vue';
  import 'ckeditor5/ckeditor5.css';

  export default {
    components: {
      Ckeditor
    },

    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
      'readonly': {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'error'],

    data() {
      return {
        editor: ClassicEditor,
      }
    },

    computed: {
      ckconfig() {
        return {
          licenseKey: 'GPL',
          plugins: [ Markdown, Essentials, PasteFromOffice, Fullscreen, Clipboard, FindAndReplace, RemoveFormat, Paragraph, Bold, Italic, Strikethrough, Code, AutoLink, Link ],
          toolbar: [ 'undo', 'redo', 'removeFormat', '|', 'bold', 'italic', 'strikethrough', 'code', 'link', '|', 'fullscreen' ]
        }
      }
    },

    methods: {
      update(value) {
        if(this.modelValue != value) {
          this.$emit('update:modelValue', value);
        }
      },


      async validate() {
        return await true
      }
    }
  }
</script>

<template>
  <ckeditor
    :config="ckconfig"
    :editor="editor"
    :disabled="readonly"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
  ></ckeditor>
</template>

<style>
</style>