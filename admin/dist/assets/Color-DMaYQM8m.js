import{_ as s,c as r,a as l,b as n,t as c,m as d,V as i,o as p}from"../index.js";const m={props:["data","config"],emits:["update:data"]},u={class:"color-picker"};function f(o,e,a,_,V,g){return p(),r("div",u,[l("label",null,c(a.config.label||"Color"),1),n(i,d(a.data,{"onUpdate:modelValue":e[0]||(e[0]=t=>o.$emit("update:data",t))}),null,16)])}const b=s(m,[["render",f]]);export{b as default};
