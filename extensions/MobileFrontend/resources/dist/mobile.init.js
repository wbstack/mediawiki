this.mfModules=this.mfModules||{},this.mfModules["mobile.init"]=(window.webpackJsonp=window.webpackJsonp||[]).push([[5],{"./src/mobile.init/editor.js":function(e,t,i){var n=i("./src/mobile.startup/moduleLoaderSingleton.js"),o=i("./src/mobile.startup/util.js"),a=i("./src/mobile.init/editorLoadingOverlay.js"),r=i("./src/mobile.startup/OverlayManager.js"),s=$("#ca-edit, .mw-editsection a, .edit-link"),c=mw.user,m=i("./src/mobile.startup/CtaDrawer.js"),l=/MSIE \d\./.test(navigator.userAgent),g=mw.config.get("wgPageContentModel"),d=mw.config.get("wgVisualEditorConfig"),u=mw.config.get("wgUserEditCount"),w=/^\/editor\/(\d+|T-\d+|all)$/;function f(e,t,i,m){var l,g,f,p=r.getSingleton(),v=0===e.id,h=!1;s.on("click",(function(e){!function(e,t,i){var n;n=1===s.length?"all":mw.util.getParamValue("section",e.href)||"all",mw.config.get("wgPageName")===mw.util.getParamValue("title",e.href)&&(i.navigate("#/editor/"+n),t.preventDefault())}(this,e,p.router)})),p.add(w,(function(r){var s,m,l,g,w=window.pageYOffset,b=$("#mw-content-text"),S={overlayManager:p,currentPageHTMLParser:i,fakeScroll:0,api:new mw.Api,licenseMsg:t.getLicenseMsg(),title:e.title,titleObj:e.titleObj,isAnon:c.isAnon(),isNewPage:v,editCount:u,oldId:mw.util.getParamValue("oldid"),contentLang:b.attr("lang"),contentDir:b.attr("dir"),sessionId:mw.config.get("wgWMESchemaEditAttemptStepSessionId")||mw.Uri().query.editingStatsId||c.generateRandomSessionId()},E=mw.util.getParamValue("redlink")?"new":"click";function y(e){h&&(S.sessionId=c.generateRandomSessionId()),mw.track("mf.schemaEditAttemptStep",{action:"init",type:"section",mechanism:E,editor_interface:e,editing_session_id:S.sessionId}),h=!0}function M(){var t=!!d,i=function(){var e,t,i=mw.user.options.get("mobile-editor")||mw.storage.get("preferredEditor");if(i)return i;switch(e=mw.config.get("wgMFDefaultEditor"),(t=mw.storage.getObject("MFDefaultEditorABToken"))&&t.expires<Date.now()&&(mw.storage.remove("MFDefaultEditorABToken"),t=null),"abtest"===e&&(mw.user.isAnon()?(t||(t={token:mw.user.generateRandomSessionId(),expires:Date.now()+7776e6},mw.storage.setObject("MFDefaultEditorABToken",t)),mw.config.set("wgMFSchemaEditAttemptStepAnonymousUserId",t.token),e=parseInt(t.token.slice(0,8),16)%2==0?"source":"visual",mw.config.set("wgMFSchemaEditAttemptStepBucket","default-"+e)):mw.config.get("wgUserEditCount")<=100?(e=mw.user.getId()%2==0?"source":"visual",mw.config.set("wgMFSchemaEditAttemptStepBucket","default-"+e)):e="preference"),e){case"source":return"SourceEditor";case"visual":return"VisualEditor";case"preference":return(mw.user.options.get("visualeditor-hidebetawelcome")||mw.user.options.get("visualeditor-hideusered"))&&"visualeditor"===mw.user.options.get("visualeditor-editor")?"VisualEditor":"SourceEditor"}return"SourceEditor"}(),n=d&&d.namespaces||[];return t&&e.isWikiText()&&-1!==n.indexOf(mw.config.get("wgNamespaceNumber"))&&"translation"!==mw.config.get("wgTranslatePageTranslation")&&("VisualEditor"===i||"VisualEditor"===f)&&"SourceEditor"!==f}function k(){return y("wikitext"),mw.hook("mobileFrontend.editorOpening").fire(),mw.loader.using("mobile.editor.overlay").then((function(){return new(n.require("mobile.editor.overlay/SourceEditorOverlay"))(S)}))}return"all"!==r&&(S.sectionId=e.isWikiText()?r:void 0),s=o.Deferred(),l=a((function(){var e,t,i,n,o;$(document.body).addClass("ve-loading"),e=$("#mw-mf-page-center"),t=$("#content"),"0"===r||"all"===r?i=$("#bodyContent"):(i=$('[data-section="'+r+'"]').closest("h1, h2, h3, h4, h5, h6")).length||(i=$("#bodyContent")),e.prop("scrollTop",w),n=i[0].getBoundingClientRect().top,n-=48,M()?(o=!0===d.enableVisualSectionEditing||"mobile"===d.enableVisualSectionEditing,("0"===r||"all"===r||o)&&(n-=16)):"0"!==r&&"all"!==r||(n-=16),t.css({transform:"translate( 0, "+-n+"px )","padding-bottom":"+="+n,"margin-bottom":"-="+n}),S.fakeScroll=n,setTimeout(s.resolve,500)}),(function(){m&&m.abort&&m.abort(),$("#content").css({transform:"","padding-bottom":"","margin-bottom":""}),$(document.body).removeClass("ve-loading")})),M()?(y("visualeditor"),mw.hook("mobileFrontend.editorOpening").fire(),S.mode="visual",S.dataPromise=mw.loader.using("ext.visualEditor.targetLoader").then((function(){return m=mw.libs.ve.targetLoader.requestPageData(S.mode,S.titleObj.getPrefixedDb(),{sessionStore:!0,section:void 0===S.sectionId?null:S.sectionId,oldId:S.oldId||void 0,targetName:"mobile"})})),g=mw.loader.using("ext.visualEditor.targetLoader").then((function(){return mw.libs.ve.targetLoader.addPlugin("mobile.editor.ve"),mw.libs.ve.targetLoader.loadModules(S.mode)})).then((function(){var e=n.require("mobile.editor.overlay/VisualEditorOverlay"),t=n.require("mobile.editor.overlay/SourceEditorOverlay");return S.SourceEditorOverlay=t,new e(S)}),(function(){return k()}))):g=k(),o.Promise.all([g,s]).then((function(e){e.getLoadingPromise().then((function(){var t=p.stack[0];t&&t.overlay===l&&p.replaceCurrent(e)}),(function(e,t){p.router.back(),e.show?(document.body.appendChild(e.$el[0]),e.show()):t?mw.notify(S.api.getErrorMessage(t)):mw.notify(mw.msg("mobile-frontend-editor-error-loading"))}))})),l})),$("#ca-edit a").prop("href",(function(e,t){try{var i=new mw.Uri(t);return i.query.section="0",i.toString()}catch(e){return t}})),m.getPath()||!mw.util.getParamValue("veaction")&&"edit"!==mw.util.getParamValue("action")||("edit"===mw.util.getParamValue("veaction")?f="VisualEditor":"editsource"===mw.util.getParamValue("veaction")&&(f="SourceEditor"),g="#/editor/"+(mw.util.getParamValue("section")||"edit"===mw.util.getParamValue("action")&&"all"||"0"),window.history&&history.pushState?(delete(l=mw.Uri()).query.action,delete l.query.veaction,delete l.query.section,history.replaceState(null,document.title,l.toString()+g)):m.navigate(g))}function p(e,t,i,n){var o,a;if(!(o=mw.config.get("wgMinervaReadOnly"))&&mw.config.get("wgIsProbablyEditable"))f(e,i,t,n);else if(function(e){e.$el.find(".mw-editsection").hide()}(t),a=mw.config.get("wgRestrictionEdit"),mw.user.isAnon()&&Array.isArray(a)&&-1!==a.indexOf("*"))!function(e){var t;function i(){t||(t=new m({content:mw.msg("mobile-frontend-editor-disabled-anon"),signupQueryParams:{warning:"mobile-frontend-watchlist-signup-action"}})),t.show()}s.on("click",(function(e){i(),e.preventDefault()})),e.route(w,(function(){i()})),e.checkRoute()}(n);else{var r=$("<a>").attr("href","/wiki/"+mw.config.get("wgPageName")+"?action=edit");v(o?mw.msg("apierror-readonly"):mw.message("mobile-frontend-editor-disabled",r).parseDom(),n)}}function v(e,t){s.on("click",(function(t){mw.notify(e),t.preventDefault()})),t.route(w,(function(){mw.notify(e)})),t.checkRoute()}e.exports=function(e,t,i){var n=0===e.id,o=mw.loader.require("mediawiki.router"),a=o.isSupported()&&!l;"wikitext"===g&&(mw.util.getParamValue("undo")||a&&(e.inNamespace("file")&&n?v(mw.msg("mobile-frontend-editor-uploadenable"),o):p(e,t,i,o)))}},"./src/mobile.init/editorLoadingOverlay.js":function(e,t,i){var n=i("./src/mobile.init/fakeToolbar.js"),o=i("./src/mobile.startup/Overlay.js");e.exports=function(e,t){var i=n(),a=new o({className:"overlay overlay-loading",noHeader:!0,isBorderBox:!1,onBeforeExit:function(e){e(),t()}});return a.show=function(){o.prototype.show.call(this),e()},i.appendTo(a.$el.find(".overlay-content")),i.addClass("toolbar-hidden"),setTimeout((function(){i.addClass("toolbar-shown"),setTimeout((function(){i.addClass("toolbar-shown-done")}),250)})),a}},"./src/mobile.init/eventLogging/schemaEditAttemptStep.js":function(e,t){e.exports=function(){var e,t,i,n,o,a,r=!!mw.util.getParamValue("trackdebug");mw.config.exists("wgWMESchemaEditAttemptStepSamplingRate")&&(e=mw.eventLog.Schema,t=mw.user,i=mw.config.get("wgWMESchemaEditAttemptStepSamplingRate"),n={firstChange:"first_change",saveIntent:"save_intent",saveAttempt:"save_attempt",saveSuccess:"save_success",saveFailure:"save_failure"},o={},a=new e("EditAttemptStep",i,{page_id:mw.config.get("wgArticleId"),revision_id:mw.config.get("wgRevisionId"),page_title:mw.config.get("wgPageName"),page_ns:mw.config.get("wgNamespaceNumber"),user_id:t.getId(),user_class:t.isAnon()?"IP":void 0,user_editcount:mw.config.get("wgUserEditCount",0),mw_version:mw.config.get("wgVersion"),platform:"phone",integration:"page",page_token:t.getPageviewToken(),session_token:t.sessionId(),version:1}),mw.trackSubscribe("mf.schemaEditAttemptStep",(function(e,t){var s=n[t.action]||t.action,c=mw.now(),m=0;if(mw.storage.get("preferredEditor")||(mw.config.get("wgMFSchemaEditAttemptStepAnonymousUserId")&&(t.anonymous_user_token=mw.config.get("wgMFSchemaEditAttemptStepAnonymousUserId")),mw.config.get("wgMFSchemaEditAttemptStepBucket")&&(t.bucket=mw.config.get("wgMFSchemaEditAttemptStepBucket"))),"init"!==t.action&&"abort"!==t.action&&"saveFailure"!==t.action||(t[s+"_type"]=t.type),"init"!==t.action&&"abort"!==t.action||(t[s+"_mechanism"]=t.mechanism),"init"!==t.action&&(m=Math.round(function(e,t,i){if(void 0!==t.timing)return t.timing;switch(e){case"ready":case"loaded":return i-o.init;case"firstChange":case"saveIntent":return i-o.ready;case"saveAttempt":return i-o.saveIntent;case"saveSuccess":case"saveFailure":return mw.log.warn("mf.schemaEditAttemptStep: Do not rely on default timing value for saveSuccess/saveFailure"),-1;case"abort":switch(t.abort_type){case"preinit":return i-o.init;case"nochange":case"switchwith":case"switchwithout":case"switchnochange":case"abandon":return i-o.ready;case"abandonMidsave":return i-o.saveAttempt}return mw.log.warn("mf.schemaEditAttemptStep: Unrecognized abort type",t.type),-1}return mw.log.warn("mf.schemaEditAttemptStep: Unrecognized action",e),-1}(t.action,t,c)),t[s+"_timing"]=m),"saveFailure"===t.action&&(t[s+"_message"]=t.message),delete t.type,delete t.mechanism,delete t.timing,delete t.message,t.is_oversample=!mw.eventLog.inSample(1/i),"abort"===t.action&&"switchnochange"!==t.abort_type?o={}:o[t.action]=c,"switchnochange"!==t.abort_type){if(o.abort){if("ready"===t.action)return;if("loaded"===t.action)return void delete o.abort}r?function(){console.log.apply(console,arguments)}(e+"."+t.action,m+"ms",t,a.defaults):a.log(t,mw.config.get("wgWMESchemaEditAttemptStepOversample")||"all"===mw.config.get("wgMFSchemaEditAttemptStepOversample")||t.editor_interface===mw.config.get("wgMFSchemaEditAttemptStepOversample")?1:i)}})))}},"./src/mobile.init/eventLogging/schemaMobileWebSearch.js":function(e,t){e.exports=function(){var e;e=new(0,mw.eventLog.Schema)("MobileWebSearch",mw.config.get("wgMFSchemaSearchSampleRate",.001),{platform:"mobileweb",platformVersion:mw.config.get("wgMFMode")}),mw.trackSubscribe("mf.schemaMobileWebSearch",(function(t,i){e.log(i)}))}},"./src/mobile.init/eventLogging/schemaVisualEditorFeatureUse.js":function(e,t){e.exports=function(){var e,t,i,n,o=!!mw.util.getParamValue("trackdebug");mw.config.exists("wgWMESchemaEditAttemptStepSamplingRate")&&(e=mw.eventLog.Schema,t=mw.user,i=mw.config.get("wgWMESchemaEditAttemptStepSamplingRate"),n=new e("VisualEditorFeatureUse",i,{user_id:t.getId(),user_editcount:mw.config.get("wgUserEditCount",0),platform:"phone",integration:"page"}),mw.trackSubscribe("mf.schemaVisualEditorFeatureUse",(function(e,t){var a={feature:t.feature,action:t.action,editingSessionId:t.editing_session_id,editor_interface:t.editor_interface};o?function(){console.log.apply(console,arguments)}(e,a,n.defaults):n.log(a,mw.config.get("wgWMESchemaEditAttemptStepOversample")||"visualeditor"===mw.config.get("wgMFSchemaEditAttemptStepOversample")||"all"===mw.config.get("wgMFSchemaEditAttemptStepOversample")?1:i)})))}},"./src/mobile.init/lazyLoadedImages.js":function(e,t,i){var n=i("./src/mobile.startup/lazyImages/lazyImageLoader.js");e.exports=function(){if(mw.config.get("wgMFLazyLoadImages")){var e=n.queryPlaceholders(document.getElementById("mw-content-text"));if("IntersectionObserver"in window){var t=new IntersectionObserver((function(e){e.forEach((function(e){var i=e.target;e.isIntersecting&&(n.loadImage(i),t.unobserve(i))}))}),{rootMargin:"0px 0px 50% 0px",threshold:0});e.forEach((function(e){t.observe(e)}))}else $(e).addClass("".concat(n.placeholderClass,"--tap")),document.addEventListener("click",(function(t){e.indexOf(t.target)>-1&&n.loadImage(t.target)}))}}},"./src/mobile.init/mobile.init.js":function(e,t,i){var n,o=mw.storage,a=i("./src/mobile.init/toggling.js"),r=i("./src/mobile.init/lazyLoadedImages.js"),s=mw.config.get("skin"),c=mw.config.get("wgMFIsPageContentModelEditable"),m=i("./src/mobile.init/editor.js"),l=i("./src/mobile.startup/currentPage.js")(),g=i("./src/mobile.startup/currentPageHTMLParser.js")(),d=i("./src/mobile.startup/util.js"),u=d.getWindow(),w=d.getDocument(),f=i("./src/mobile.startup/Skin.js"),p=i("./src/mobile.startup/eventBusSingleton.js"),v=i("./src/mobile.init/eventLogging/schemaMobileWebSearch.js"),h=i("./src/mobile.init/eventLogging/schemaEditAttemptStep.js"),b=i("./src/mobile.init/eventLogging/schemaVisualEditorFeatureUse.js");function S(e,t){return function(){e.apply(this,arguments),t.apply(this,arguments)}}function E(){var e=o.get("userFontSize","regular");w.addClass("mf-font-size-"+e)}n=f.getSingleton(),u.on("resize",S(mw.util.debounce(100,(function(){p.emit("resize")})),$.throttle(200,(function(){p.emit("resize:throttled")})))).on("scroll",S(mw.util.debounce(100,(function(){p.emit("scroll")})),$.throttle(200,(function(){p.emit("scroll:throttled")})))),u.on("pageshow",(function(){E()})),E(),window.console&&window.console.log&&window.console.log.apply&&mw.config.get("wgMFEnableJSConsoleRecruitment")&&console.log(mw.msg("mobile-frontend-console-recruit")),!l.inNamespace("special")&&c&&"minerva"===s&&null!==mw.config.get("wgMFMode")&&m(l,g,n),a(),r(),mw.loader.using("ext.eventLogging").then((function(){v(),h(),b()}))},"./src/mobile.init/toggling.js":function(e,t,i){e.exports=function(){var e,t,n,o=$("#mw-content-text > .mw-parser-output"),a=i("./src/mobile.startup/currentPage.js")(),r=i("./src/mobile.startup/Toggler.js"),s=i("./src/mobile.startup/eventBusSingleton.js");0===o.length&&(o=$("#mw-content-text")),a.inNamespace("special")||"view"!==mw.config.get("wgAction")||(t="content-",n=a,(e=o).find("> h1,> h2,> h3,> h4,> h5,> h6").addClass("section-heading").removeAttr("onclick"),void 0!==window.mfTempOpenSection&&delete window.mfTempOpenSection,new r({$container:e,prefix:t,page:n,eventBus:s}))}}},[["./src/mobile.init/mobile.init.js",0,1]]]);
//# sourceMappingURL=mobile.init.js.map.json