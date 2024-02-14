(()=>{"use strict";const e=window.React,t=function({paymentMethodConfigurationId:t,eventRegistration:n}){const{onCheckoutSuccess:o,onCheckoutValidation:r}=n,[i,a]=(0,e.useState)(!0),c=`payment-method-${t}`;let s=window.IframeCheckoutHandler(t);const l=(0,e.useCallback)((()=>{s.create(c),a(!1)}),[t,c]);return(0,e.useEffect)((()=>{document.getElementById(c)&&l();const e=o((()=>new Promise((e=>{s.submit(),setTimeout((function(){e(!1)}),3e4)})))),t=r((()=>{let e=new Promise((e=>{s.setValidationCallback((t=>{void 0!==t.success?e(t.success):(console.error("Validation was not successful"),e(!1))}))}));return s.validate(),e}));return()=>{e(),t()}}),[l,c,o,r]),(0,e.createElement)("div",null,i&&(0,e.createElement)("div",null,"Loading payment method..."),(0,e.createElement)("div",{id:c}))},n=function({paymentMethodConfigurationId:t,eventRegistration:n}){const{onCheckoutSuccess:o}=n;(0,e.useEffect)((()=>{const e=o((()=>new Promise(((e,n)=>{window.LightboxCheckoutHandler.startPayment(t,(function(t,o){!t||t.error?n(t):e(t)}))})).catch((e=>{console.error(e),alert("An error occurred during the initialization of the payment lightbox.")}))));return()=>{e()}}),[o,t])};function o({label:t,src:n,alt:o,width:r}){return(0,e.createElement)("div",null," ",t," ",(0,e.createElement)("img",{src:n,alt:o,width:r})," ")}const r=window.wc.wcBlocksRegistry;jQuery((function(i){const a=wp.apiFetch.nonceEndpoint;i.post(a,{action:"get_payment_methods"},(function(c){c.map((function(c){let s;s="iframe"===c.integration_mode?(0,e.createElement)(t,{paymentMethodConfigurationId:c.configuration_id}):(0,e.createElement)(n,{paymentMethodConfigurationId:c.configuration_id});let l=function(e){const t=e.match(/src="([^"]+)"/),n=e.match(/alt="([^"]*)"/),o=e.match(/width="([^"]+)"/);return{src:t?t[1]:"",alt:n?n[1]:"",width:o?o[1]:""}}(c.icon),u={name:c.name,label:(0,e.createElement)(o,{label:c.label,...l}),content:s,edit:(0,e.createElement)("div",null,c.description),ariaLabel:c.ariaLabel,supports:{features:c.supports},canMakePayment:async e=>{let t=(e=>{const t=JSON.stringify(e);let n=0;for(let e=0;e<t.length;e++)n=(n<<5)-n+t.charCodeAt(e),n|=0;return Math.abs(n).toString(16)})(e),n=c.name+"-"+c.configuration_id+"-"+t,o=(e=>{const t=sessionStorage.getItem(e);if(!t)return null;const n=JSON.parse(t);return(new Date).getTime()>n.expiry?(sessionStorage.removeItem(e),null):n.value})(n);return null!==o?o:await async function(){try{return new Promise(((e,t)=>{i.post(a,{action:"is_payment_method_available",payment_method:c.name,configuration_id:c.configuration_id},(function(t){((e,t,n)=>{const o={value:t,expiry:(new Date).getTime()+36e5};sessionStorage.setItem(e,JSON.stringify(o))})(n,t),e(t)})).fail((function(e){console.error("Error:",e),t(!1)}))}))}catch(e){return console.error("Error:",e),!1}}()}};(0,r.registerPaymentMethod)(u)}))})).fail((function(e){console.error("Error getting payment methods. ",e)}))}))})();