!function(t){var o={};function r(n){if(o[n])return o[n].exports;var e=o[n]={i:n,l:!1,exports:{}};return t[n].call(e.exports,e,e.exports,r),e.l=!0,e.exports}r.m=t,r.c=o,r.d=function(n,e,t){r.o(n,e)||Object.defineProperty(n,e,{enumerable:!0,get:t})},r.r=function(n){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(n,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(n,"__esModule",{value:!0})},r.t=function(e,n){if(1&n&&(e=r(e)),8&n)return e;if(4&n&&"object"==typeof e&&e&&e.__esModule)return e;var t=Object.create(null);if(r.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:e}),2&n&&"string"!=typeof e)for(var o in e)r.d(t,o,function(n){return e[n]}.bind(null,o));return t},r.n=function(n){var e=n&&n.__esModule?function(){return n.default}:function(){return n};return r.d(e,"a",e),e},r.o=function(n,e){return Object.prototype.hasOwnProperty.call(n,e)},r.p="/static-dist/",r(r.s=487)}({487:function(n,e){$("html").on("shown.bs.modal","#functionModal",function(n){$("html").css("overflow","hidden")}).on("hidden.bs.modal","#functionModal",function(n){$("html").css("overflow","scroll")}),$(".js-quick-entrance").on("click",".js-function-choose",function(n){var e=$(n.currentTarget);!e.hasClass("active")&&7<=$(".js-function-choose.active").length?cd.message({type:"warning",message:Translator.trans("admin_v2.homepage.quick_entrance.hint")}):e.toggleClass("active")}),$(".js-quick-entrance").on("click",".js-save-btn",function(n){var e,t;7<$(".js-function-choose.active").length?cd.message({type:"warning",message:Translator.trans("admin_v2.homepage.quick_entrance.hint")}):((e=$(n.target)).button("loading"),t=[],$(".js-function-choose.active").each(function(n,e){t.push($(e).data("code"))}),$.post($("#quick-entrance-form").attr("action"),{_csrf_token:$("meta[name=csrf-token]").attr("content"),data:t},function(n){e.button("reset"),$("#functionModal").modal("hide"),$(".js-quick-entrance").html(n)}))})}});