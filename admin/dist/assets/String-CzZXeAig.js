import{_ as o,d,m as l,e as r,o as s}from"../index.js";const i={props:["data","config"],emits:["update:data"]};function p(t,e,a,u,c,f){return s(),d(r,l({label:a.config.label||"Value",variant:"underlined"},a.data,{"onUpdate:modelValue":e[0]||(e[0]=n=>t.$emit("update:data",n))}),null,16,["label"])}const _=o(i,[["render",p]]);export{_ as default};
