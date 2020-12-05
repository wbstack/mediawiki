this.mfModules=this.mfModules||{},this.mfModules["mobile.editor.overlay"]=(window.webpackJsonp=window.webpackJsonp||[]).push([[3],{"./src/mobile.editor.overlay/BlockMessageDetails.js":function(e,t,i){"use strict";function o(e){return(o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e})(e)}function n(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}function r(e,t){for(var i=0;i<t.length;i++){var o=t[i];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(e,o.key,o)}}function s(e,t){return!t||"object"!==o(t)&&"function"!=typeof t?function(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}(e):t}function a(e){return(a=Object.setPrototypeOf?Object.getPrototypeOf:function(e){return e.__proto__||Object.getPrototypeOf(e)})(e)}function d(e,t){return(d=Object.setPrototypeOf||function(e,t){return e.__proto__=t,e})(e,t)}var l=i("./src/mobile.startup/Button.js"),c=i("./src/mobile.startup/View.js"),h=i("./src/mobile.startup/Icon.js"),m=new l({label:mw.msg("ok"),tagName:"button",progressive:!0,additionalClassNames:"cancel"}),u=i("./src/mobile.startup/util.js"),g=function(e){function t(){return n(this,t),s(this,a(t).apply(this,arguments))}var i,o,l;return function(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function");e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,writable:!0,configurable:!0}}),t&&d(e,t)}(t,e),i=t,(o=[{key:"postRender",value:function(){var e=new h({tagName:"span",name:"userAvatar",hasText:!0,label:this.options.creator.name});this.$el.find(".block-message-creator a").prepend(e.$el),this.$el.find(".block-message-buttons").prepend(m.$el),this.$el.find(".block-message-icon").prepend(new h({name:"stopHand-destructive",additionalClassNames:"mw-ui-icon-flush-top"}).$el)}},{key:"isTemplateMode",get:function(){return!0}},{key:"defaults",get:function(){return{createDetailsAnchorHref:function(){return function(e,t){return mw.util.getUrl("Special:BlockList",{wpTarget:"#"+t(e)})}},createDetailsAnchorLabel:function(){return mw.msg("mobile-frontend-editor-blocked-drawer-help")},createTitle:function(){return this.partial?mw.msg("mobile-frontend-editor-blocked-drawer-title-partial"):mw.msg("mobile-frontend-editor-blocked-drawer-title")},reasonHeader:mw.msg("mobile-frontend-editor-blocked-drawer-reason-header"),creatorHeader:function(){return mw.msg("mobile-frontend-editor-blocked-drawer-creator-header",mw.user.options.get("gender"))},expiryHeader:mw.msg("mobile-frontend-editor-blocked-drawer-expiry-header")}}},{key:"template",get:function(){return u.template('\n<div class="block-message">\n  <div class="block-message-icon"></div>\n  <div class="block-message-info">\n    <div class="block-message-item block-message-title">\n      <h5>{{ createTitle }}</h5>\n    </div>\n    <div class="block-message-data">\n      {{#reason}}\n        <div class="block-message-item">\n          <h6>{{ reasonHeader }}</h6>\n          <div><strong>{{{ reason }}}</strong></div>\n        </div>\n      {{/reason}}\n      <div class="block-message-item block-message-creator">\n        {{#creator.name}}\n          <h6>{{ creatorHeader }}</h6>\n          <div>\n            <strong>\n              {{#creator.url}}\n                <a href="{{ creator.url }}"></a>\n              {{/creator.url}}\n              {{^creator.url}}\n                {{ creator.name }}\n              {{/creator.url}}\n            </strong>\n          </div>\n        {{/creator.name}}\n      </div>\n      {{#expiry}}\n        <div class="block-message-item">\n          <h6>{{ expiryHeader }}</h6>\n          <div><strong>{{#duration}}{{ duration }}{{/duration}} {{ expiry }}</strong></div>\n        </div>\n      {{/expiry}}\n    </div>\n    <div class="block-message-item block-message-buttons">\n      {{#blockId}}\n        <a href="{{#createDetailsAnchorHref}}{{ blockId }}{{/createDetailsAnchorHref}}">\n          {{ createDetailsAnchorLabel }}\n        </a>\n      {{/blockId}}\n    </div>\n  </div>')}}])&&r(i.prototype,o),l&&r(i,l),t}(c);e.exports=g},"./src/mobile.editor.overlay/EditorGateway.js":function(e,t,i){var o=i("./src/mobile.startup/util.js"),n=i("./src/mobile.startup/actionParams.js");function r(e){this.api=e.api,this.title=e.title,this.sectionId=e.sectionId,this.oldId=e.oldId,this.content=e.isNewPage?"":void 0,this.fromModified=e.fromModified,this.hasChanged=e.fromModified}r.prototype={getBlockInfo:function(e){var t;return e.actions&&e.actions.edit&&Array.isArray(e.actions.edit)&&(e.actions.edit.some((function(e){return-1!==["blocked","autoblocked"].indexOf(e.code)&&(t=e,!0)})),t&&t.data&&t.data.blockinfo)?t.data.blockinfo:null},getContent:function(){var e,t=this;function i(){return o.Deferred().resolve({text:t.content||"",blockinfo:t.blockinfo})}return void 0!==this.content?i():(e=n({prop:["revisions","info"],rvprop:["content","timestamp"],titles:t.title,intestactions:"edit",intestactionsdetail:"full"}),this.oldId&&(e.rvstartid=this.oldId),this.sectionId&&(e.rvsection=this.sectionId),this.api.get(e).then((function(e){var n,r;return e.error?o.Deferred().reject(e.error.code):(void 0!==(r=e.query.pages[0]).missing?t.content="":(n=r.revisions[0],t.content=n.content,t.timestamp=n.timestamp),t.originalContent=t.content,t.blockinfo=t.getBlockInfo(r),i())})))},setContent:function(e){this.originalContent!==e||this.fromModified?this.hasChanged=!0:this.hasChanged=!1,this.content=e},setPrependText:function(e){this.prependtext=e,this.hasChanged=!0},save:function(e){var t,i=this,n=o.Deferred();return e=e||{},t={action:"edit",errorformat:"html",errorlang:mw.config.get("wgUserLanguage"),errorsuselocal:1,formatversion:2,title:i.title,summary:e.summary,captchaid:e.captchaId,captchaword:e.captchaWord,basetimestamp:i.timestamp,starttimestamp:i.timestamp},void 0!==i.content?t.text=i.content:i.prependtext&&(t.prependtext=i.prependtext),i.sectionId&&(t.section=i.sectionId),i.api.postWithToken("csrf",t).then((function(e){e&&e.edit&&"Success"===e.edit.result?(i.hasChanged=!1,n.resolve(e.edit.newrevid)):n.reject(e)}),(function(e,t){n.reject(t)})),n},abortPreview:function(){this._pending&&this._pending.abort()},getPreview:function(e){var t="",i="",n=this;return o.extend(e,{action:"parse",sectionpreview:!0,disableeditsection:!0,pst:!0,mobileformat:!0,title:this.title,prop:["text","sections"]}),this.abortPreview(),this._pending=this.api.post(e),this._pending.then((function(e){return e&&e.parse&&e.parse.text?("0"!==n.sectionId&&void 0!==e.parse.sections&&void 0!==e.parse.sections[0]&&(void 0!==e.parse.sections[0].anchor&&(i=e.parse.sections[0].anchor),void 0!==e.parse.sections[0].line&&(t=e.parse.sections[0].line)),{text:e.parse.text["*"],id:i,line:t}):o.Deferred().reject()})).promise({abort:function(){n._pending.abort()}})}},e.exports=r},"./src/mobile.editor.overlay/EditorOverlayBase.js":function(e,t,i){var o=i("./src/mobile.startup/Overlay.js"),n=i("./src/mobile.startup/util.js"),r=i("./src/mobile.editor.overlay/parseBlockInfo.js"),s=i("./src/mobile.startup/headers.js"),a=i("./src/mobile.startup/PageGateway.js"),d=i("./src/mobile.startup/icons.js"),l=i("./src/mobile.startup/Button.js"),c=i("./src/mobile.startup/Icon.js"),h=i("./src/mobile.startup/showOnPageReload.js"),m=i("./src/mobile.editor.overlay/saveFailureMessage.js"),u=i("./src/mobile.startup/mfExtend.js"),g=i("./src/mobile.editor.overlay/blockMessageDrawer.js"),p=i("./src/mobile.startup/MessageBox.js"),f=mw.user;function w(e,t){(t=t||{}).classes=["visual-editor"],w.super.call(this,e,t)}function v(e){var t=n.extend(!0,{onBeforeExit:this.onBeforeExit.bind(this),className:"overlay editor-overlay",isBorderBox:!1},e,{events:n.extend({"click .back":"onClickBack","click .continue":"onClickContinue","click .submit":"onClickSubmit","click .anonymous":"onClickAnonymous"},e.events)});t.isNewPage&&(t.placeholder=mw.msg("mobile-frontend-editor-placeholder-new-page",f)),0!==mw.config.get("wgNamespaceNumber")&&(t.summaryRequestMsg=mw.msg("mobile-frontend-editor-summary")),this.pageGateway=new a(t.api),this.editCount=t.editCount,this.isNewPage=t.isNewPage,this.isNewEditor=0===t.editCount,this.sectionId=t.sectionId,this.sessionId=t.sessionId,this.overlayManager=t.overlayManager,o.call(this,t)}OO.inheritClass(w,OO.ui.Tool),w.static.name="editVe",w.static.icon="edit",w.static.group="editorSwitcher",w.static.title=mw.msg("mobile-frontend-editor-switch-visual-editor"),w.prototype.onSelect=function(){},w.prototype.onUpdateState=function(){},u(v,o,{defaults:n.extend({},o.prototype.defaults,{hasToolbar:!1,continueMsg:mw.msg("mobile-frontend-editor-continue"),closeMsg:mw.msg("mobile-frontend-editor-keep-editing"),summaryRequestMsg:mw.msg("mobile-frontend-editor-summary-request"),summaryMsg:mw.msg("mobile-frontend-editor-summary-placeholder"),placeholder:mw.msg("mobile-frontend-editor-placeholder"),captchaMsg:mw.msg("mobile-frontend-account-create-captcha-placeholder"),captchaTryAgainMsg:mw.msg("mobile-frontend-editor-captcha-try-again"),switchMsg:mw.msg("mobile-frontend-editor-switch-editor"),confirmMsg:mw.msg("mobile-frontend-editor-cancel-confirm"),licenseMsg:void 0}),template:n.template('\n<div class="overlay-header-container header-container position-fixed"></div>\n\n<div class="overlay-content">\n\t<div class="panels">\n\t\t<div class="save-panel panel hideable hidden">\n\t\t\t<div id="error-notice-container"></div>\n\t\t\t<h2 class="summary-request">{{{summaryRequestMsg}}}</h2>\n\t\t\t<textarea rows="2" class="mw-ui-input summary" placeholder="{{summaryMsg}}"></textarea>\n\t\t\t{{#licenseMsg}}<div class="license">{{{licenseMsg}}}</div>{{/licenseMsg}}\n\t\t</div>\n\t\t<div class="captcha-panel panel hideable hidden">\n\t\t\t<div class="captcha-box">\n\t\t\t\t<img id="image" src="">\n\t\t\t\t<div id="question"></div>\n\t\t\t\t<input class="captcha-word mw-ui-input" placeholder="{{captchaMsg}}" />\n\t\t\t</div>\n\t\t</div>\n\t</div>\n\t{{>content}}\n</div>\n<div class="overlay-footer-container position-fixed">\n\t{{>footer}}\n</div>\n\t'),sectionId:"",log:function(e){mw.track("mf.schemaEditAttemptStep",n.extend(e,{editor_interface:this.editor,editing_session_id:this.sessionId}))},logFeatureUse:function(e){mw.track("mf.schemaVisualEditorFeatureUse",n.extend(e,{editor_interface:this.editor,editing_session_id:this.sessionId}))},confirmSave:function(){return!(this.isNewPage&&!window.confirm(mw.msg("mobile-frontend-editor-new-page-confirm",f)))},onSaveComplete:function(e){var t,i=n.getWindow(),o=this.options.title;this.saved=!0,this.pageGateway.invalidatePage(o),t=this.isNewPage?mw.msg("mobile-frontend-editor-success-new-page"):this.isNewEditor?mw.msg("mobile-frontend-editor-success-landmark-1"):mw.msg("mobile-frontend-editor-success"),mw.hook("postEditMobile").fire(),mw.config.get("wgPostEditConfirmationDisabled")||h.showOnPageReload(t,{type:"success"}),this.log({action:"saveSuccess",revision_id:e}),this.sectionId?window.location.hash="#"+this.sectionId:window.location.hash="#",i.off("beforeunload.mfeditorwarning"),window.location.reload()},onSaveFailure:function(e){var t=e&&e.errors&&e.errors[0]&&e.errors[0].code;e.edit&&e.edit.captcha&&(t="captcha"),this.log({action:"saveFailure",message:m(e),type:{editconflict:"editConflict",wasdeleted:"editPageDeleted","abusefilter-disallowed":"extensionAbuseFilter",captcha:"extensionCaptcha",spamprotectiontext:"extensionSpamBlacklist","titleblacklist-forbidden-edit":"extensionTitleBlacklist"}[t]||"responseUnknown"})},reportError:function(e){var t=new p({className:"errorbox",msg:e,heading:mw.msg("mobile-frontend-editor-error")});this.$errorNoticeContainer.html(t.$el)},hideErrorNotice:function(){this.$errorNoticeContainer.empty()},onStageChanges:function(){this.showHidden(".save-header, .save-panel"),this.hideErrorNotice(),this.log({action:"saveIntent"}),window.scrollTo(0,1)},onSaveBegin:function(){this.confirmAborted=!1,this.hideErrorNotice(),this.confirmSave()?this.log({action:"saveAttempt"}):this.confirmAborted=!0},preRender:function(){var e=this.options;this.options.headers=[s.formHeader(n.template('\n{{^hasToolbar}}\n<div class="overlay-title">\n\t<h2>{{{editingMsg}}}</h2>\n</div>\n{{/hasToolbar}}\n{{#hasToolbar}}<div class="toolbar"></div>{{/hasToolbar}}\n{{#editSwitcher}}\n\t<div class="switcher-container">\n\t</div>\n{{/editSwitcher}}\n\t\t\t\t').render({hasToolbar:e.hasToolbar,editSwitcher:e.editSwitcher,editingMsg:e.editingMsg}),e.readOnly?[]:[new c({tagName:"button",name:"next-invert",additionalClassNames:"continue",disabled:!0,title:e.continueMsg})],d.cancel(),"initial-header"),s.saveHeader(e.previewingMsg,"save-header hidden"),s.savingHeader(mw.msg("mobile-frontend-editor-wait"))]},postRender:function(){this.$errorNoticeContainer=this.$el.find("#error-notice-container"),o.prototype.postRender.apply(this),this.showHidden(".initial-header")},show:function(){var e=this;this.allowCloseWindow=mw.confirmCloseWindow({test:function(){return e.hasChanged()},message:mw.msg("mobile-frontend-editor-cancel-confirm"),namespace:"editwarning"}),this.saved=!1,o.prototype.show.call(this),mw.hook("mobileFrontend.editorOpened").fire(this.editor)},onClickBack:function(){},onClickSubmit:function(){this.onSaveBegin()},onClickContinue:function(){this.onStageChanges()},onClickAnonymous:function(){},onBeforeExit:function(e){var t,i=this;if(this.hasChanged()&&!this.switching)return(t=OO.ui.getWindowManager()).addWindows([new mw.widgets.AbandonEditDialog]),void t.openWindow("abandonedit").closed.then((function(t){t&&"discard"===t.action&&(i.log({action:"abort",mechanism:"cancel",type:"abandon"}),i.allowCloseWindow.release(),mw.hook("mobileFrontend.editorClosed").fire(),e())}));this.switching||this.saved||this.log({action:"abort",mechanism:"cancel",type:this.target&&this.target.edited?"abandon":"nochange"}),this.allowCloseWindow.release(),mw.hook("mobileFrontend.editorClosed").fire(),e()},createAnonWarning:function(e){var t=$("<div>").addClass("actions"),i=$("<div>").addClass("anonwarning content").append(new p({className:"warningbox anon-msg",msg:mw.msg("mobile-frontend-editor-anonwarning")}).$el,t),o=n.extend({returnto:e.returnTo||mw.config.get("wgPageName"),returntoquery:"action=edit&section="+e.sectionId,warning:"mobile-frontend-edit-login-action"},e.queryParams),r=n.extend({type:"signup",warning:"mobile-frontend-edit-signup-action"},e.signupQueryParams),s=[new l({label:mw.msg("mobile-frontend-editor-anon"),block:!0,additionalClassNames:"anonymous progressive",progressive:!0}),new l({block:!0,href:mw.util.getUrl("Special:UserLogin",o),label:mw.msg("mobile-frontend-watchlist-cta-button-login")}),new l({block:!0,href:mw.util.getUrl("Special:UserLogin",n.extend(o,r)),label:mw.msg("mobile-frontend-watchlist-cta-button-signup")})];return t.append(s.map((function(e){return e.$el}))),i},getOptionsForSwitch:function(){return{switched:!0,overlayManager:this.options.overlayManager,currentPageHTMLParser:this.options.currentPageHTMLParser,fakeScroll:this.options.fakeScroll,api:this.options.api,licenseMsg:this.options.licenseMsg,title:this.options.title,titleObj:this.options.titleObj,isAnon:this.options.isAnon,isNewPage:this.options.isNewPage,editCount:this.options.editCount,oldId:this.options.oldId,contentLang:this.options.contentLang,contentDir:this.options.contentDir,sessionId:this.options.sessionId,sectionId:this.options.sectionId}},hasChanged:function(){},getLoadingPromise:function(){return this.dataPromise.then((function(e){return e&&e.blockinfo?mw.loader.using("moment").then((function(){var t=r(e.blockinfo),i=g(t);return n.Deferred().reject(i)})):e}))},handleCaptcha:function(e){var t=this,i=this.$el.find(".captcha-word");this.captchaShown&&(i.val(""),i.attr("placeholder",this.options.captchaTryAgainMsg),setTimeout((function(){i.attr("placeholder",t.options.captchaMsg)}),2e3)),0===e.mime.indexOf("image/")?(this.$el.find(".captcha-panel#question").detach(),this.$el.find(".captcha-panel img").attr("src",e.url)):(this.$el.find(".captcha-panel #image").detach(),0===e.mime.indexOf("text/html")?this.$el.find(".captcha-panel #question").html(e.question):this.$el.find(".captcha-panel #question").text(e.question)),this.showHidden(".save-header, .captcha-panel"),this.captchaShown=!0}}),e.exports=v},"./src/mobile.editor.overlay/SourceEditorOverlay.js":function(e,t,i){var o=i("./src/mobile.editor.overlay/EditorOverlayBase.js"),n=i("./src/mobile.startup/util.js"),r=i("./src/mobile.startup/icons.js"),s=i("./src/mobile.startup/Section.js"),a=i("./src/mobile.editor.overlay/saveFailureMessage.js"),d=i("./src/mobile.editor.overlay/EditorGateway.js"),l=i("./src/mobile.init/fakeToolbar.js"),c=i("./src/mobile.startup/mfExtend.js"),h=i("./src/mobile.editor.overlay/VisualEditorOverlay.js");function m(e,t){this.isFirefox=/firefox/i.test(window.navigator.userAgent),this.visualEditorConfig=e.visualEditorConfig||mw.config.get("wgVisualEditorConfig")||{},this.gateway=new d({api:e.api,title:e.title,sectionId:e.sectionId,oldId:e.oldId,isNewPage:e.isNewPage,fromModified:!!t}),this.readOnly=!!e.oldId,this.dataPromise=t||this.gateway.getContent(),this.isVisualEditorEnabled()&&(e.editSwitcher=!0),this.readOnly?(e.readOnly=!0,e.editingMsg=mw.msg("mobile-frontend-editor-viewing-source-page",e.title)):e.editingMsg=mw.msg("mobile-frontend-editor-editing-page",e.title),e.previewingMsg=mw.msg("mobile-frontend-editor-previewing-page",e.title),o.call(this,n.extend(!0,{events:{"input .wikitext-editor":"onInputWikitextEditor"}},e))}c(m,o,{templatePartials:n.extend({},o.prototype.templatePartials,{content:n.template('\n<div lang="{{contentLang}}" dir="{{contentDir}}" class="editor-container content">\n\t<textarea class="wikitext-editor" id="wikitext-editor" cols="40" rows="10" placeholder="{{placeholder}}"></textarea>\n\t<div class="preview"></div>\n</div>\n\t\t')}),editor:"wikitext",sectionLine:"",show:function(){o.prototype.show.apply(this,arguments),this._resizeEditor()},isVisualEditorEnabled:function(){var e=this.visualEditorConfig.namespaces;return e&&e.indexOf(mw.config.get("wgNamespaceNumber"))>-1&&"translation"!==mw.config.get("wgTranslatePageTranslation")&&"wikitext"===mw.config.get("wgPageContentModel")},onInputWikitextEditor:function(){this.gateway.setContent(this.$el.find(".wikitext-editor").val()),this.$el.find(".continue, .submit").prop("disabled",!1)},onClickBack:function(){o.prototype.onClickBack.apply(this,arguments),this._hidePreview()},postRender:function(){var e=this,t=this.visualEditorConfig,i=this.options,n=i.isAnon&&!i.switched;this.log({action:"ready"}),this.log({action:"loaded"}),this.isVisualEditorEnabled()&&mw.loader.using("ext.visualEditor.switching").then((function(){var i,o,n,r=new OO.ui.ToolFactory,s=new OO.ui.ToolGroupFactory;r.register(mw.libs.ve.MWEditModeVisualTool),r.register(mw.libs.ve.MWEditModeSourceTool),(i=new OO.ui.Toolbar(r,s,{classes:["editor-switcher"]})).on("switchEditor",(function(i){var r=t.fullRestbaseUrl||t.allowLossySwitching;"visual"===i&&(e.gateway.hasChanged?r?e._switchToVisualEditor(e.gateway.content):(o=new OO.ui.WindowManager,n=new mw.libs.ve.SwitchConfirmDialog,o.$element.appendTo(document.body),o.addWindows([n]),o.openWindow(n,{mode:"simple"}).closed.then((function(t){t&&"discard"===t.action&&e._switchToVisualEditor(),o.destroy()}))):e._switchToVisualEditor())})),i.setup([{name:"editMode",type:"list",icon:"edit",title:mw.msg("visualeditor-mweditmode-tooltip"),include:["editModeVisual","editModeSource"]}]),e.$el.find(".switcher-container").html(i.$element),i.emit("updateState")})),o.prototype.postRender.apply(this),this.$el.find(".overlay-content").append(r.spinner().$el),this.hideSpinner(),this.$preview=this.$el.find(".preview"),this.$content=this.$el.find(".wikitext-editor"),this.$content.addClass("mw-editfont-"+mw.user.options.get("editfont")),n&&(this.$anonWarning=this.createAnonWarning(i),this.$el.find(".editor-container").append(this.$anonWarning),this.$content.hide(),this.$anonHiddenButtons=this.$el.find(".overlay-header .continue, .editor-switcher").hide()),this.$el.find(".license a").attr("target","_blank"),this.readOnly&&this.$content.prop("readonly",!0),this.$content.on("input",this._resizeEditor.bind(this)).one("input",(function(){e.log({action:"firstChange"})})),this.isFirefox&&this.$content.on("mousedown",(function(){var e=document.documentElement,t=e.scrollTop;function i(){e.scrollTop=t}window.addEventListener("scroll",i),setTimeout((function(){window.removeEventListener("scroll",i)}),1e3)})),n||this._loadContent()},onClickAnonymous:function(){this.$anonWarning.hide(),this.$anonHiddenButtons.show(),this._loadContent()},onStageChanges:function(){var e=this,t={text:this.getContent()};function i(){e.hideSpinner(),e.$preview.show()}this.scrollTop=n.getDocument().find("body").scrollTop(),this.$content.hide(),this.showSpinner(),mw.config.get("wgIsMainPage")&&(t.mainpage=1),this.gateway.getPreview(t).then((function(t){var o=t.text,n=t.line;e.sectionId=t.id,e.sectionLine=e.parseHTML("<div>").html(n).text(),new s({el:e.$preview,text:o}).$el.find("a").on("click",!1),i()}),(function(){e.$preview.addClass("errorbox").text(mw.msg("mobile-frontend-editor-error-preview")),i()})),o.prototype.onStageChanges.apply(this,arguments)},_hidePreview:function(){this.gateway.abortPreview(),this.hideSpinner(),this.$preview.removeClass("errorbox").hide(),this.$content.show(),window.scrollTo(0,this.scrollTop),this.showHidden(".initial-header")},_resizeEditor:function(){var e,t,i;this.$scrollContainer?i=this.$scrollContainer:(t=OO.ui.Element.static.getClosestScrollableContainer(this.$content[0]),i=this.$el.find(t).length?this.$el.find(t):n.getDocument(),this.$scrollContainer=i,this.$content.css("padding-bottom",.6*this.$scrollContainer.height())),this.$content.prop("scrollHeight")&&i.length&&(e=i.scrollTop(),this.$content.css("height","auto").css("height",this.$content.prop("scrollHeight")+2+"px"),i.scrollTop(e))},setContent:function(e){this.$content.show().val(e),this._resizeEditor()},getContent:function(){return this.$content.val()},_loadContent:function(){var e=this;this.$content.hide(),this.getLoadingPromise().then((function(t){var i=t.text;e.setContent(i),e.gateway.fromModified&&e.onInputWikitextEditor()}))},_switchToVisualEditor:function(e){var t=this;this.log({action:"abort",type:"switchnochange",mechanism:"navigate"}),this.logFeatureUse({feature:"editor-switch",action:"visual-mobile"}),mw.storage.set("preferredEditor","VisualEditor"),this.$el.addClass("switching"),this.$el.find(".overlay-header-container").hide(),this.$el.append(l()),this.$content.prop("readonly",!0),mw.loader.using("ext.visualEditor.targetLoader").then((function(){return mw.libs.ve.targetLoader.addPlugin("mobile.editor.ve"),mw.libs.ve.targetLoader.loadModules("visual")})).then((function(){var i,o=t.getOptionsForSwitch();o.SourceEditorOverlay=m,e?o.dataPromise=mw.libs.ve.targetLoader.requestPageData("visual",mw.config.get("wgRelevantPageName"),{section:o.sectionId,oldId:o.oldId||mw.config.get("wgRevisionId"),targetName:"mobile",modified:!0,wikitext:e}):delete o.dataPromise,(i=new h(o)).getLoadingPromise().then((function(){t.switching=!0,t.overlayManager.replaceCurrent(i),t.switching=!1}))}),(function(){t.$el.removeClass("switching"),t.$el.find(".overlay-header-container").show(),t.$el.find(".ve-mobile-fakeToolbar-container").remove(),t.$content.prop("readonly",!1)}))},onSaveBegin:function(){var e=this,t={summary:this.$el.find(".summary").val()};""!==e.sectionLine&&(t.summary="/* "+e.sectionLine+" */"+t.summary),o.prototype.onSaveBegin.apply(this,arguments),this.confirmAborted||(this.captchaId&&(t.captchaId=this.captchaId,t.captchaWord=this.$el.find(".captcha-word").val()),this.showHidden(".saving-header"),this.gateway.save(t).then((function(t){var i=e.options.title;mw.config.get("wgIsMainPage")?window.location=mw.util.getUrl(i):e.onSaveComplete(t)}),(function(t){e.onSaveFailure(t)})))},onSaveFailure:function(e){var t;e.edit&&e.edit.captcha?(this.captchaId=e.edit.captcha.id,this.handleCaptcha(e.edit.captcha)):(t=a(e),this.reportError(t),this.showHidden(".save-header, .save-panel"),e.errors&&e.errors.some((function(e){return"abusefilter-disallowed"===e.code}))&&this.$el.find(".continue, .submit").prop("disabled",!0)),o.prototype.onSaveFailure.apply(this,arguments)},hasChanged:function(){return this.gateway.hasChanged}}),e.exports=m},"./src/mobile.editor.overlay/VisualEditorOverlay.js":function(e,t,i){var o=i("./src/mobile.editor.overlay/EditorOverlayBase.js"),n=i("./src/mobile.editor.overlay/EditorGateway.js"),r=i("./src/mobile.init/fakeToolbar.js"),s=i("./src/mobile.startup/mfExtend.js"),a=mw.loader.require("mediawiki.router"),d=i("./src/mobile.editor.overlay/identifyLeadParagraph.js"),l=i("./src/mobile.startup/util.js");function c(e){var t=l.Deferred();o.call(this,l.extend({editSwitcher:!1,hasToolbar:!0,onBeforeExit:this.onBeforeExit.bind(this),isBorderBox:!1,className:"overlay editor-overlay editor-overlay-ve"},e)),this.SourceEditorOverlay=e.SourceEditorOverlay,this.isNewPage=e.isNewPage,this.fromModified=e.dataPromise&&e.switched,this.gateway=new n({api:e.api,title:e.title,sectionId:e.sectionId,oldId:e.oldId,isNewPage:e.isNewPage}),this.origDataPromise=this.options.dataPromise||mw.libs.ve.targetLoader.requestPageData("visual",e.titleObj.getPrefixedDb(),{sessionStore:!0,section:e.sectionId||null,oldId:e.oldId||void 0,targetName:ve.init.mw.MobileArticleTarget.static.trackingName}),this.target=ve.init.mw.targetFactory.create("article",this,{$element:this.$el,section:this.options.sectionId||null}),this.target.once("surfaceReady",function(){t.resolve(),this.target.getSurface().getModel().getDocument().once("transact",function(){this.log({action:"firstChange"})}.bind(this))}.bind(this)),this.target.load(this.origDataPromise),this.dataPromise=this.origDataPromise.then((function(e){return t.then((function(){return e&&e.visualeditor}))}))}s(c,o,{templatePartials:l.extend({},o.prototype.templatePartials,{editHeader:l.template('\n<div class="overlay-header header initial-header hideable hidden">\n\t<div class="toolbar"></div>\n</div>\n\t\t'),content:l.template('\n<div class="surface" lang="{{contentLang}}" dir="{{contentDir}}">\n</div>\n\t\t')}),editor:"visualeditor",destroyTarget:function(){this.target&&(this.target.destroy(),this.target=null)},show:function(){var e=this.options,t=e.isAnon&&!e.switched;o.prototype.show.apply(this,arguments),this.emit("editor-loaded"),this.log({action:"ready"}),this.log({action:"loaded"}),t?(this.$anonWarning=this.createAnonWarning(this.options),this.$el.append(this.$anonWarning),this.$el.find(".overlay-content").hide()):this.redoTargetInit()},redoTargetInit:function(){this.target.adjustContentPadding(),this.target.restoreEditSection(),this.scrollToLeadParagraph()},scrollToLeadParagraph:function(){var e,t,i,o,n,r=this.options.currentPageHTMLParser,s=this.options.fakeScroll,a=$(window),l=this.target.section,c=this.target.getSurface(),h=c.getMode();null!==l&&"0"!==l||"visual"!==h||(e=d(c.getView().$attachedRootNode),r.getLeadSectionElement()&&(i=d(r.getLeadSectionElement())),e&&i&&(o=$(e).offset().top-($(i).offset().top-s),(t=$(e).data("view"))&&(c.getModel().setLinearSelection(new ve.Range(t.getModel().getRange().start)),n=c.getView().getSelection().getSelectionBoundingRect().top,c.$element.css("min-height",a.height()+n-c.padding.top)),a.scrollTop(o)))},onBeforeExit:function(e){var t=this;o.prototype.onBeforeExit.call(this,(function(){e(),t.destroyTarget()}))},onClickBack:function(){o.prototype.onClickBack.apply(this,arguments),this.switchToEditor()},onClickAnonymous:function(){this.$anonWarning.hide(),this.$el.find(".overlay-content").show(),this.redoTargetInit()},switchToEditor:function(){this.showHidden(".initial-header")},switchToSourceEditor:function(e){var t,i=this,o=this.SourceEditorOverlay,n=this.getOptionsForSwitch();this.log({action:"abort",type:"switchnochange",mechanism:"navigate"}),this.logFeatureUse({feature:"editor-switch",action:"source-mobile"}),mw.storage.set("preferredEditor","SourceEditor"),this.$el.addClass("switching"),this.$el.find(".overlay-header-container").hide(),this.$el.append(r()),this.target.getSurface().setReadOnly(!0),e&&(n.sectionId=null,a.navigateTo(document.title,{path:"#/editor/all",useReplaceState:!0})),(t=new o(n,e)).getLoadingPromise().then((function(){i.switching=!0,i.overlayManager.replaceCurrent(t),i.switching=!1}))},onSaveComplete:function(){o.prototype.onSaveComplete.apply(this,arguments),this.destroyTarget()},hasChanged:function(){return!this.saved&&(this.fromModified||this.target&&this.target.getSurface()&&this.target.getSurface().getModel().hasBeenModified())}}),e.exports=c},"./src/mobile.editor.overlay/blockMessageDrawer.js":function(e,t,i){var o=i("./src/mobile.startup/Drawer.js"),n=i("./src/mobile.editor.overlay/BlockMessageDetails.js");e.exports=function(e){return new o({className:"drawer block-message",onBeforeHide:function(e){e.$el.remove()},children:[new n(e).$el]})}},"./src/mobile.editor.overlay/identifyLeadParagraph.js":function(e,t){e.exports=function(e){var t,i,o;function n(e){var t;if(e=e.cloneNode(!0),$(e).find(".ve-ce-branchNode-inlineSlug, .ve-ce-focusableNode-invisible").remove(),function(e){return!/^[\s↵➞]*$/.test(e.textContent)}(e)){if(!(t=$(e).find("span#coordinates")).length)return!1;if(e.textContent)return e.textContent===t[0].textContent}return!0}for(t=e.children("p"),i=0;i<t.length;i++)if(!n(o=t[i]))return o;return null}},"./src/mobile.editor.overlay/mobile.editor.overlay.js":function(e,t,i){var o=i("./src/mobile.startup/moduleLoaderSingleton.js"),n=i("./src/mobile.editor.overlay/SourceEditorOverlay.js"),r=i("./src/mobile.editor.overlay/VisualEditorOverlay.js");o.define("mobile.editor.overlay/SourceEditorOverlay",n),o.define("mobile.editor.overlay/VisualEditorOverlay",r)},"./src/mobile.editor.overlay/parseBlockInfo.js":function(e,t){e.exports=function(e){var t,i,o,n,r=window.moment;return t={partial:e.blockpartial||!1,creator:{name:e.blockedby,url:null},expiry:null,duration:null,reason:"",blockId:e.blockid},0===e.blockedbyid?t.creator.url="":t.creator.url=mw.Title.makeTitle(mw.config.get("wgNamespaceIds").user,t.creator.name).getUrl(),i=e.blockexpiry,-1===["infinite","indefinite","infinity","never"].indexOf(i)&&(n=r(i).diff(r(),"seconds"),t.expiry=n<=86400?mw.message("parentheses",r(i).format("LL, LT")).escaped():mw.message("parentheses",r(i).format("LL")).escaped(),t.duration=r().to(i,!0)),o=e.blockreason,t.reason=o?function(e){var t,i;t=new mw.jqueryMsg.parser;try{return i=t.wikiTextToAst(e),t.emitter.emit(i).html()}catch(e){return!1}}(o)||mw.html.escape(o):mw.message("mobile-frontend-editor-generic-block-reason").escaped(),t}},"./src/mobile.editor.overlay/saveFailureMessage.js":function(e,t){e.exports=function(e){var t=e&&e.errors&&e.errors[0]&&e.errors[0].code;return"editconflict"===t?mw.msg("mobile-frontend-editor-error-conflict"):"readonly"===t?e.errors[0].html+"<br>"+e.errors[0].data.readonlyreason:e.errors&&e.errors[0]?e.errors[0].html:""}}},[["./src/mobile.editor.overlay/mobile.editor.overlay.js",0,1]]]);
//# sourceMappingURL=mobile.editor.overlay.js.map.json