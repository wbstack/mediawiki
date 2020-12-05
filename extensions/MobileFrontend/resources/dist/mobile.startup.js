this.mfModules=this.mfModules||{},this.mfModules["mobile.startup"]=(window.webpackJsonp=window.webpackJsonp||[]).push([[12],{"./src/mobile.startup/categoryOverlay.js":function(e,t,r){var s=r("./src/mobile.startup/Overlay.js"),n=r("./src/mobile.startup/promisedView.js"),a=r("./src/mobile.startup/Anchor.js"),o=r("./src/mobile.startup/moduleLoaderSingleton.js"),i=r("./src/mobile.startup/headers.js").header;e.exports=function(e){var t,r;return t=new s({className:"category-overlay overlay",headers:[i(mw.msg("mobile-frontend-categories-heading"),e.isAnon?[]:[new a({href:"#/categories/add",label:mw.msg("mobile-frontend-categories-add"),additionalClassNames:"add continue"})])]}),r=n(mw.loader.using("mobile.categories.overlays").then((function(){return new(0,o.require("mobile.categories.overlays").CategoryTabs)({eventBus:e.eventBus,api:e.api,title:e.title})}))),t.$el.find(".overlay-content").append(r.$el),t}},"./src/mobile.startup/languageOverlay/getDeviceLanguage.js":function(e,t){e.exports=function(e){var t=e.languages?e.languages[0]:e.language||e.userLanguage||e.browserLanguage||e.systemLanguage;return t?t.toLowerCase():void 0}},"./src/mobile.startup/languageOverlay/languageOverlay.js":function(e,t,r){var s=r("./src/mobile.startup/moduleLoaderSingleton.js"),n=r("./src/mobile.startup/languageOverlay/getDeviceLanguage.js"),a=r("./src/mobile.startup/Overlay.js"),o=r("./src/mobile.startup/promisedView.js");function i(e){return mw.loader.using("mobile.languages.structured").then((function(){return e.getPageLanguages(mw.config.get("wgPageName"),mw.config.get("wgUserLanguage"))})).then((function(e){return new(s.require("mobile.languages.structured/LanguageSearcher"))({languages:e.languages,variants:e.variants,deviceLanguage:n(navigator)})}))}function c(e){return a.make({heading:mw.msg("mobile-frontend-language-heading"),className:"overlay language-overlay"},o(i(e)))}c.test={loadLanguageSearcher:i},e.exports=c},"./src/mobile.startup/loadingOverlay.js":function(e,t,r){var s=r("./src/mobile.startup/icons.js"),n=r("./src/mobile.startup/Overlay.js");e.exports=function(){var e=new n({className:"overlay overlay-loading",noHeader:!0});return s.spinner().$el.appendTo(e.$el.find(".overlay-content")),e}},"./src/mobile.startup/mediaViewer/overlay.js":function(e,t,r){var s=r("./src/mobile.startup/moduleLoaderSingleton.js"),n=r("./src/mobile.startup/promisedView.js"),a=r("./src/mobile.startup/util.js"),o=r("./src/mobile.startup/headers.js").header,i=r("./src/mobile.startup/icons.js"),c=r("./src/mobile.startup/Overlay.js");e.exports=function(e){return c.make({headers:[o("",[],i.cancel("gray"))],className:"overlay media-viewer"},n(a.Promise.all([mw.loader.using("mobile.mediaViewer")]).then((function(){return new(0,s.require("mobile.mediaViewer").ImageCarousel)(e)}))))}},"./src/mobile.startup/mobile.startup.js":function(e,t,r){var s=r("./src/mobile.startup/moduleLoaderSingleton.js"),n=r("./src/mobile.startup/util.js");e.exports={moduleLoader:s,mfExtend:r("./src/mobile.startup/mfExtend.js"),time:r("./src/mobile.startup/time.js"),util:n,headers:r("./src/mobile.startup/headers.js"),View:r("./src/mobile.startup/View.js"),PageGateway:r("./src/mobile.startup/PageGateway.js"),Browser:r("./src/mobile.startup/Browser.js"),Button:r("./src/mobile.startup/Button.js"),Icon:r("./src/mobile.startup/Icon.js"),ReferencesGateway:r("./src/mobile.startup/references/ReferencesGateway.js"),ReferencesHtmlScraperGateway:r("./src/mobile.startup/references/ReferencesHtmlScraperGateway.js"),icons:r("./src/mobile.startup/icons.js"),Page:r("./src/mobile.startup/Page.js"),currentPage:r("./src/mobile.startup/currentPage.js"),PageHTMLParser:r("./src/mobile.startup/PageHTMLParser.js"),currentPageHTMLParser:r("./src/mobile.startup/currentPageHTMLParser.js"),Anchor:r("./src/mobile.startup/Anchor.js"),Skin:r("./src/mobile.startup/Skin.js"),OverlayManager:r("./src/mobile.startup/OverlayManager.js"),Overlay:r("./src/mobile.startup/Overlay.js"),loadingOverlay:r("./src/mobile.startup/loadingOverlay.js"),Drawer:r("./src/mobile.startup/Drawer.js"),CtaDrawer:r("./src/mobile.startup/CtaDrawer.js"),showOnPageReload:r("./src/mobile.startup/showOnPageReload.js"),toast:r("./src/mobile.startup/showOnPageReload.js"),Watchstar:r("./src/mobile.startup/watchstar/watchstar.js"),categoryOverlay:r("./src/mobile.startup/categoryOverlay.js"),eventBusSingleton:r("./src/mobile.startup/eventBusSingleton.js"),promisedView:r("./src/mobile.startup/promisedView.js"),Toggler:r("./src/mobile.startup/Toggler.js"),toc:{TableOfContents:function(){return{$el:n.parseHTML("<div>")}}},references:r("./src/mobile.startup/references/references.js"),search:{SearchOverlay:r("./src/mobile.startup/search/SearchOverlay.js"),MobileWebSearchLogger:r("./src/mobile.startup/search/MobileWebSearchLogger.js"),SearchGateway:r("./src/mobile.startup/search/SearchGateway.js")},lazyImages:{lazyImageLoader:r("./src/mobile.startup/lazyImages/lazyImageLoader.js")},languageOverlay:r("./src/mobile.startup/languageOverlay/languageOverlay.js"),mediaViewer:{overlay:r("./src/mobile.startup/mediaViewer/overlay.js")},amcOutreach:r("./src/mobile.startup/amcOutreach/amcOutreach.js"),Section:r("./src/mobile.startup/Section.js")},mw.mobileFrontend=s,s.define("mobile.startup",e.exports)},"./src/mobile.startup/references/ReferencesGateway.js":function(e,t){function r(e){this.api=e}r.prototype.getReference=null,r.ERROR_NOT_EXIST="NOT_EXIST_ERROR",r.ERROR_OTHER="OTHER_ERROR",e.exports=r},"./src/mobile.startup/references/ReferencesHtmlScraperGateway.js":function(e,t,r){var s=r("./src/mobile.startup/references/ReferencesGateway.js"),n=r("./src/mobile.startup/mfExtend.js"),a=r("./src/mobile.startup/util.js");function o(){s.apply(this,arguments)}n(o,s,{EXTERNAL_LINK_CLASS:"external--reference",getReferenceFromContainer:function(e,t){var r,n,o,i=a.Deferred();return(r=t.find("#"+a.escapeSelector(e.substr(1)))).length?((n=r.closest("ol")).hasClass("mw-extended-references")&&(o=n.parent()),(o||r).find(".external").addClass(this.EXTERNAL_LINK_CLASS),i.resolve({text:this.getReferenceHtml(r),parentText:this.getReferenceHtml(o)})):i.reject(s.ERROR_NOT_EXIST),i.promise()},getReferenceHtml:function(e){return e?e.find(".mw-reference-text, .reference-text").first().html():""},getReference:function(e,t,r){return this.getReferenceFromContainer(decodeURIComponent(e),r.$el.find("ol.references"))}}),e.exports=o},"./src/mobile.startup/references/references.js":function(e,t,r){var s,n=r("./src/mobile.startup/Drawer.js"),a=r("./src/mobile.startup/util.js"),o=r("./src/mobile.startup/icons.js"),i=r("./src/mobile.startup/references/ReferencesGateway.js"),c=r("./src/mobile.startup/Icon.js");function l(e){return function(t){var r=t.target;if("A"===r.tagName)return e(r.getAttribute("href"),r.textContent),!1}}function u(e){var t=e.error?new c({name:"error",isSmall:!0}).$el:null;return new n(a.extend({showCollapseIcon:!1,className:"drawer position-fixed text references-drawer",events:{"click sup":e.onNestedReferenceClick&&l(e.onNestedReferenceClick)},children:[a.parseHTML("<div>").addClass("references-drawer__header").append([new c({isSmall:!0,name:"reference",modifier:""}).$el,a.parseHTML("<span>").addClass("references-drawer__title").text(mw.msg("mobile-frontend-references-citation")),o.cancel("gray",{isSmall:!0,modifier:"mw-ui-icon-element mw-ui-icon-flush-right"}).$el]),a.parseHTML("<div>").addClass("mw-parser-output").append([t,e.parentText?a.parseHTML("<div>").html(e.parentText):"",a.parseHTML("<sup>").text(e.title),e.text?a.parseHTML("<span>").html(" "+e.text):o.spinner().$el])]},e))}s={test:{makeOnNestedReferenceClickHandler:l},referenceDrawer:u,showReference:function(e,t,r,n,o,c,l){return o.getReference(e,t,n).then((function(e){var i=u(a.extend({title:r,text:e.text,parentText:e.parentText,onNestedReferenceClick:function(e,r){s.showReference(e,t,r,n,o).then((function(e){c.onShowNestedReference?l(i,e):(mw.log.warn("Please provide onShowNestedReferences parameter."),document.body.appendChild(e.$el[0]),i.hide(),e.show())}))}},c));return i}),(function(e){if(e!==i.ERROR_NOT_EXIST)return u({error:!0,title:r,text:mw.msg("mobile-frontend-references-citation-error")})}))}},e.exports=s},"./src/mobile.startup/search/MobileWebSearchLogger.js":function(e,t){function r(){this.userSessionToken=null,this.searchSessionToken=null}r.prototype={_newUserSession:function(){this.userSessionToken=mw.user.generateRandomSessionId()},_newSearchSession:function(){this.searchSessionToken=mw.user.generateRandomSessionId(),this.searchSessionCreatedAt=(new Date).getTime()},onSearchShow:function(){this._newUserSession()},onSearchStart:function(){this._newSearchSession(),mw.track("mf.schemaMobileWebSearch",{action:"session-start",userSessionToken:this.userSessionToken,searchSessionToken:this.searchSessionToken,timeOffsetSinceStart:0})},onSearchResults:function(e){var t=(new Date).getTime()-this.searchSessionCreatedAt;mw.track("mf.schemaMobileWebSearch",{action:"impression-results",resultSetType:"prefix",numberOfResults:e.results.length,userSessionToken:this.userSessionToken,searchSessionToken:this.searchSessionToken,timeToDisplayResults:t,timeOffsetSinceStart:t})},onSearchResultClick:function(e){var t=(new Date).getTime()-this.searchSessionCreatedAt;mw.track("mf.schemaMobileWebSearch",{action:"click-result",clickIndex:e.resultIndex+1,userSessionToken:this.userSessionToken,searchSessionToken:this.searchSessionToken,timeOffsetSinceStart:t})}},r.register=function(e){var t=new r;e.on("search-show",t.onSearchShow.bind(t)),e.on("search-start",t.onSearchStart.bind(t)),e.on("search-results",t.onSearchResults.bind(t)),e.on("search-result-click",t.onSearchResultClick.bind(t))},e.exports=r},"./src/mobile.startup/search/SearchHeaderView.js":function(e,t,r){function s(e){return(s="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function n(e,t){for(var r=0;r<t.length;r++){var s=t[r];s.enumerable=s.enumerable||!1,s.configurable=!0,"value"in s&&(s.writable=!0),Object.defineProperty(e,s.key,s)}}function a(e,t){return!t||"object"!==s(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function o(e){return(o=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function i(e,t){return(i=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var c=r("./src/mobile.startup/util.js"),l=r("./src/mobile.startup/View.js"),u=r("./src/mobile.startup/Icon.js"),p=function(e){"use strict";function t(e){return function(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}(this,t),a(this,o(t).call(this,c.extend({},e,{events:{"input input":"onInput"}})))}var r,s,l;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&i(e,t)}(t,e),r=t,(s=[{key:"onInput",value:function(e){var t=e.target.value;this.options.onInput(t),t?this.clearIcon.$el.show():this.clearIcon.$el.hide()}},{key:"postRender",value:function(){var e=new u({tagName:"button",name:"clear",isSmall:!0,label:mw.msg("mobile-frontend-clear-search"),additionalClassNames:"clear",events:{click:function(){return this.$el.find("input").val("").trigger("focus"),this.options.onInput(""),e.$el.hide(),!1}.bind(this)}});this.clearIcon=e,e.$el.hide(),this.$el.find("form").append(e.$el)}},{key:"isTemplateMode",get:function(){return!0}},{key:"template",get:function(){return c.template('<div class="overlay-title search-header-view">\n\t\t<form method="get" action="{{action}}" class="search-box">\n\t\t<input class="search" type="search" name="search" autocomplete="off" placeholder="{{placeholderMsg}}" aria-label="{{placeholderMsg}}" value="{{searchTerm}}">\n\t\t</form>\n</div>')}}])&&n(r.prototype,s),l&&n(r,l),t}(l);e.exports=p},"./src/mobile.startup/search/SearchOverlay.js":function(e,t,r){var s=r("./src/mobile.startup/mfExtend.js"),n=r("./src/mobile.startup/Overlay.js"),a=r("./src/mobile.startup/util.js"),o=r("./src/mobile.startup/search/searchHeader.js"),i=r("./src/mobile.startup/search/SearchResultsView.js"),c=r("./src/mobile.startup/watchstar/WatchstarPageList.js");function l(e){var t=o(e.placeholderMsg,e.action||mw.config.get("wgScript"),function(e){this.performSearch(e)}.bind(this)),r=a.extend(!0,{headerChrome:!0,isBorderBox:!1,className:"overlay search-overlay",headers:[t],events:{"click .search-content":"onClickSearchContent","click .overlay-content":"onClickOverlayContent","click .overlay-content > div":function(e){e.stopPropagation()},"touchstart .results":"hideKeyboardOnScroll","mousedown .results":"hideKeyboardOnScroll","click .results a":"onClickResult"}},e);this.header=t,n.call(this,r),this.api=r.api,this.gateway=r.gateway||new r.gatewayClass(this.api),this.router=r.router}s(l,n,{onClickSearchContent:function(){var e=a.getDocument().find("body"),t=this.$el.find("form");this.parseHTML("<input>").attr({type:"hidden",name:"fulltext",value:"search"}).appendTo(t),setTimeout((function(){t.appendTo(e),t.trigger("submit")}),0)},onClickOverlayContent:function(){this.$el.find(".cancel").trigger("click")},hideKeyboardOnScroll:function(){this.$input.trigger("blur")},onClickResult:function(e){var t=this.$el.find(e.currentTarget),r=t.closest("li");this.emit("search-result-click",{result:r,resultIndex:this.$results.index(r),originalEvent:e}),e.preventDefault(),this.router.back().then((function(){window.location.href=t.attr("href")}))},postRender:function(){var e,t=this,r=new i({searchContentLabel:mw.msg("mobile-frontend-search-content"),noResultsMsg:mw.msg("mobile-frontend-search-no-results"),searchContentNoResultsMsg:mw.message("mobile-frontend-search-content-no-results").parse()});function s(){t.$spinner.hide(),clearTimeout(e)}this.$el.find(".overlay-content").append(r.$el),n.prototype.postRender.call(this),this.$input=this.$el.find(this.header).find("input"),this.$searchContent=r.$el.hide(),this.$resultContainer=r.$el.find(".results-list-container"),this.$spinner=r.$el.find(".spinner-container"),this.on("search-start",(function(r){e&&s(),e=setTimeout((function(){t.$spinner.show()}),2e3-r.delay)})),this.on("search-results",s)},showKeyboard:function(){var e=this.$input.val().length;this.$input.trigger("focus"),this.$input[0].setSelectionRange&&this.$input[0].setSelectionRange(e,e)},show:function(){n.prototype.show.apply(this,arguments),this.showKeyboard(),this.emit("search-show")},performSearch:function(e){var t=this,r=this.api,s=this.gateway.isCached(e)?0:300;e!==this.lastQuery&&(t._pendingQuery&&t._pendingQuery.abort(),clearTimeout(this.timer),e.length?this.timer=setTimeout((function(){var n;t.emit("search-start",{query:e,delay:s}),n=t.gateway.search(e),t._pendingQuery=n.then((function(e){e&&e.query===t.$input.val()&&(t.$el.toggleClass("no-results",0===e.results.length),t.$searchContent.show().find("p").hide().filter(e.results.length?".with-results":".without-results").show(),new c({api:r,funnel:"search",pages:e.results,el:t.$resultContainer}),t.$results=t.$resultContainer.find("li"),t.emit("search-results",{results:e.results}))})).promise({abort:function(){n.abort()}})}),s):t.resetSearch(),this.lastQuery=e)},resetSearch:function(){this.$el.find(".overlay-content").children().hide()}}),e.exports=l},"./src/mobile.startup/search/SearchResultsView.js":function(e,t,r){function s(e){return(s="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function n(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function a(e,t){for(var r=0;r<t.length;r++){var s=t[r];s.enumerable=s.enumerable||!1,s.configurable=!0,"value"in s&&(s.writable=!0),Object.defineProperty(e,s.key,s)}}function o(e,t){return!t||"object"!==s(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function i(e,t,r){return(i="undefined"!=typeof Reflect&&Reflect.get?Reflect.get:function(e,t,r){var s=function(e,t){for(;!Object.prototype.hasOwnProperty.call(e,t)&&null!==(e=c(e)););return e}(e,t);if(s){var n=Object.getOwnPropertyDescriptor(s,t);return n.get?n.get.call(r):n.value}})(e,t,r||e)}function c(e){return(c=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function l(e,t){return(l=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var u=r("./src/mobile.startup/View.js"),p=r("./src/mobile.startup/Icon.js"),h=r("./src/mobile.startup/Anchor.js"),m=r("./src/mobile.startup/icons.js").spinner().$el,f=r("./src/mobile.startup/util.js"),d=function(e){"use strict";function t(){return n(this,t),o(this,c(t).apply(this,arguments))}var r,s,u;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&l(e,t)}(t,e),r=t,(s=[{key:"preRender",value:function(){mw.config.get("wgCirrusSearchFeedbackLink")&&(this.options.feedback={prompt:mw.msg("mobile-frontend-search-feedback-prompt")})}},{key:"postRender",value:function(e){var r=mw.config.get("wgCirrusSearchFeedbackLink");i(c(t.prototype),"postRender",this).call(this,e),this.$el.find(".search-content li").append(new p({tagName:"a",href:"#",name:"articlesSearch",additionalClassNames:"mw-ui-icon-flush-left",label:mw.msg("mobile-frontend-search-content")}).$el),this.$el.find(".spinner-container").append(m),r&&this.$el.find(".search-feedback").append(new h({label:mw.msg("mobile-frontend-search-feedback-link-text"),href:r}).$el)}},{key:"isTemplateMode",get:function(){return!0}},{key:"template",get:function(){return f.template('\n<div class="search-results-view">\n\t<div class="search-content overlay-header">\n\t\t<ul>\n\t\t\t<li>{{! search content icon goes here }}</li>\n\t\t</ul>\n\t\t<div class="caption">\n\t\t\t<p class="with-results">{{searchContentLabel}}</p>\n\t\t\t<p class="without-results">{{noResultsMsg}}</p>\n\t\t\t<p class="without-results">{{{searchContentNoResultsMsg}}}</p>\n\t\t</div>\n\t</div>\n\t<div class="spinner-container position-fixed"></div>\n\t<div class="results">\n\t\t<div class="results-list-container"></div>\n\t\t{{#feedback}}\n\t\t\t<div class="search-feedback">\n\t\t\t\t{{prompt}}\n\t\t\t</div>\n\t\t{{/feedback}}\n\t</div>\n</div>')}}])&&a(r.prototype,s),u&&a(r,u),t}(u);e.exports=d},"./src/mobile.startup/search/searchHeader.js":function(e,t,r){var s=r("./src/mobile.startup/headers.js").formHeader,n=r("./src/mobile.startup/search/SearchHeaderView.js"),a=r("./src/mobile.startup/icons.js");e.exports=function(e,t,r){return s(new n({placeholderMsg:e,action:t,onInput:r}),[a.cancel()],!1)}}},[["./src/mobile.startup/mobile.startup.js",0,1]]]);
//# sourceMappingURL=mobile.startup.js.map.json