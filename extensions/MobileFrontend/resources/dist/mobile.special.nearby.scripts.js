this.mfModules=this.mfModules||{},this.mfModules["mobile.special.nearby.scripts"]=(window.webpackJsonp=window.webpackJsonp||[]).push([[9],{"./src/mobile.special.nearby.scripts/LocationProvider.js":function(e,t,r){var n,i=r("./src/mobile.startup/Browser.js").getSingleton(),s=r("./src/mobile.startup/util.js");n={isAvailable:function(){return i.supportsGeoLocation()},getCurrentPosition:function(){var e=s.Deferred();return n.isAvailable()?navigator.geolocation.getCurrentPosition((function(t){e.resolve({latitude:t.coords.latitude,longitude:t.coords.longitude})}),(function(t){var r;switch(t.code){case t.PERMISSION_DENIED:r="permission";break;case t.TIMEOUT:r="timeout";break;case t.POSITION_UNAVAILABLE:r="location";break;default:r="unknown"}e.reject(r)}),{timeout:1e4,enableHighAccuracy:!0}):e.reject("incompatible"),e}},e.exports=n},"./src/mobile.special.nearby.scripts/NearbyGateway.js":function(e,t,r){var n=r("./src/mobile.startup/page/pageJSONParser.js"),i=mw.config.get("wgContentNamespaces"),s=r("./src/mobile.startup/util.js"),a=r("./src/mobile.startup/extendSearchParams.js");function o(e){this.api=e.api}o.prototype={_distanceMessage:function(e){if(e<1){if(e*=100,1e3!==(e=10*Math.ceil(e)))return mw.msg("mobile-frontend-nearby-distance-meters",mw.language.convertNumber(e));e=1}else e>2?(e*=10,e=(e=Math.ceil(e)/10).toFixed(1)):(e*=100,e=(e=Math.ceil(e)/100).toFixed(2));return mw.msg("mobile-frontend-nearby-distance",mw.language.convertNumber(e))},getPages:function(e,t,r){return this._search({ggscoord:[e.latitude,e.longitude]},t,r)},getPagesAroundPage:function(e,t){return this._search({ggspage:e},t,e)},_search:function(e,t,r){var o,c=s.Deferred(),u=this;return o=a("nearby",{colimit:"max",prop:["coordinates"],generator:"geosearch",ggsradius:t,ggsnamespace:i,ggslimit:50},e),e.ggscoord?o.codistancefrompoint=e.ggscoord:e.ggspage&&(o.codistancefrompage=e.ggspage),this.api.ajax(o).then((function(e){var t;(t=(t=e.query&&e.query.pages||[]).map((function(e,t){var i,s;return(s=n.parse(e)).anchor="item_"+t,e.coordinates?(i=e.coordinates[0],s.dist=i.dist/1e3,s.latitude=i.lat,s.longitude=i.lon,s.proximity=u._distanceMessage(s.dist)):s.dist=0,r!==e.title?s:null})).filter((function(e){return!!e}))).sort((function(e,t){return e.dist>t.dist?1:-1})),0===t.length?c.reject("empty"):c.resolve(t)}),(function(e){c.reject(e)})),c}},e.exports=o},"./src/mobile.special.nearby.scripts/mobile.special.nearby.scripts.js":function(e,t,r){var n=new mw.Api,i=r("./src/mobile.special.nearby.scripts/nearbyErrorMessage.js"),s=r("./src/mobile.special.nearby.scripts/LocationProvider.js"),a=mw.config.get("wgMFNearbyRange")||1e3,o=r("./src/mobile.startup/promisedView.js"),c=mw.loader.require("mediawiki.router"),u=$("#mw-mf-nearby"),l=r("./src/mobile.startup/watchstar/WatchstarPageList.js"),g=$("#mf-nearby-info-holder"),m=new(r("./src/mobile.special.nearby.scripts/NearbyGateway.js"))({api:n});function d(e,t){g.hide(),$("body").removeClass("nearby-accept-pending"),t.empty().append(e.$el).show()}function p(e,t){var r={latitude:e,longitude:t};return o(m.getPages(r,a).then((function(e){return new l({pages:e,api:n})}),(function(e){return i(e)})))}c.on("hashchange",(function(){""===c.getPath()?(g.show(),u.hide()):(g.hide(),u.show())})),c.route(/^\/coord\/(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/,(function(e,t){d(p(e,t),u)})),c.route(/^\/page\/(.+)$/,(function(e){d(o(m.getPagesAroundPage(mw.Uri.decode(e),a).then((function(e){return new l({pages:e,api:n})})).catch((function(e){return i(e)}))),u)})),c.checkRoute(),$((function(){$("#showArticles").prop("disabled",!1).on("click",(function(){d(o(s.getCurrentPosition().then((function(e){var t=e.latitude,r=e.longitude;return c.navigateTo(null,{path:"#/coord/".concat(t,",").concat(r)}),p(t,r)})).catch((function(e){return i(e)}))),u)}))}))},"./src/mobile.special.nearby.scripts/nearbyErrorMessage.js":function(e,t,r){var n=r("./src/mobile.startup/util.js"),i=r("./src/mobile.startup/MessageBox.js"),s={permission:{hasHeading:!1,msg:mw.msg("mobile-frontend-nearby-permission-denied")},location:{hasHeading:!1,msg:mw.msg("mobile-frontend-nearby-location-unavailable")},empty:{heading:mw.msg("mobile-frontend-nearby-noresults"),hasHeading:!0,msg:mw.msg("mobile-frontend-nearby-noresults-guidance")},http:{heading:mw.msg("mobile-frontend-nearby-error"),hasHeading:!0,msg:mw.msg("mobile-frontend-nearby-error-guidance")},incompatible:{heading:mw.msg("mobile-frontend-nearby-requirements"),hasHeading:!0,msg:mw.msg("mobile-frontend-nearby-requirements-guidance")}};e.exports=function(e){var t=s[e]||s.http;return new i(n.extend({className:"errorbox"},t))}}},[["./src/mobile.special.nearby.scripts/mobile.special.nearby.scripts.js",0,1]]]);
//# sourceMappingURL=mobile.special.nearby.scripts.js.map.json