import{_ as a,c as n,V as d,o as m}from"../index.js";const u={props:["modelValue","config"],emits:["update:modelValue"]};function s(l,o,e,i,c,r){return m(),n(d,{items:e.config.options||[],label:e.config.label||"",modelValue:e.modelValue,"onUpdate:modelValue":o[0]||(o[0]=t=>l.$emit("update:modelValue",t)),density:"comfortable",variant:"underlined"},null,8,["items","label","modelValue"])}const p=a(u,[["render",s]]);export{p as default};
