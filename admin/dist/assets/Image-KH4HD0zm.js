import{g as n}from"./index-CzuiuYVM.js";import{_ as f,f as s,c as h,g as u,h as m,i as p,j as g,k as c,u as V,o,l as _}from"../index.js";const v={props:["modelValue","config"],emits:["update:modelValue"],setup(){return{app:V()}},data(){return{index:Math.floor(Math.random()*1e5)}},methods:{add(t){const e=t.target.files||t.dataTransfer.files||[];e.length&&(this.$emit("update:modelValue",{path:URL.createObjectURL(e[0]),uploading:!0}),this.$apollo.mutate({mutation:n`mutation($file: Upload!) {
            addFile(file: $file) {
              id
              mime
              name
              path
              previews
            }
          }`,variables:{file:e[0]},context:{hasUpload:!0}}).then(a=>{var i;if(a.errors)throw a.errors;const r=((i=a.data)==null?void 0:i.addFile)||{};r.previews=JSON.parse(r.previews)||{},URL.revokeObjectURL(this.modelValue.path),this.$emit("update:modelValue",r)}).catch(a=>{console.error("addFile()",a)}))},remove(){var t;(t=this.modelValue)!=null&&t.id&&this.$apollo.mutate({mutation:n`mutation($id: ID!) {
            dropFile(id: $id) {
              id
            }
          }`,variables:{id:this.modelValue.id}}).then(e=>{if(e.errors)throw e.errors;this.$emit("update:modelValue",null)}).catch(e=>{console.error(`dropFile(${code})`,e)})},srcset(t){let e=[];for(const a in t)e.push(`${this.url(t[a])} ${a}w`);return e.join(", ")},url(t){return t.startsWith("http")||t.startsWith("blob:")?t:this.app.urlfile.replace(/\/+$/g,"")+"/"+t}}},k={key:0,class:"image"},b={key:1,class:"image file-input"},y=["id"],w=["for"];function x(t,e,a,r,i,l){return a.modelValue?(o(),s("div",k,[a.modelValue.uploading?(o(),h(_,{key:0,color:"primary",height:"5",indeterminate:"",rounded:""})):u("",!0),m(p,{draggable:!1,src:l.url(a.modelValue.path),srcset:l.srcset(a.modelValue.previews)},null,8,["src","srcset"]),a.modelValue.id?(o(),s("button",{key:1,onClick:e[0]||(e[0]=d=>l.remove()),title:"Remove image",type:"button"},[m(g,{icon:"mdi-trash-can",role:"img"})])):u("",!0)])):(o(),s("div",b,[c("input",{type:"file",onInput:e[1]||(e[1]=d=>l.add(d)),id:"image-"+i.index,value:null,accept:"image/*",hidden:""},null,40,y),c("label",{for:"image-"+i.index},"Add file",8,w)]))}const U=f(v,[["render",x],["__scopeId","data-v-f7a48c39"]]);export{U as default};
