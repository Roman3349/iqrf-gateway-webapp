!function(n){function e(e){for(var r,i,s=e[0],a=e[1],c=e[2],f=0,p=[];f<s.length;f++)i=s[f],Object.prototype.hasOwnProperty.call(o,i)&&o[i]&&p.push(o[i][0]),o[i]=0;for(r in a)Object.prototype.hasOwnProperty.call(a,r)&&(n[r]=a[r]);for(l&&l(e);p.length;)p.shift()();return u.push.apply(u,c||[]),t()}function t(){for(var n,e=0;e<u.length;e++){for(var t=u[e],r=!0,s=1;s<t.length;s++){var a=t[s];0!==o[a]&&(r=!1)}r&&(u.splice(e--,1),n=i(i.s=t[0]))}return n}var r={},o={1:0},u=[];function i(e){if(r[e])return r[e].exports;var t=r[e]={i:e,l:!1,exports:{}};return n[e].call(t.exports,t,t.exports,i),t.l=!0,t.exports}i.m=n,i.c=r,i.d=function(n,e,t){i.o(n,e)||Object.defineProperty(n,e,{enumerable:!0,get:t})},i.r=function(n){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(n,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(n,"__esModule",{value:!0})},i.t=function(n,e){if(1&e&&(n=i(n)),8&e)return n;if(4&e&&"object"==typeof n&&n&&n.__esModule)return n;var t=Object.create(null);if(i.r(t),Object.defineProperty(t,"default",{enumerable:!0,value:n}),2&e&&"string"!=typeof n)for(var r in n)i.d(t,r,function(e){return n[e]}.bind(null,r));return t},i.n=function(n){var e=n&&n.__esModule?function(){return n.default}:function(){return n};return i.d(e,"a",e),e},i.o=function(n,e){return Object.prototype.hasOwnProperty.call(n,e)},i.p="/dist/";var s=window.webpackJsonp=window.webpackJsonp||[],a=s.push.bind(s);s.push=e,s=s.slice();for(var c=0;c<s.length;c++)e(s[c]);var l=a;u.push([133,0,4]),t()}({133:function(n,e,t){"use strict";t.r(e),function(n){t(43),t(135),t(136);var e=t(40),r=t.n(e),o=t(8);r.a.initOnLoad(),n((function(){n.nette.init()})),n.nette.ext("spinner",{start:function(){o.a.commit("spinner/SHOW")},complete:function(){o.a.commit("spinner/HIDE")}})}.call(this,t(43))},7:function(n,e,t){"use strict";t.d(e,"a",(function(){return o}));var r=t(8);function o(){var n=r.a.getters["user/getToken"];return null===n?{}:{Authorization:"Bearer "+n}}},8:function(n,e,t){"use strict";var r=t(9),o=t(28),u=t(68),i=t(4),s=t.n(i),a=t(7),c=new(function(){function n(){}return n.prototype.fetchAll=function(){return s.a.get("features",{headers:Object(a.a)()})},n}()),l={namespaced:!0,state:{features:{}},actions:{fetch:function(n){var e=n.commit;return c.fetchAll().then((function(n){e("SET",n.data)}))}},getters:{isEnabled:function(n){return function(e){try{return n.features[e].enabled}catch(n){return}}},configuration:function(n){return function(e){try{return n.features[e]}catch(n){return}}}},mutations:{SET:function(n,e){n.features=e}}},f={namespaced:!0,state:{show:"responsive",minimize:!1},mutations:{toggleSidebarDesktop:function(n){var e=[!0,"responsive"].includes(n.show);n.show=!e&&"responsive"},toggleSidebarMobile:function(n){var e=[!1,"responsive"].includes(n.show);n.show=!!e||"responsive"},set:function(n,e){var t=e[0],r=e[1];n[t]=r}}},p={namespaced:!0,state:{enabled:null,text:null},getters:{isEnabled:function(n){return n.enabled},text:function(n){return n.text}},mutations:{HIDE:function(n){n.enabled=!1,n.text=null},SHOW:function(n,e){void 0===e&&(e=null),n.enabled=!0,n.text=e},UPDATE_TEXT:function(n,e){n.text=e}}},d=new(function(){function n(){}return n.prototype.apiLogin=function(n,e){var t={username:n,password:e};return s.a.post("user/signIn",t)},n.prototype.netteLogin=function(n,e){var t=new URLSearchParams;return t.append("username",n),t.append("password",e),t.append("remember","on"),t.append("send","Sign+in"),t.append("_do","signInForm-submit"),s.a.post("//"+window.location.host+"/sign/in",t)},n.prototype.login=function(n,e){return Promise.all([this.apiLogin(n,e),this.netteLogin(n,e)])},n.prototype.logout=function(){return s.a.get("//"+window.location.host+"/sign/out")},n}()),g={namespaced:!0,state:{user:null},actions:{signIn:function(n,e){var t=n.commit;return d.login(e.username,e.password).then((function(n){var e=n[0];return t("SIGN_IN",e.data),n})).catch((function(n){return console.error(n),Promise.reject(n)}))},signOut:function(n){var e=n.commit;return d.logout().then((function(){e("SIGN_OUT")}))}},getters:{isLoggedIn:function(n){return null!==n.user},getId:function(n){return null===n.user?null:n.user.id},getName:function(n){return null===n.user?null:n.user.username},getRole:function(n){return null===n.user?null:n.user.role},getToken:function(n){return null===n.user?null:n.user.token}},mutations:{SIGN_IN:function(n,e){n.user=e},SIGN_OUT:function(n){n.user=null}}},m=t(290),O={socket:{isConnected:!1,reconnectError:!1},requests:{},responses:{}},v={SOCKET_ONOPEN:function(n,e){r.default.prototype.$socket=e.currentTarget,n.socket.isConnected=!0},SOCKET_ONCLOSE:function(){O.socket.isConnected=!1},SOCKET_ONERROR:function(n,e){console.error(n,e)},SOCKET_ONMESSAGE:function(n,e){n.responses[e.data.msgId]=e},SOCKET_ONSEND:function(n,e){n.requests[e.data.msgId]=e},SOCKET_RECONNECT:function(n,e){console.info(n,e)},SOCKET_RECONNECT_ERROR:function(n){n.socket.reconnectError=!0}},b={state:O,actions:{sendRequest:function(n,e){void 0!==e.data&&void 0===e.data.msgId&&(e.data.msgId=Object(m.a)()),r.default.prototype.$socket.sendObj(e),n.commit("SOCKET_ONSEND",e)}},getters:{isSocketConnected:function(n){return n.socket.isConnected}},mutations:v};r.default.use(o.a);var h=new o.a.Store({modules:{features:l,sidebar:f,spinner:p,user:g,webSocketClient:b},plugins:[Object(u.a)()]});e.a=h}});
//# sourceMappingURL=app.bundle.js.map