(function(t){var e={};function n(r){if(e[r])return e[r].exports;var o=e[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,n),o.l=!0,o.exports}n.m=t,n.c=e,n.d=function(t,e,r){n.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},n.r=function(t){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},n.t=function(t,e){if(1&e&&(t=n(t)),8&e)return t;if(4&e&&"object"===typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)n.d(r,o,function(e){return t[e]}.bind(null,o));return r},n.n=function(t){var e=t&&t.__esModule?function(){return t["default"]}:function(){return t};return n.d(e,"a",e),e},n.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},n.p="/",n(n.s="fd46")})({"0049":function(t,e,n){"use strict";var r=n("65ee").IteratorPrototype,o=n("6756"),i=n("8d23"),c=n("77da"),a=n("ca70"),u=function(){return this};t.exports=function(t,e,n){var f=e+" Iterator";return t.prototype=o(r,{next:i(1,n)}),c(t,f,!1,!0),a[f]=u,t}},"0209":function(t,e,n){var r=n("db8f"),o=Function.toString;"function"!=typeof r.inspectSource&&(r.inspectSource=function(t){return o.call(t)}),t.exports=r.inspectSource},"0368":function(t,e,n){var r=n("a714");t.exports=!r((function(){return 7!=Object.defineProperty({},1,{get:function(){return 7}})[1]}))},"0761":function(t,e,n){var r=n("d0c8"),o=n("caad"),i=n("09d1"),c=n("4dd8"),a=n("c35a"),u=n("8181"),f=function(t,e){this.stopped=t,this.result=e};t.exports=function(t,e,n){var s,l,p,d,v,h,y,b=n&&n.that,g=!(!n||!n.AS_ENTRIES),m=!(!n||!n.IS_ITERATOR),w=!(!n||!n.INTERRUPTED),x=c(e,b,1+g+w),S=function(t){return s&&u(s),new f(!0,t)},j=function(t){return g?(r(t),w?x(t[0],t[1],S):x(t[0],t[1])):w?x(t,S):x(t)};if(m)s=t;else{if(l=a(t),"function"!=typeof l)throw TypeError("Target is not iterable");if(o(l)){for(p=0,d=i(t.length);d>p;p++)if(v=j(t[p]),v&&v instanceof f)return v;return new f(!1)}s=l.call(t)}h=s.next;while(!(y=h.call(s)).done){try{v=j(y.value)}catch(k){throw u(s),k}if("object"==typeof v&&v&&v instanceof f)return v}return new f(!1)}},"0828":function(t,e,n){var r=n("0f33"),o=n("db8f");(t.exports=function(t,e){return o[t]||(o[t]=void 0!==e?e:{})})("versions",[]).push({version:"3.10.1",mode:r?"pure":"global",copyright:"© 2021 Denis Pushkarev (zloirock.ru)"})},"09d1":function(t,e,n){var r=n("59c2"),o=Math.min;t.exports=function(t){return t>0?o(r(t),9007199254740991):0}},"09e4":function(t,e,n){(function(e){var n=function(t){return t&&t.Math==Math&&t};t.exports=n("object"==typeof globalThis&&globalThis)||n("object"==typeof window&&window)||n("object"==typeof self&&self)||n("object"==typeof e&&e)||function(){return this}()||Function("return this")()}).call(this,n("c8ba"))},"0d05":function(t,e,n){var r=n("09e4"),o=n("0209"),i=r.WeakMap;t.exports="function"===typeof i&&/native code/.test(o(i))},"0e17":function(t,e,n){"use strict";var r={}.propertyIsEnumerable,o=Object.getOwnPropertyDescriptor,i=o&&!r.call({1:2},1);e.f=i?function(t){var e=o(this,t);return!!e&&e.enumerable}:r},"0ee6":function(t,e,n){var r=n("d1d7"),o=n("09e4"),i=function(t){return"function"==typeof t?t:void 0};t.exports=function(t,e){return arguments.length<2?i(r[t])||i(o[t]):r[t]&&r[t][e]||o[t]&&o[t][e]}},"0ef5":function(t,e,n){"use strict";n.d(e,"h",(function(){return r})),n.d(e,"e",(function(){return o})),n.d(e,"f",(function(){return i})),n.d(e,"c",(function(){return c})),n.d(e,"b",(function(){return a})),n.d(e,"a",(function(){return u})),n.d(e,"d",(function(){return f})),n.d(e,"g",(function(){return s}));const r="initStore",o="taintTaintedState",i="untaintTaintedState",c="showPopper",a="hidePopper",u="setHelpLink",f="startStatementEdit",s="stopStatementEdit"},"0f33":function(t,e){t.exports=!1},"0fd9":function(t,e,n){var r,o,i,c=n("09e4"),a=n("a714"),u=n("4dd8"),f=n("68d9"),s=n("c4dd"),l=n("68e0"),p=n("6629"),d=c.location,v=c.setImmediate,h=c.clearImmediate,y=c.process,b=c.MessageChannel,g=c.Dispatch,m=0,w={},x="onreadystatechange",S=function(t){if(w.hasOwnProperty(t)){var e=w[t];delete w[t],e()}},j=function(t){return function(){S(t)}},k=function(t){S(t.data)},O=function(t){c.postMessage(t+"",d.protocol+"//"+d.host)};v&&h||(v=function(t){var e=[],n=1;while(arguments.length>n)e.push(arguments[n++]);return w[++m]=function(){("function"==typeof t?t:Function(t)).apply(void 0,e)},r(m),m},h=function(t){delete w[t]},p?r=function(t){y.nextTick(j(t))}:g&&g.now?r=function(t){g.now(j(t))}:b&&!l?(o=new b,i=o.port2,o.port1.onmessage=k,r=u(i.postMessage,i,1)):c.addEventListener&&"function"==typeof postMessage&&!c.importScripts&&d&&"file:"!==d.protocol&&!a(O)?(r=O,c.addEventListener("message",k,!1)):r=x in s("script")?function(t){f.appendChild(s("script"))[x]=function(){f.removeChild(this),S(t)}}:function(t){setTimeout(j(t),0)}),t.exports={set:v,clear:h}},"189d":function(t,e){t.exports=function(t){try{return{error:!1,value:t()}}catch(e){return{error:!0,value:e}}}},"199f":function(t,e,n){var r=n("09e4"),o=n("2439").f,i=n("3261"),c=n("7024"),a=n("79ae"),u=n("2d0a"),f=n("25d0");t.exports=function(t,e){var n,s,l,p,d,v,h=t.target,y=t.global,b=t.stat;if(s=y?r:b?r[h]||a(h,{}):(r[h]||{}).prototype,s)for(l in e){if(d=e[l],t.noTargetGet?(v=o(s,l),p=v&&v.value):p=s[l],n=f(y?l:h+(b?".":"#")+l,t.forced),!n&&void 0!==p){if(typeof d===typeof p)continue;u(d,p)}(t.sham||p&&p.sham)&&i(d,"sham",!0),c(s,l,d,t)}}},"1fc1":function(t,e){t.exports={}},"20a7":function(t,e,n){var r=n("6629"),o=n("fce5"),i=n("a714");t.exports=!!Object.getOwnPropertySymbols&&!i((function(){return!Symbol.sham&&(r?38===o:o>37&&o<41)}))},2439:function(t,e,n){var r=n("0368"),o=n("0e17"),i=n("8d23"),c=n("a84f"),a=n("fe68"),u=n("7f34"),f=n("bf45"),s=Object.getOwnPropertyDescriptor;e.f=r?s:function(t,e){if(t=c(t),e=a(e,!0),f)try{return s(t,e)}catch(n){}if(u(t,e))return i(!o.f.call(t,e),t[e])}},"25d0":function(t,e,n){var r=n("a714"),o=/#|\.prototype\./,i=function(t,e){var n=a[c(t)];return n==f||n!=u&&("function"==typeof e?r(e):!!e)},c=i.normalize=function(t){return String(t).replace(o,".").toLowerCase()},a=i.data={},u=i.NATIVE="N",f=i.POLYFILL="P";t.exports=i},"2ba0":function(t,e,n){var r=n("7024");t.exports=function(t,e,n){for(var o in e)r(t,o,e[o],n);return t}},"2d0a":function(t,e,n){var r=n("7f34"),o=n("b973"),i=n("2439"),c=n("4c07");t.exports=function(t,e){for(var n=o(e),a=c.f,u=i.f,f=0;f<n.length;f++){var s=n[f];r(t,s)||a(t,s,u(e,s))}}},3261:function(t,e,n){var r=n("0368"),o=n("4c07"),i=n("8d23");t.exports=r?function(t,e,n){return o.f(t,e,i(1,n))}:function(t,e,n){return t[e]=n,t}},"37e1":function(t,e,n){"use strict";var r=n("199f"),o=n("0f33"),i=n("c85d"),c=n("a714"),a=n("0ee6"),u=n("894d"),f=n("8fe4"),s=n("7024"),l=!!i&&c((function(){i.prototype["finally"].call({then:function(){}},(function(){}))}));r({target:"Promise",proto:!0,real:!0,forced:l},{finally:function(t){var e=u(this,a("Promise")),n="function"==typeof t;return this.then(n?function(n){return f(e,t()).then((function(){return n}))}:t,n?function(n){return f(e,t()).then((function(){throw n}))}:t)}}),o||"function"!=typeof i||i.prototype["finally"]||s(i.prototype,"finally",a("Promise").prototype["finally"])},"4c07":function(t,e,n){var r=n("0368"),o=n("bf45"),i=n("d0c8"),c=n("fe68"),a=Object.defineProperty;e.f=r?a:function(t,e,n){if(i(t),e=c(e,!0),i(n),o)try{return a(t,e,n)}catch(r){}if("get"in n||"set"in n)throw TypeError("Accessors not supported");return"value"in n&&(t[e]=n.value),t}},"4dd8":function(t,e,n){var r=n("90c5");t.exports=function(t,e,n){if(r(t),void 0===e)return t;switch(n){case 0:return function(){return t.call(e)};case 1:return function(n){return t.call(e,n)};case 2:return function(n,r){return t.call(e,n,r)};case 3:return function(n,r,o){return t.call(e,n,r,o)}}return function(){return t.apply(e,arguments)}}},"51d2":function(t,e,n){"use strict";var r=n("0368"),o=n("a714"),i=n("f14a"),c=n("a5b6"),a=n("0e17"),u=n("ebca"),f=n("774c"),s=Object.assign,l=Object.defineProperty;t.exports=!s||o((function(){if(r&&1!==s({b:1},s(l({},"a",{enumerable:!0,get:function(){l(this,"b",{value:3,enumerable:!1})}}),{b:2})).b)return!0;var t={},e={},n=Symbol(),o="abcdefghijklmnopqrst";return t[n]=7,o.split("").forEach((function(t){e[t]=t})),7!=s({},t)[n]||i(s({},e)).join("")!=o}))?function(t,e){var n=u(t),o=arguments.length,s=1,l=c.f,p=a.f;while(o>s){var d,v=f(arguments[s++]),h=l?i(v).concat(l(v)):i(v),y=h.length,b=0;while(y>b)d=h[b++],r&&!p.call(v,d)||(n[d]=v[d])}return n}:s},5923:function(t,e,n){var r,o,i,c,a,u,f,s,l=n("09e4"),p=n("2439").f,d=n("0fd9").set,v=n("68e0"),h=n("f514"),y=n("6629"),b=l.MutationObserver||l.WebKitMutationObserver,g=l.document,m=l.process,w=l.Promise,x=p(l,"queueMicrotask"),S=x&&x.value;S||(r=function(){var t,e;y&&(t=m.domain)&&t.exit();while(o){e=o.fn,o=o.next;try{e()}catch(n){throw o?c():i=void 0,n}}i=void 0,t&&t.enter()},v||y||h||!b||!g?w&&w.resolve?(f=w.resolve(void 0),s=f.then,c=function(){s.call(f,r)}):c=y?function(){m.nextTick(r)}:function(){d.call(l,r)}:(a=!0,u=g.createTextNode(""),new b(r).observe(u,{characterData:!0}),c=function(){u.data=a=!a})),t.exports=S||function(t){var e={fn:t,next:void 0};i&&(i.next=e),o||(o=e,c()),i=e}},"59c2":function(t,e){var n=Math.ceil,r=Math.floor;t.exports=function(t){return isNaN(t=+t)?0:(t>0?r:n)(t)}},"5dc8":function(t,e,n){var r=n("199f"),o=n("51d2");r({target:"Object",stat:!0,forced:Object.assign!==o},{assign:o})},"5f2f":function(t,e,n){var r=n("0ee6");t.exports=r("navigator","userAgent")||""},6117:function(t,e,n){var r=n("8b0e"),o=r("toStringTag"),i={};i[o]="z",t.exports="[object z]"===String(i)},"613f":function(t,e,n){var r=n("8b0e"),o=n("6756"),i=n("4c07"),c=r("unscopables"),a=Array.prototype;void 0==a[c]&&i.f(a,c,{configurable:!0,value:o(null)}),t.exports=function(t){a[c][t]=!0}},"65ee":function(t,e,n){"use strict";var r,o,i,c=n("a714"),a=n("9aed"),u=n("3261"),f=n("7f34"),s=n("8b0e"),l=n("0f33"),p=s("iterator"),d=!1,v=function(){return this};[].keys&&(i=[].keys(),"next"in i?(o=a(a(i)),o!==Object.prototype&&(r=o)):d=!0);var h=void 0==r||c((function(){var t={};return r[p].call(t)!==t}));h&&(r={}),l&&!h||f(r,p)||u(r,p,v),t.exports={IteratorPrototype:r,BUGGY_SAFARI_ITERATORS:d}},6629:function(t,e,n){var r=n("d714"),o=n("09e4");t.exports="process"==r(o.process)},6756:function(t,e,n){var r,o=n("d0c8"),i=n("df84"),c=n("c51e"),a=n("1fc1"),u=n("68d9"),f=n("c4dd"),s=n("816e"),l=">",p="<",d="prototype",v="script",h=s("IE_PROTO"),y=function(){},b=function(t){return p+v+l+t+p+"/"+v+l},g=function(t){t.write(b("")),t.close();var e=t.parentWindow.Object;return t=null,e},m=function(){var t,e=f("iframe"),n="java"+v+":";return e.style.display="none",u.appendChild(e),e.src=String(n),t=e.contentWindow.document,t.open(),t.write(b("document.F=Object")),t.close(),t.F},w=function(){try{r=document.domain&&new ActiveXObject("htmlfile")}catch(e){}w=r?g(r):m();var t=c.length;while(t--)delete w[d][c[t]];return w()};a[h]=!0,t.exports=Object.create||function(t,e){var n;return null!==t?(y[d]=o(t),n=new y,y[d]=null,n[h]=t):n=w(),void 0===e?n:i(n,e)}},"68d9":function(t,e,n){var r=n("0ee6");t.exports=r("document","documentElement")},"68e0":function(t,e,n){var r=n("5f2f");t.exports=/(?:iphone|ipod|ipad).*applewebkit/i.test(r)},7024:function(t,e,n){var r=n("09e4"),o=n("3261"),i=n("7f34"),c=n("79ae"),a=n("0209"),u=n("a547"),f=u.get,s=u.enforce,l=String(String).split("String");(t.exports=function(t,e,n,a){var u,f=!!a&&!!a.unsafe,p=!!a&&!!a.enumerable,d=!!a&&!!a.noTargetGet;"function"==typeof n&&("string"!=typeof e||i(n,"name")||o(n,"name",e),u=s(n),u.source||(u.source=l.join("string"==typeof e?e:""))),t!==r?(f?!d&&t[e]&&(p=!0):delete t[e],p?t[e]=n:o(t,e,n)):p?t[e]=n:c(e,n)})(Function.prototype,"toString",(function(){return"function"==typeof this&&f(this).source||a(this)}))},"761e":function(t,e,n){"use strict";var r=n("90c5"),o=function(t){var e,n;this.promise=new t((function(t,r){if(void 0!==e||void 0!==n)throw TypeError("Bad Promise constructor");e=t,n=r})),this.resolve=r(e),this.reject=r(n)};t.exports.f=function(t){return new o(t)}},"76af":function(t,e){t.exports=function(t){if(void 0==t)throw TypeError("Can't call method on "+t);return t}},"774c":function(t,e,n){var r=n("a714"),o=n("d714"),i="".split;t.exports=r((function(){return!Object("z").propertyIsEnumerable(0)}))?function(t){return"String"==o(t)?i.call(t,""):Object(t)}:Object},"77da":function(t,e,n){var r=n("4c07").f,o=n("7f34"),i=n("8b0e"),c=i("toStringTag");t.exports=function(t,e,n){t&&!o(t=n?t:t.prototype,c)&&r(t,c,{configurable:!0,value:e})}},7820:function(t,e,n){var r=n("6117"),o=n("d714"),i=n("8b0e"),c=i("toStringTag"),a="Arguments"==o(function(){return arguments}()),u=function(t,e){try{return t[e]}catch(n){}};t.exports=r?o:function(t){var e,n,r;return void 0===t?"Undefined":null===t?"Null":"string"==typeof(n=u(e=Object(t),c))?n:a?o(e):"Object"==(r=o(e))&&"function"==typeof e.callee?"Arguments":r}},"793f":function(t,e,n){"use strict";var r=n("0ee6"),o=n("4c07"),i=n("8b0e"),c=n("0368"),a=i("species");t.exports=function(t){var e=r(t),n=o.f;c&&e&&!e[a]&&n(e,a,{configurable:!0,get:function(){return this}})}},"79ae":function(t,e,n){var r=n("09e4"),o=n("3261");t.exports=function(t,e){try{o(r,t,e)}catch(n){r[t]=e}return e}},"7f34":function(t,e){var n={}.hasOwnProperty;t.exports=function(t,e){return n.call(t,e)}},"808c":function(t,e,n){var r=n("8b0e"),o=r("iterator"),i=!1;try{var c=0,a={next:function(){return{done:!!c++}},return:function(){i=!0}};a[o]=function(){return this},Array.from(a,(function(){throw 2}))}catch(u){}t.exports=function(t,e){if(!e&&!i)return!1;var n=!1;try{var r={};r[o]=function(){return{next:function(){return{done:n=!0}}}},t(r)}catch(u){}return n}},"816e":function(t,e,n){var r=n("0828"),o=n("f385"),i=r("keys");t.exports=function(t){return i[t]||(i[t]=o(t))}},8181:function(t,e,n){var r=n("d0c8");t.exports=function(t){var e=t["return"];if(void 0!==e)return r(e.call(t)).value}},8779:function(t,e,n){var r=n("a714");t.exports=!r((function(){function t(){}return t.prototype.constructor=null,Object.getPrototypeOf(new t)!==t.prototype}))},"894d":function(t,e,n){var r=n("d0c8"),o=n("90c5"),i=n("8b0e"),c=i("species");t.exports=function(t,e){var n,i=r(t).constructor;return void 0===i||void 0==(n=r(i)[c])?e:o(n)}},"8b0e":function(t,e,n){var r=n("09e4"),o=n("0828"),i=n("7f34"),c=n("f385"),a=n("20a7"),u=n("aa51"),f=o("wks"),s=r.Symbol,l=u?s:s&&s.withoutSetter||c;t.exports=function(t){return i(f,t)&&(a||"string"==typeof f[t])||(a&&i(s,t)?f[t]=s[t]:f[t]=l("Symbol."+t)),f[t]}},"8d23":function(t,e){t.exports=function(t,e){return{enumerable:!(1&t),configurable:!(2&t),writable:!(4&t),value:e}}},"8f08":function(t,e){t.exports=function(t,e,n){if(!(t instanceof e))throw TypeError("Incorrect "+(n?n+" ":"")+"invocation");return t}},"8fe4":function(t,e,n){var r=n("d0c8"),o=n("bb6e"),i=n("761e");t.exports=function(t,e){if(r(t),o(e)&&e.constructor===t)return e;var n=i.f(t),c=n.resolve;return c(e),n.promise}},"90c5":function(t,e){t.exports=function(t){if("function"!=typeof t)throw TypeError(String(t)+" is not a function");return t}},"997c":function(t,e,n){var r=n("d0c8"),o=n("ba83");t.exports=Object.setPrototypeOf||("__proto__"in{}?function(){var t,e=!1,n={};try{t=Object.getOwnPropertyDescriptor(Object.prototype,"__proto__").set,t.call(n,[]),e=n instanceof Array}catch(i){}return function(n,i){return r(n),o(i),e?t.call(n,i):n.__proto__=i,n}}():void 0)},"9aed":function(t,e,n){var r=n("7f34"),o=n("ebca"),i=n("816e"),c=n("8779"),a=i("IE_PROTO"),u=Object.prototype;t.exports=c?Object.getPrototypeOf:function(t){return t=o(t),r(t,a)?t[a]:"function"==typeof t.constructor&&t instanceof t.constructor?t.constructor.prototype:t instanceof Object?u:null}},a547:function(t,e,n){var r,o,i,c=n("0d05"),a=n("09e4"),u=n("bb6e"),f=n("3261"),s=n("7f34"),l=n("db8f"),p=n("816e"),d=n("1fc1"),v=a.WeakMap,h=function(t){return i(t)?o(t):r(t,{})},y=function(t){return function(e){var n;if(!u(e)||(n=o(e)).type!==t)throw TypeError("Incompatible receiver, "+t+" required");return n}};if(c){var b=l.state||(l.state=new v),g=b.get,m=b.has,w=b.set;r=function(t,e){return e.facade=t,w.call(b,t,e),e},o=function(t){return g.call(b,t)||{}},i=function(t){return m.call(b,t)}}else{var x=p("state");d[x]=!0,r=function(t,e){return e.facade=t,f(t,x,e),e},o=function(t){return s(t,x)?t[x]:{}},i=function(t){return s(t,x)}}t.exports={set:r,get:o,has:i,enforce:h,getterFor:y}},a580:function(t,e,n){"use strict";var r=n("199f"),o=n("0049"),i=n("9aed"),c=n("997c"),a=n("77da"),u=n("3261"),f=n("7024"),s=n("8b0e"),l=n("0f33"),p=n("ca70"),d=n("65ee"),v=d.IteratorPrototype,h=d.BUGGY_SAFARI_ITERATORS,y=s("iterator"),b="keys",g="values",m="entries",w=function(){return this};t.exports=function(t,e,n,s,d,x,S){o(n,e,s);var j,k,O,E=function(t){if(t===d&&_)return _;if(!h&&t in C)return C[t];switch(t){case b:return function(){return new n(this,t)};case g:return function(){return new n(this,t)};case m:return function(){return new n(this,t)}}return function(){return new n(this)}},P=e+" Iterator",T=!1,C=t.prototype,R=C[y]||C["@@iterator"]||d&&C[d],_=!h&&R||E(d),M="Array"==e&&C.entries||R;if(M&&(j=i(M.call(new t)),v!==Object.prototype&&j.next&&(l||i(j)===v||(c?c(j,v):"function"!=typeof j[y]&&u(j,y,w)),a(j,P,!0,!0),l&&(p[P]=w))),d==g&&R&&R.name!==g&&(T=!0,_=function(){return R.call(this)}),l&&!S||C[y]===_||u(C,y,_),p[e]=_,d)if(k={values:E(g),keys:x?_:E(b),entries:E(m)},S)for(O in k)(h||T||!(O in C))&&f(C,O,k[O]);else r({target:e,proto:!0,forced:h||T},k);return k}},a5b6:function(t,e){e.f=Object.getOwnPropertySymbols},a714:function(t,e){t.exports=function(t){try{return!!t()}catch(e){return!0}}},a84f:function(t,e,n){var r=n("774c"),o=n("76af");t.exports=function(t){return r(o(t))}},aa51:function(t,e,n){var r=n("20a7");t.exports=r&&!Symbol.sham&&"symbol"==typeof Symbol.iterator},b1b0:function(t,e,n){var r=n("09e4");t.exports=function(t,e){var n=r.console;n&&n.error&&(1===arguments.length?n.error(t):n.error(t,e))}},b973:function(t,e,n){var r=n("0ee6"),o=n("fdbe"),i=n("a5b6"),c=n("d0c8");t.exports=r("Reflect","ownKeys")||function(t){var e=o.f(c(t)),n=i.f;return n?e.concat(n(t)):e}},ba83:function(t,e,n){var r=n("bb6e");t.exports=function(t){if(!r(t)&&null!==t)throw TypeError("Can't set "+String(t)+" as a prototype");return t}},bb6e:function(t,e){t.exports=function(t){return"object"===typeof t?null!==t:"function"===typeof t}},bf45:function(t,e,n){var r=n("0368"),o=n("a714"),i=n("c4dd");t.exports=!r&&!o((function(){return 7!=Object.defineProperty(i("div"),"a",{get:function(){return 7}}).a}))},c272:function(t,e,n){var r=n("a84f"),o=n("09d1"),i=n("fb8a"),c=function(t){return function(e,n,c){var a,u=r(e),f=o(u.length),s=i(c,f);if(t&&n!=n){while(f>s)if(a=u[s++],a!=a)return!0}else for(;f>s;s++)if((t||s in u)&&u[s]===n)return t||s||0;return!t&&-1}};t.exports={includes:c(!0),indexOf:c(!1)}},c35a:function(t,e,n){var r=n("7820"),o=n("ca70"),i=n("8b0e"),c=i("iterator");t.exports=function(t){if(void 0!=t)return t[c]||t["@@iterator"]||o[r(t)]}},c4dd:function(t,e,n){var r=n("09e4"),o=n("bb6e"),i=r.document,c=o(i)&&o(i.createElement);t.exports=function(t){return c?i.createElement(t):{}}},c51e:function(t,e){t.exports=["constructor","hasOwnProperty","isPrototypeOf","propertyIsEnumerable","toLocaleString","toString","valueOf"]},c85d:function(t,e,n){var r=n("09e4");t.exports=r.Promise},c8ba:function(t,e){var n;n=function(){return this}();try{n=n||new Function("return this")()}catch(r){"object"===typeof window&&(n=window)}t.exports=n},ca70:function(t,e){t.exports={}},caad:function(t,e,n){var r=n("8b0e"),o=n("ca70"),i=r("iterator"),c=Array.prototype;t.exports=function(t){return void 0!==t&&(o.Array===t||c[i]===t)}},d0c8:function(t,e,n){var r=n("bb6e");t.exports=function(t){if(!r(t))throw TypeError(String(t)+" is not an object");return t}},d1d7:function(t,e,n){var r=n("09e4");t.exports=r},d714:function(t,e){var n={}.toString;t.exports=function(t){return n.call(t).slice(8,-1)}},db8f:function(t,e,n){var r=n("09e4"),o=n("79ae"),i="__core-js_shared__",c=r[i]||o(i,{});t.exports=c},df84:function(t,e,n){var r=n("0368"),o=n("4c07"),i=n("d0c8"),c=n("f14a");t.exports=r?Object.defineProperties:function(t,e){i(t);var n,r=c(e),a=r.length,u=0;while(a>u)o.f(t,n=r[u++],e[n]);return t}},e379:function(t,e,n){"use strict";var r,o,i,c,a=n("199f"),u=n("0f33"),f=n("09e4"),s=n("0ee6"),l=n("c85d"),p=n("7024"),d=n("2ba0"),v=n("77da"),h=n("793f"),y=n("bb6e"),b=n("90c5"),g=n("8f08"),m=n("0209"),w=n("0761"),x=n("808c"),S=n("894d"),j=n("0fd9").set,k=n("5923"),O=n("8fe4"),E=n("b1b0"),P=n("761e"),T=n("189d"),C=n("a547"),R=n("25d0"),_=n("8b0e"),M=n("6629"),A=n("fce5"),I=_("species"),H="Promise",F=C.get,L=C.set,N=C.getterFor(H),q=l,D=f.TypeError,U=f.document,G=f.process,z=s("fetch"),W=P.f,B=W,Q=!!(U&&U.createEvent&&f.dispatchEvent),Y="function"==typeof PromiseRejectionEvent,K="unhandledrejection",V="rejectionhandled",X=0,J=1,Z=2,$=1,tt=2,et=R(H,(function(){var t=m(q)!==String(q);if(!t){if(66===A)return!0;if(!M&&!Y)return!0}if(u&&!q.prototype["finally"])return!0;if(A>=51&&/native code/.test(q))return!1;var e=q.resolve(1),n=function(t){t((function(){}),(function(){}))},r=e.constructor={};return r[I]=n,!(e.then((function(){}))instanceof n)})),nt=et||!x((function(t){q.all(t)["catch"]((function(){}))})),rt=function(t){var e;return!(!y(t)||"function"!=typeof(e=t.then))&&e},ot=function(t,e){if(!t.notified){t.notified=!0;var n=t.reactions;k((function(){var r=t.value,o=t.state==J,i=0;while(n.length>i){var c,a,u,f=n[i++],s=o?f.ok:f.fail,l=f.resolve,p=f.reject,d=f.domain;try{s?(o||(t.rejection===tt&&ut(t),t.rejection=$),!0===s?c=r:(d&&d.enter(),c=s(r),d&&(d.exit(),u=!0)),c===f.promise?p(D("Promise-chain cycle")):(a=rt(c))?a.call(c,l,p):l(c)):p(r)}catch(v){d&&!u&&d.exit(),p(v)}}t.reactions=[],t.notified=!1,e&&!t.rejection&&ct(t)}))}},it=function(t,e,n){var r,o;Q?(r=U.createEvent("Event"),r.promise=e,r.reason=n,r.initEvent(t,!1,!0),f.dispatchEvent(r)):r={promise:e,reason:n},!Y&&(o=f["on"+t])?o(r):t===K&&E("Unhandled promise rejection",n)},ct=function(t){j.call(f,(function(){var e,n=t.facade,r=t.value,o=at(t);if(o&&(e=T((function(){M?G.emit("unhandledRejection",r,n):it(K,n,r)})),t.rejection=M||at(t)?tt:$,e.error))throw e.value}))},at=function(t){return t.rejection!==$&&!t.parent},ut=function(t){j.call(f,(function(){var e=t.facade;M?G.emit("rejectionHandled",e):it(V,e,t.value)}))},ft=function(t,e,n){return function(r){t(e,r,n)}},st=function(t,e,n){t.done||(t.done=!0,n&&(t=n),t.value=e,t.state=Z,ot(t,!0))},lt=function(t,e,n){if(!t.done){t.done=!0,n&&(t=n);try{if(t.facade===e)throw D("Promise can't be resolved itself");var r=rt(e);r?k((function(){var n={done:!1};try{r.call(e,ft(lt,n,t),ft(st,n,t))}catch(o){st(n,o,t)}})):(t.value=e,t.state=J,ot(t,!1))}catch(o){st({done:!1},o,t)}}};et&&(q=function(t){g(this,q,H),b(t),r.call(this);var e=F(this);try{t(ft(lt,e),ft(st,e))}catch(n){st(e,n)}},r=function(t){L(this,{type:H,done:!1,notified:!1,parent:!1,reactions:[],rejection:!1,state:X,value:void 0})},r.prototype=d(q.prototype,{then:function(t,e){var n=N(this),r=W(S(this,q));return r.ok="function"!=typeof t||t,r.fail="function"==typeof e&&e,r.domain=M?G.domain:void 0,n.parent=!0,n.reactions.push(r),n.state!=X&&ot(n,!1),r.promise},catch:function(t){return this.then(void 0,t)}}),o=function(){var t=new r,e=F(t);this.promise=t,this.resolve=ft(lt,e),this.reject=ft(st,e)},P.f=W=function(t){return t===q||t===i?new o(t):B(t)},u||"function"!=typeof l||(c=l.prototype.then,p(l.prototype,"then",(function(t,e){var n=this;return new q((function(t,e){c.call(n,t,e)})).then(t,e)}),{unsafe:!0}),"function"==typeof z&&a({global:!0,enumerable:!0,forced:!0},{fetch:function(t){return O(q,z.apply(f,arguments))}}))),a({global:!0,wrap:!0,forced:et},{Promise:q}),v(q,H,!1,!0),h(H),i=s(H),a({target:H,stat:!0,forced:et},{reject:function(t){var e=W(this);return e.reject.call(void 0,t),e.promise}}),a({target:H,stat:!0,forced:u||et},{resolve:function(t){return O(u&&this===i?q:this,t)}}),a({target:H,stat:!0,forced:nt},{all:function(t){var e=this,n=W(e),r=n.resolve,o=n.reject,i=T((function(){var n=b(e.resolve),i=[],c=0,a=1;w(t,(function(t){var u=c++,f=!1;i.push(void 0),a++,n.call(e,t).then((function(t){f||(f=!0,i[u]=t,--a||r(i))}),o)})),--a||r(i)}));return i.error&&o(i.value),n.promise},race:function(t){var e=this,n=W(e),r=n.reject,o=T((function(){var o=b(e.resolve);w(t,(function(t){o.call(e,t).then(n.resolve,r)}))}));return o.error&&r(o.value),n.promise}})},e623:function(t,e,n){"use strict";var r=n("a84f"),o=n("613f"),i=n("ca70"),c=n("a547"),a=n("a580"),u="Array Iterator",f=c.set,s=c.getterFor(u);t.exports=a(Array,"Array",(function(t,e){f(this,{type:u,target:r(t),index:0,kind:e})}),(function(){var t=s(this),e=t.target,n=t.kind,r=t.index++;return!e||r>=e.length?(t.target=void 0,{value:void 0,done:!0}):"keys"==n?{value:r,done:!1}:"values"==n?{value:e[r],done:!1}:{value:[r,e[r]],done:!1}}),"values"),i.Arguments=i.Array,o("keys"),o("values"),o("entries")},ebca:function(t,e,n){var r=n("76af");t.exports=function(t){return Object(r(t))}},f14a:function(t,e,n){var r=n("f55b"),o=n("c51e");t.exports=Object.keys||function(t){return r(t,o)}},f385:function(t,e){var n=0,r=Math.random();t.exports=function(t){return"Symbol("+String(void 0===t?"":t)+")_"+(++n+r).toString(36)}},f514:function(t,e,n){var r=n("5f2f");t.exports=/web0s(?!.*chrome)/i.test(r)},f55b:function(t,e,n){var r=n("7f34"),o=n("a84f"),i=n("c272").indexOf,c=n("1fc1");t.exports=function(t,e){var n,a=o(t),u=0,f=[];for(n in a)!r(c,n)&&r(a,n)&&f.push(n);while(e.length>u)r(a,n=e[u++])&&(~i(f,n)||f.push(n));return f}},fb8a:function(t,e,n){var r=n("59c2"),o=Math.max,i=Math.min;t.exports=function(t,e){var n=r(t);return n<0?o(n+e,0):i(n,e)}},fce5:function(t,e,n){var r,o,i=n("09e4"),c=n("5f2f"),a=i.process,u=a&&a.versions,f=u&&u.v8;f?(r=f.split("."),o=r[0]+r[1]):c&&(r=c.match(/Edge\/(\d+)/),(!r||r[1]>=74)&&(r=c.match(/Chrome\/(\d+)/),r&&(o=r[1]))),t.exports=o&&+o},fd46:function(t,e,n){"use strict";n.r(e);n("e623"),n("e379"),n("5dc8"),n("37e1");var r=n("0ef5");class o{constructor(t,e,n){this.mwHooks=t,this.taintedChecker=e,this.statementTracker=n}addStore(t){this.addStartEditingHook(t),this.addSaveHook(t),this.addStopEditingHook(t)}addStartEditingHook(t){this.mwHooks("wikibase.statement.startEditing").add(e=>{t.dispatch(r["d"],e)})}addSaveHook(t){this.mwHooks("wikibase.statement.saved").add((e,n,o,i)=>{t.state.statementsTaintedState[n]?t.dispatch(r["f"],n):this.taintedChecker.check(o,i)&&t.dispatch(r["e"],n)}),this.mwHooks("wikibase.statement.saved").add((t,e,n,r)=>{this.statementTracker.trackChanges(n,r)})}addStopEditingHook(t){this.mwHooks("wikibase.statement.stopEditing").add(e=>{t.dispatch(r["g"],e)})}}class i{check(t,e){return null!==t&&!t.getClaim().getMainSnak().equals(e.getClaim().getMainSnak())&&t.getReferences().equals(e.getReferences())&&!t.getReferences().isEmpty()}}class c{constructor(t,e){this.track=t,this.refChangeCounter=e}trackChanges(t,e){if(null===t)return;const n=this.refChangeCounter.countOldReferencesRemovedOrChanged(t.getReferences(),e.getReferences()),r=t.getReferences().length,o=e.getReferences().length;this.mainSnakChanged(t,e)?n===r?this.track("counter.wikibase.view.tainted-ref.mainSnakChanged.allReferencesChanged",1):0===n&&r===o?this.track("counter.wikibase.view.tainted-ref.mainSnakChanged.noReferencesChanged",1):this.track("counter.wikibase.view.tainted-ref.mainSnakChanged.someNotAllReferencesChanged",1):(n>=1&&this.track("counter.wikibase.view.tainted-ref.mainSnakUnchanged.someReferencesChanged",1),this.qualifierChange(t,e)&&this.track("counter.wikibase.view.tainted-ref.mainSnakUnchanged.someQualifierChanged",1))}mainSnakChanged(t,e){return!t.getClaim().getMainSnak().equals(e.getClaim().getMainSnak())}qualifierChange(t,e){const n=t.getClaim().getQualifiers(),r=e.getClaim().getQualifiers();return!n.equals(r)}}class a{countOldReferencesRemovedOrChanged(t,e){let n=0;return t.each((t,r)=>{e.hasItem(r)||n++}),n}}const u="wikibase.tainted-ref";var f=async()=>{const t=window;function e(e){return t.mw.message(e).text()}if(t.mw.config.get("wbTaintedReferencesEnabled")){const n=await t.mw.loader.using(u),r=n(u),f=new c(t.mw.track,new a),s=new o(t.mw.hook,new i,f);t.mw.hook("wikibase.entityPage.entityView.rendered").add(()=>{const n=t.mw.util.getUrl("Special:MyLanguage/Help:Sources");r.launch(s,n,e,t.mw.track)})}};"loading"===document.readyState?document.addEventListener("DOMContentLoaded",f):f()},fdbe:function(t,e,n){var r=n("f55b"),o=n("c51e"),i=o.concat("length","prototype");e.f=Object.getOwnPropertyNames||function(t){return r(t,i)}},fe68:function(t,e,n){var r=n("bb6e");t.exports=function(t,e){if(!r(t))return t;var n,o;if(e&&"function"==typeof(n=t.toString)&&!r(o=n.call(t)))return o;if("function"==typeof(n=t.valueOf)&&!r(o=n.call(t)))return o;if(!e&&"function"==typeof(n=t.toString)&&!r(o=n.call(t)))return o;throw TypeError("Can't convert object to primitive value")}}});
//# sourceMappingURL=tainted-ref.init.js.map