import{r as v,_ as p,o as i,c,b as e,n as m,a as l,t as d,p as u,l as h,e as f,f as y,h as o,k as r,i as g}from"./index.js";const x={props:{type:{type:String,default:"text"},id:String,value:String,icon:String,title:String,text:String},setup(t){const n=v(null);return{props:t,errorMessage:n}}};const b=t=>(u("data-v-69d17cdc"),t=t(),h(),t),S={class:"item-wrapper"},k=["id","value"],w=["for"],I={class:"text-wrapper"},R={key:0},C={key:1},N=b(()=>e("i",{class:"club-chevron-right"},null,-1));function V(t,n,s,_,q,A){return i(),c("div",S,[e("input",{type:"radio",name:"type",id:s.id,value:s.value},null,8,k),e("label",{for:s.id},[s.icon?(i(),c("i",{key:0,class:m(s.icon)},null,2)):l("",!0),e("div",I,[s.title?(i(),c("h3",R,d(s.title),1)):l("",!0),s.text?(i(),c("p",C,d(s.text),1)):l("",!0)]),N],8,w)])}const a=p(x,[["render",V],["__scopeId","data-v-69d17cdc"]]),$=t=>(u("data-v-a0ae6721"),t=t(),h(),t),B={class:"signin-container purple"},P={class:"container"},T={class:"row"},D=$(()=>e("div",{class:"col-12"},[e("h1",{class:"mb-1"}," Аз съм... "),e("span",{class:"sub-title mb-3"}," Изберете една от опциите, за да настроим Вашия профил ")],-1)),z={class:"col-12"},E={class:"selection-list"},J={class:"bottom-wrapper"},M={class:"have-register mb-5"},j=f({__name:"Register",setup(t){return(n,s)=>{const _=y("router-link");return i(),c("div",B,[e("div",P,[e("div",T,[D,e("div",z,[e("div",E,[o(a,{id:"profile-type-1",value:"1",icon:"club-club",title:"Заведение",text:"Собственик, управител, PR или част от персонала"}),o(a,{id:"profile-type-2",value:"2",icon:"club-microphone",title:"Музикант/DJ",text:"Самостоятелен зпълнител без мениджър или компания"}),o(a,{id:"profile-type-3",value:"3",icon:"club-microphone",title:"Муз.компания",text:"Мениджър или компания, които отговарят за участията на изпълнителите"}),o(a,{id:"profile-type-4",value:"4",icon:"club-home",title:"Кметство/Община",text:"Кмет или служители, които отговарят за провеждане на културните мероприятия"})]),e("div",J,[e("span",M,[r(" Имаш регистрация? "),o(_,{to:{name:"login"}},{default:g(()=>[r(" Вход ")]),_:1})])])])])])])}}});const G=p(j,[["__scopeId","data-v-a0ae6721"]]);export{G as default};