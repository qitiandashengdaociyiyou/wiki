function isCompatible(ua){return!!((function(){'use strict';return!this&&Function.prototype.bind;}())&&'querySelector'in document&&'localStorage'in window&&!ua.match(/MSIE 10|NetFront|Opera Mini|S40OviBrowser|MeeGo|Android.+Glass|^Mozilla\/5\.0 .+ Gecko\/$|googleweblight|PLAYSTATION|PlayStation/));}if(!isCompatible(navigator.userAgent)){document.documentElement.className=document.documentElement.className.replace(/(^|\s)client-js(\s|$)/,'$1client-nojs$2');while(window.NORLQ&&NORLQ[0]){NORLQ.shift()();}NORLQ={push:function(fn){fn();}};RLQ={push:function(){}};}else{if(window.performance&&performance.mark){performance.mark('mwStartup');}(function(){'use strict';var con=window.console;function logError(topic,data){if(con.log){var e=data.exception;var msg=(e?'Exception':'Error')+' in '+data.source+(data.module?' in module '+data.module:'')+(e?':':'.');con.log(msg);if(e&&con.warn){con.warn(e);}}}function Map(){this.values=Object.create(null);}Map.prototype={constructor:Map,get:function(
selection,fallback){if(arguments.length<2){fallback=null;}if(typeof selection==='string'){return selection in this.values?this.values[selection]:fallback;}var results;if(Array.isArray(selection)){results={};for(var i=0;i<selection.length;i++){if(typeof selection[i]==='string'){results[selection[i]]=selection[i]in this.values?this.values[selection[i]]:fallback;}}return results;}if(selection===undefined){results={};for(var key in this.values){results[key]=this.values[key];}return results;}return fallback;},set:function(selection,value){if(arguments.length>1){if(typeof selection==='string'){this.values[selection]=value;return true;}}else if(typeof selection==='object'){for(var key in selection){this.values[key]=selection[key];}return true;}return false;},exists:function(selection){return typeof selection==='string'&&selection in this.values;}};var log=function(){};log.warn=con.warn?Function.prototype.bind.call(con.warn,con):function(){};var mw={now:function(){var perf=window.performance;
var navStart=perf&&perf.timing&&perf.timing.navigationStart;mw.now=navStart&&perf.now?function(){return navStart+perf.now();}:Date.now;return mw.now();},trackQueue:[],track:function(topic,data){mw.trackQueue.push({topic:topic,data:data});},trackError:function(topic,data){mw.track(topic,data);logError(topic,data);},Map:Map,config:new Map(),messages:new Map(),templates:new Map(),log:log};window.mw=window.mediaWiki=mw;}());(function(){'use strict';var StringSet,store,hasOwn=Object.hasOwnProperty;function defineFallbacks(){StringSet=window.Set||function(){var set=Object.create(null);return{add:function(value){set[value]=true;},has:function(value){return value in set;}};};}defineFallbacks();function fnv132(str){var hash=0x811C9DC5;for(var i=0;i<str.length;i++){hash+=(hash<<1)+(hash<<4)+(hash<<7)+(hash<<8)+(hash<<24);hash^=str.charCodeAt(i);}hash=(hash>>>0).toString(36).slice(0,5);while(hash.length<5){hash='0'+hash;}return hash;}var isES6Supported=typeof Promise==='function'&&Promise.
prototype.finally&&/./g.flags==='g'&&(function(){try{new Function('(a = 0) => a');return true;}catch(e){return false;}}());var registry=Object.create(null),sources=Object.create(null),handlingPendingRequests=false,pendingRequests=[],queue=[],jobs=[],willPropagate=false,errorModules=[],baseModules=["jquery","mediawiki.base"],marker=document.querySelector('meta[name="ResourceLoaderDynamicStyles"]'),lastCssBuffer,rAF=window.requestAnimationFrame||setTimeout;function addToHead(el,nextNode){if(nextNode&&nextNode.parentNode){nextNode.parentNode.insertBefore(el,nextNode);}else{document.head.appendChild(el);}}function newStyleTag(text,nextNode){var el=document.createElement('style');el.appendChild(document.createTextNode(text));addToHead(el,nextNode);return el;}function flushCssBuffer(cssBuffer){if(cssBuffer===lastCssBuffer){lastCssBuffer=null;}newStyleTag(cssBuffer.cssText,marker);for(var i=0;i<cssBuffer.callbacks.length;i++){cssBuffer.callbacks[i]();}}function addEmbeddedCSS(cssText,callback
){if(!lastCssBuffer||cssText.slice(0,7)==='@import'){lastCssBuffer={cssText:'',callbacks:[]};rAF(flushCssBuffer.bind(null,lastCssBuffer));}lastCssBuffer.cssText+='\n'+cssText;lastCssBuffer.callbacks.push(callback);}function getCombinedVersion(modules){var hashes=modules.reduce(function(result,module){return result+registry[module].version;},'');return fnv132(hashes);}function allReady(modules){for(var i=0;i<modules.length;i++){if(mw.loader.getState(modules[i])!=='ready'){return false;}}return true;}function allWithImplicitReady(module){return allReady(registry[module].dependencies)&&(baseModules.indexOf(module)!==-1||allReady(baseModules));}function anyFailed(modules){for(var i=0;i<modules.length;i++){var state=mw.loader.getState(modules[i]);if(state==='error'||state==='missing'){return modules[i];}}return false;}function doPropagation(){var didPropagate=true;var module;while(didPropagate){didPropagate=false;while(errorModules.length){var errorModule=errorModules.shift(),
baseModuleError=baseModules.indexOf(errorModule)!==-1;for(module in registry){if(registry[module].state!=='error'&&registry[module].state!=='missing'){if(baseModuleError&&baseModules.indexOf(module)===-1){registry[module].state='error';didPropagate=true;}else if(registry[module].dependencies.indexOf(errorModule)!==-1){registry[module].state='error';errorModules.push(module);didPropagate=true;}}}}for(module in registry){if(registry[module].state==='loaded'&&allWithImplicitReady(module)){execute(module);didPropagate=true;}}for(var i=0;i<jobs.length;i++){var job=jobs[i];var failed=anyFailed(job.dependencies);if(failed!==false||allReady(job.dependencies)){jobs.splice(i,1);i-=1;try{if(failed!==false&&job.error){job.error(new Error('Failed dependency: '+failed),job.dependencies);}else if(failed===false&&job.ready){job.ready();}}catch(e){mw.trackError('resourceloader.exception',{exception:e,source:'load-callback'});}didPropagate=true;}}}willPropagate=false;}function setAndPropagate(module,
state){registry[module].state=state;if(state==='ready'){store.add(module);}else if(state==='error'||state==='missing'){errorModules.push(module);}else if(state!=='loaded'){return;}if(willPropagate){return;}willPropagate=true;mw.requestIdleCallback(doPropagation,{timeout:1});}function sortDependencies(module,resolved,unresolved){if(!(module in registry)){throw new Error('Unknown module: '+module);}if(typeof registry[module].skip==='string'){var skip=(new Function(registry[module].skip)());registry[module].skip=!!skip;if(skip){registry[module].dependencies=[];setAndPropagate(module,'ready');return;}}if(!unresolved){unresolved=new StringSet();}var deps=registry[module].dependencies;unresolved.add(module);for(var i=0;i<deps.length;i++){if(resolved.indexOf(deps[i])===-1){if(unresolved.has(deps[i])){throw new Error('Circular reference detected: '+module+' -> '+deps[i]);}sortDependencies(deps[i],resolved,unresolved);}}resolved.push(module);}function resolve(modules){var resolved=baseModules.
slice();for(var i=0;i<modules.length;i++){sortDependencies(modules[i],resolved);}return resolved;}function resolveStubbornly(modules){var resolved=baseModules.slice();for(var i=0;i<modules.length;i++){var saved=resolved.slice();try{sortDependencies(modules[i],resolved);}catch(err){resolved=saved;mw.log.warn('Skipped unavailable module '+modules[i]);if(modules[i]in registry){mw.trackError('resourceloader.exception',{exception:err,source:'resolve'});}}}return resolved;}function resolveRelativePath(relativePath,basePath){var relParts=relativePath.match(/^((?:\.\.?\/)+)(.*)$/);if(!relParts){return null;}var baseDirParts=basePath.split('/');baseDirParts.pop();var prefixes=relParts[1].split('/');prefixes.pop();var prefix;while((prefix=prefixes.pop())!==undefined){if(prefix==='..'){baseDirParts.pop();}}return(baseDirParts.length?baseDirParts.join('/')+'/':'')+relParts[2];}function makeRequireFunction(moduleObj,basePath){return function require(moduleName){var fileName=resolveRelativePath(
moduleName,basePath);if(fileName===null){return mw.loader.require(moduleName);}if(hasOwn.call(moduleObj.packageExports,fileName)){return moduleObj.packageExports[fileName];}var scriptFiles=moduleObj.script.files;if(!hasOwn.call(scriptFiles,fileName)){throw new Error('Cannot require undefined file '+fileName);}var result,fileContent=scriptFiles[fileName];if(typeof fileContent==='function'){var moduleParam={exports:{}};fileContent(makeRequireFunction(moduleObj,fileName),moduleParam,moduleParam.exports);result=moduleParam.exports;}else{result=fileContent;}moduleObj.packageExports[fileName]=result;return result;};}function addScript(src,callback){var script=document.createElement('script');script.src=src;script.onload=script.onerror=function(){if(script.parentNode){script.parentNode.removeChild(script);}if(callback){callback();callback=null;}};document.head.appendChild(script);return script;}function queueModuleScript(src,moduleName,callback){pendingRequests.push(function(){if(moduleName
!=='jquery'){window.require=mw.loader.require;window.module=registry[moduleName].module;}addScript(src,function(){delete window.module;callback();if(pendingRequests[0]){pendingRequests.shift()();}else{handlingPendingRequests=false;}});});if(!handlingPendingRequests&&pendingRequests[0]){handlingPendingRequests=true;pendingRequests.shift()();}}function addLink(url,media,nextNode){var el=document.createElement('link');el.rel='stylesheet';if(media){el.media=media;}el.href=url;addToHead(el,nextNode);return el;}function domEval(code){var script=document.createElement('script');if(mw.config.get('wgCSPNonce')!==false){script.nonce=mw.config.get('wgCSPNonce');}script.text=code;document.head.appendChild(script);script.parentNode.removeChild(script);}function enqueue(dependencies,ready,error){if(allReady(dependencies)){if(ready){ready();}return;}var failed=anyFailed(dependencies);if(failed!==false){if(error){error(new Error('Dependency '+failed+' failed to load'),dependencies);}return;}if(ready||
error){jobs.push({dependencies:dependencies.filter(function(module){var state=registry[module].state;return state==='registered'||state==='loaded'||state==='loading'||state==='executing';}),ready:ready,error:error});}dependencies.forEach(function(module){if(registry[module].state==='registered'&&queue.indexOf(module)===-1){queue.push(module);}});mw.loader.work();}function execute(module){if(registry[module].state!=='loaded'){throw new Error('Module in state "'+registry[module].state+'" may not execute: '+module);}registry[module].state='executing';var runScript=function(){var script=registry[module].script;var markModuleReady=function(){setAndPropagate(module,'ready');};var nestedAddScript=function(arr,offset){if(offset>=arr.length){markModuleReady();return;}queueModuleScript(arr[offset],module,function(){nestedAddScript(arr,offset+1);});};try{if(Array.isArray(script)){nestedAddScript(script,0);}else if(typeof script==='function'){if(module==='jquery'){script();}else{script(window.$,
window.$,mw.loader.require,registry[module].module);}markModuleReady();}else if(typeof script==='object'&&script!==null){var mainScript=script.files[script.main];if(typeof mainScript!=='function'){throw new Error('Main file in module '+module+' must be a function');}mainScript(makeRequireFunction(registry[module],script.main),registry[module].module,registry[module].module.exports);markModuleReady();}else if(typeof script==='string'){domEval(script);markModuleReady();}else{markModuleReady();}}catch(e){setAndPropagate(module,'error');mw.trackError('resourceloader.exception',{exception:e,module:module,source:'module-execute'});}};if(registry[module].messages){mw.messages.set(registry[module].messages);}if(registry[module].templates){mw.templates.set(module,registry[module].templates);}var cssPending=0;var cssHandle=function(){cssPending++;return function(){cssPending--;if(cssPending===0){var runScriptCopy=runScript;runScript=undefined;runScriptCopy();}};};if(registry[module].style){for(
var key in registry[module].style){var value=registry[module].style[key];if(key==='css'){for(var i=0;i<value.length;i++){addEmbeddedCSS(value[i],cssHandle());}}else if(key==='url'){for(var media in value){var urls=value[media];for(var j=0;j<urls.length;j++){addLink(urls[j],media,marker);}}}}}if(module==='user'){var siteDeps;var siteDepErr;try{siteDeps=resolve(['site']);}catch(e){siteDepErr=e;runScript();}if(!siteDepErr){enqueue(siteDeps,runScript,runScript);}}else if(cssPending===0){runScript();}}function sortQuery(o){var sorted={};var list=[];for(var key in o){list.push(key);}list.sort();for(var i=0;i<list.length;i++){sorted[list[i]]=o[list[i]];}return sorted;}function buildModulesString(moduleMap){var str=[];var list=[];var p;function restore(suffix){return p+suffix;}for(var prefix in moduleMap){p=prefix===''?'':prefix+'.';str.push(p+moduleMap[prefix].join(','));list.push.apply(list,moduleMap[prefix].map(restore));}return{str:str.join('|'),list:list};}function makeQueryString(params)
{var str='';for(var key in params){str+=(str?'&':'')+encodeURIComponent(key)+'='+encodeURIComponent(params[key]);}return str;}function batchRequest(batch){if(!batch.length){return;}var sourceLoadScript,currReqBase,moduleMap;function doRequest(){var query=Object.create(currReqBase),packed=buildModulesString(moduleMap);query.modules=packed.str;query.version=getCombinedVersion(packed.list);query=sortQuery(query);addScript(sourceLoadScript+'?'+makeQueryString(query));}batch.sort();var reqBase={"lang":"en","skin":"minerva"};var splits=Object.create(null);for(var b=0;b<batch.length;b++){var bSource=registry[batch[b]].source;var bGroup=registry[batch[b]].group;if(!splits[bSource]){splits[bSource]=Object.create(null);}if(!splits[bSource][bGroup]){splits[bSource][bGroup]=[];}splits[bSource][bGroup].push(batch[b]);}for(var source in splits){sourceLoadScript=sources[source];for(var group in splits[source]){var modules=splits[source][group];currReqBase=Object.create(reqBase);if(group===0&&mw.
config.get('wgUserName')!==null){currReqBase.user=mw.config.get('wgUserName');}var currReqBaseLength=makeQueryString(currReqBase).length+23;var length=0;moduleMap=Object.create(null);for(var i=0;i<modules.length;i++){var lastDotIndex=modules[i].lastIndexOf('.'),prefix=modules[i].slice(0,Math.max(0,lastDotIndex)),suffix=modules[i].slice(lastDotIndex+1),bytesAdded=moduleMap[prefix]?suffix.length+3:modules[i].length+3;if(length&&length+currReqBaseLength+bytesAdded>mw.loader.maxQueryLength){doRequest();length=0;moduleMap=Object.create(null);}if(!moduleMap[prefix]){moduleMap[prefix]=[];}length+=bytesAdded;moduleMap[prefix].push(suffix);}doRequest();}}}function asyncEval(implementations,cb){if(!implementations.length){return;}mw.requestIdleCallback(function(){try{domEval(implementations.join(';'));}catch(err){cb(err);}});}function getModuleKey(module){return module in registry?(module+'@'+registry[module].version):null;}function splitModuleKey(key){var index=key.lastIndexOf('@');if(index===-
1||index===0){return{name:key,version:''};}return{name:key.slice(0,index),version:key.slice(index+1)};}function registerOne(module,version,dependencies,group,source,skip){if(module in registry){throw new Error('module already registered: '+module);}version=String(version||'');if(version.slice(-1)==='!'){if(!isES6Supported){return;}version=version.slice(0,-1);}registry[module]={module:{exports:{}},packageExports:{},version:version,dependencies:dependencies||[],group:typeof group==='undefined'?null:group,source:typeof source==='string'?source:'local',state:'registered',skip:typeof skip==='string'?skip:null};}mw.loader={moduleRegistry:registry,maxQueryLength:2000,addStyleTag:newStyleTag,addScriptTag:addScript,addLinkTag:addLink,enqueue:enqueue,resolve:resolve,work:function(){store.init();var q=queue.length,storedImplementations=[],storedNames=[],requestNames=[],batch=new StringSet();while(q--){var module=queue[q];if(mw.loader.getState(module)==='registered'&&!batch.has(module)){registry[
module].state='loading';batch.add(module);var implementation=store.get(module);if(implementation){storedImplementations.push(implementation);storedNames.push(module);}else{requestNames.push(module);}}}queue=[];asyncEval(storedImplementations,function(err){store.stats.failed++;store.clear();mw.trackError('resourceloader.exception',{exception:err,source:'store-eval'});var failed=storedNames.filter(function(name){return registry[name].state==='loading';});batchRequest(failed);});batchRequest(requestNames);},addSource:function(ids){for(var id in ids){if(id in sources){throw new Error('source already registered: '+id);}sources[id]=ids[id];}},register:function(modules){if(typeof modules!=='object'){registerOne.apply(null,arguments);return;}function resolveIndex(dep){return typeof dep==='number'?modules[dep][0]:dep;}for(var i=0;i<modules.length;i++){var deps=modules[i][2];if(deps){for(var j=0;j<deps.length;j++){deps[j]=resolveIndex(deps[j]);}}registerOne.apply(null,modules[i]);}},implement:
function(module,script,style,messages,templates){var split=splitModuleKey(module),name=split.name,version=split.version;if(!(name in registry)){mw.loader.register(name);}if(registry[name].script!==undefined){throw new Error('module already implemented: '+name);}if(version){registry[name].version=version;}registry[name].script=script||null;registry[name].style=style||null;registry[name].messages=messages||null;registry[name].templates=templates||null;if(registry[name].state!=='error'&&registry[name].state!=='missing'){setAndPropagate(name,'loaded');}},load:function(modules,type){if(typeof modules==='string'&&/^(https?:)?\/?\//.test(modules)){if(type==='text/css'){addLink(modules);}else if(type==='text/javascript'||type===undefined){addScript(modules);}else{throw new Error('Invalid type '+type);}}else{modules=typeof modules==='string'?[modules]:modules;enqueue(resolveStubbornly(modules));}},state:function(states){for(var module in states){if(!(module in registry)){mw.loader.register(
module);}setAndPropagate(module,states[module]);}},getState:function(module){return module in registry?registry[module].state:null;},require:function(moduleName){if(mw.loader.getState(moduleName)!=='ready'){throw new Error('Module "'+moduleName+'" is not loaded');}return registry[moduleName].module.exports;}};var hasPendingWrites=false;function flushWrites(){store.prune();while(store.queue.length){store.set(store.queue.shift());}try{localStorage.removeItem(store.key);var data=JSON.stringify(store);localStorage.setItem(store.key,data);}catch(e){mw.trackError('resourceloader.exception',{exception:e,source:'store-localstorage-update'});}hasPendingWrites=false;}mw.loader.store=store={enabled:null,items:{},queue:[],stats:{hits:0,misses:0,expired:0,failed:0},toJSON:function(){return{items:store.items,vary:store.vary,asOf:Math.ceil(Date.now()/1e7)};},key:"MediaWikiModuleStore:mediawiki",vary:"minerva:1:en",init:function(){if(this.enabled===null){this.enabled=false;if(true){this.load();}else{
this.clear();}}},load:function(){try{var raw=localStorage.getItem(this.key);this.enabled=true;var data=JSON.parse(raw);if(data&&data.vary===this.vary&&data.items&&Date.now()<(data.asOf*1e7)+259e7){this.items=data.items;}}catch(e){}},get:function(module){if(this.enabled){var key=getModuleKey(module);if(key in this.items){this.stats.hits++;return this.items[key];}this.stats.misses++;}return false;},add:function(module){if(this.enabled){this.queue.push(module);this.requestUpdate();}},set:function(module){var args,encodedScript,descriptor=registry[module],key=getModuleKey(module);if(key in this.items||!descriptor||descriptor.state!=='ready'||!descriptor.version||descriptor.group===1||descriptor.group===0||[descriptor.script,descriptor.style,descriptor.messages,descriptor.templates].indexOf(undefined)!==-1){return;}try{if(typeof descriptor.script==='function'){encodedScript=String(descriptor.script);}else if(typeof descriptor.script==='object'&&descriptor.script&&!Array.isArray(descriptor.
script)){encodedScript='{'+'main:'+JSON.stringify(descriptor.script.main)+','+'files:{'+Object.keys(descriptor.script.files).map(function(file){var value=descriptor.script.files[file];return JSON.stringify(file)+':'+(typeof value==='function'?value:JSON.stringify(value));}).join(',')+'}}';}else{encodedScript=JSON.stringify(descriptor.script);}args=[JSON.stringify(key),encodedScript,JSON.stringify(descriptor.style),JSON.stringify(descriptor.messages),JSON.stringify(descriptor.templates)];}catch(e){mw.trackError('resourceloader.exception',{exception:e,source:'store-localstorage-json'});return;}var src='mw.loader.implement('+args.join(',')+');';if(src.length>1e5){return;}this.items[key]=src;},prune:function(){for(var key in this.items){if(getModuleKey(splitModuleKey(key).name)!==key){this.stats.expired++;delete this.items[key];}}},clear:function(){this.items={};try{localStorage.removeItem(this.key);}catch(e){}},requestUpdate:function(){if(!hasPendingWrites){hasPendingWrites=true;
setTimeout(function(){mw.requestIdleCallback(flushWrites);},2000);}}};}());mw.requestIdleCallbackInternal=function(callback){setTimeout(function(){var start=mw.now();callback({didTimeout:false,timeRemaining:function(){return Math.max(0,50-(mw.now()-start));}});},1);};mw.requestIdleCallback=window.requestIdleCallback?window.requestIdleCallback.bind(window):mw.requestIdleCallbackInternal;(function(){var queue;mw.loader.addSource({"local":"/load.php"});mw.loader.register([["site","1tu07",[1]],["site.styles","8qopz",[],2],["filepage","1ljys"],["user","1tdkc",[],0],["user.styles","18fec",[],0],["user.options","12s5i",[],1],["mediawiki.skinning.interface","ynfzk"],["jquery.makeCollapsible.styles","qx5d5"],["mediawiki.skinning.content.parsoid","rkmi6"],["jquery","p9z7x"],["es6-polyfills","1xwex",[],null,null,"return Array.prototype.find\u0026\u0026Array.prototype.findIndex\u0026\u0026Array.prototype.includes\u0026\u0026typeof Promise==='function'\u0026\u0026Promise.prototype.finally;"],[
"web2017-polyfills","5cxhc",[10],null,null,"return'IntersectionObserver'in window\u0026\u0026typeof fetch==='function'\u0026\u0026typeof URL==='function'\u0026\u0026'toJSON'in URL.prototype;"],["mediawiki.base","15aj3",[9]],["jquery.chosen","fjvzv"],["jquery.client","1jnox"],["jquery.color","1y5ur"],["jquery.confirmable","1qc1o",[109]],["jquery.cookie","emj1l"],["jquery.form","1djyv"],["jquery.fullscreen","1lanf"],["jquery.highlightText","a2wnf",[83]],["jquery.hoverIntent","1cahm"],["jquery.i18n","1pu0k",[108]],["jquery.lengthLimit","k5zgm",[67]],["jquery.makeCollapsible","1863g",[7,83]],["jquery.spinner","1rx3f",[26]],["jquery.spinner.styles","153wt"],["jquery.suggestions","1g6wh",[20]],["jquery.tablesorter","owtca",[29,110,83]],["jquery.tablesorter.styles","rwcx6"],["jquery.textSelection","m1do8",[14]],["jquery.throttle-debounce","1p2bq"],["jquery.tipsy","tl14d"],["jquery.ui","1h75h"],["moment","x1k6h",[106,83]],["vue","zfi8r!"],["@vue/composition-api","scw0q!",[35]],["vuex","1twvy!"
,[35]],["wvui","v4ef5!",[36]],["wvui-search","1nhzn!",[35]],["@wikimedia/codex","r6zyv!",[35]],["@wikimedia/codex-search","1p7vn!",[35]],["mediawiki.template","bca94"],["mediawiki.template.mustache","199kg",[42]],["mediawiki.apipretty","19n2s"],["mediawiki.api","4z1te",[73,109]],["mediawiki.content.json","9ryjh"],["mediawiki.confirmCloseWindow","1ewwa"],["mediawiki.debug","d8is9",[193]],["mediawiki.diff","paqy5"],["mediawiki.diff.styles","11490"],["mediawiki.feedback","dk4zz",[317,201]],["mediawiki.feedlink","1yq8n"],["mediawiki.filewarning","1brek",[193,205]],["mediawiki.ForeignApi","6vgsr",[55]],["mediawiki.ForeignApi.core","llzm2",[80,45,189]],["mediawiki.helplink","wjdrt"],["mediawiki.hlist","6en35"],["mediawiki.htmlform","1icg3",[23,83]],["mediawiki.htmlform.ooui","1m5pb",[193]],["mediawiki.htmlform.styles","1mdmd"],["mediawiki.htmlform.ooui.styles","t3imb"],["mediawiki.icon","17xpk"],["mediawiki.inspect","88qa7",[67,83]],["mediawiki.notification","1ksa6",[83,89]],[
"mediawiki.notification.convertmessagebox","1kd6x",[64]],["mediawiki.notification.convertmessagebox.styles","19vc0"],["mediawiki.String","1vc9s"],["mediawiki.pager.styles","eo2ge"],["mediawiki.pager.tablePager","1tupc"],["mediawiki.pulsatingdot","1i1zo"],["mediawiki.searchSuggest","e6v42",[27,45]],["mediawiki.storage","2gicm",[83]],["mediawiki.Title","1345o",[67,83]],["mediawiki.Upload","ooev2",[45]],["mediawiki.ForeignUpload","2bu58",[54,74]],["mediawiki.Upload.Dialog","198dv",[77]],["mediawiki.Upload.BookletLayout","178we",[74,81,34,196,201,206,207]],["mediawiki.ForeignStructuredUpload.BookletLayout","3n0xt",[75,77,113,172,166]],["mediawiki.toc","1jhap",[86]],["mediawiki.Uri","5izs0",[83]],["mediawiki.user","1fogn",[45,86]],["mediawiki.userSuggest","1hhzv",[27,45]],["mediawiki.util","51xco",[14,11]],["mediawiki.checkboxtoggle","159pl"],["mediawiki.checkboxtoggle.styles","1b0zv"],["mediawiki.cookie","1nfq0",[17]],["mediawiki.experiments","dhcyy"],["mediawiki.editfont.styles","l0cwg"],
["mediawiki.visibleTimeout","xcitq"],["mediawiki.action.delete","1ssul",[23,193]],["mediawiki.action.edit","108tk",[30,92,45,88,168]],["mediawiki.action.edit.styles","h1ysc"],["mediawiki.action.edit.collapsibleFooter","za3yf",[24,62,72]],["mediawiki.action.edit.preview","1kz6y",[25,119,81]],["mediawiki.action.history","cpbx3",[24]],["mediawiki.action.history.styles","qumyi"],["mediawiki.action.protect","1dt0w",[23,193]],["mediawiki.action.view.metadata","13p0w",[104]],["mediawiki.action.view.categoryPage.styles","acp5g"],["mediawiki.action.view.postEdit","13vzn",[109,64,193,212]],["mediawiki.action.view.redirect","iqcjx"],["mediawiki.action.view.redirectPage","x5z6j"],["mediawiki.action.edit.editWarning","ihdqq",[30,47,109]],["mediawiki.action.view.filepage","mbna9"],["mediawiki.action.styles","g8x3w"],["mediawiki.language","1ysaw",[107]],["mediawiki.cldr","w8zqb",[108]],["mediawiki.libs.pluralruleparser","1kwne"],["mediawiki.jqueryMsg","d02ut",[67,106,83,5]],[
"mediawiki.language.months","1iag2",[106]],["mediawiki.language.names","159lr",[106]],["mediawiki.language.specialCharacters","f8zox",[106]],["mediawiki.libs.jpegmeta","1h4oh"],["mediawiki.page.gallery","19ugl",[115,83]],["mediawiki.page.gallery.styles","mpxkc"],["mediawiki.page.gallery.slideshow","1f4yv",[45,196,215,217]],["mediawiki.page.ready","1fl2l",[45]],["mediawiki.page.watch.ajax","45qm7",[45]],["mediawiki.page.preview","8a65o",[24,30,45,49,50,193]],["mediawiki.page.image.pagination","kn7b4",[25,83]],["mediawiki.rcfilters.filters.base.styles","a1cd8"],["mediawiki.rcfilters.highlightCircles.seenunseen.styles","13n9y"],["mediawiki.rcfilters.filters.ui","1jah8",[24,80,81,163,202,209,211,212,213,215,216]],["mediawiki.interface.helpers.styles","1vlxc"],["mediawiki.special","1ogii"],["mediawiki.special.apisandbox","10nj8",[24,80,183,169,192]],["mediawiki.special.block","1n3h1",[58,166,182,173,183,180,209]],["mediawiki.misc-authed-ooui","1iw6h",[59,163,168]],[
"mediawiki.misc-authed-pref","16eja",[5]],["mediawiki.misc-authed-curate","1vp4k",[16,25,45]],["mediawiki.special.changeslist","wkqyl"],["mediawiki.special.changeslist.watchlistexpiry","1tnj7",[125,212]],["mediawiki.special.changeslist.enhanced","1kflq"],["mediawiki.special.changeslist.legend","1woh0"],["mediawiki.special.changeslist.legend.js","qa88i",[24,86]],["mediawiki.special.contributions","1luqq",[24,109,166,192]],["mediawiki.special.edittags","79img",[13,23]],["mediawiki.special.import.styles.ooui","1hzv9"],["mediawiki.special.changecredentials","f9fqt"],["mediawiki.special.changeemail","10bxu"],["mediawiki.special.preferences.ooui","17q0e",[47,88,65,72,173,168]],["mediawiki.special.preferences.styles.ooui","ithcr"],["mediawiki.special.revisionDelete","13kw3",[23]],["mediawiki.special.search","11pp3",[185]],["mediawiki.special.search.commonsInterwikiWidget","e3z5z",[80,45]],["mediawiki.special.search.interwikiwidget.styles","cxv8q"],["mediawiki.special.search.styles","1da08"],[
"mediawiki.special.unwatchedPages","mk9s7",[45]],["mediawiki.special.upload","1kaju",[25,45,47,113,125,42]],["mediawiki.special.userlogin.common.styles","1bg87"],["mediawiki.special.userlogin.login.styles","1w9oo"],["mediawiki.special.createaccount","mbk5h",[45]],["mediawiki.special.userlogin.signup.styles","2q1sd"],["mediawiki.special.userrights","4k0n6",[23,65]],["mediawiki.special.watchlist","lr1n3",[45,193,212]],["mediawiki.ui","14249"],["mediawiki.ui.checkbox","14jwt"],["mediawiki.ui.radio","p7ytf"],["mediawiki.ui.anchor","12ret"],["mediawiki.ui.button","wtog9"],["mediawiki.ui.input","1rl9q"],["mediawiki.ui.icon","16dbb"],["mediawiki.widgets","7oz2w",[45,164,196,206,207]],["mediawiki.widgets.styles","1x5du"],["mediawiki.widgets.AbandonEditDialog","1tcrg",[201]],["mediawiki.widgets.DateInputWidget","1axcu",[167,34,196,217]],["mediawiki.widgets.DateInputWidget.styles","5tutn"],["mediawiki.widgets.visibleLengthLimit","m325n",[23,193]],["mediawiki.widgets.datetime","1l69a",[83,193,212
,216,217]],["mediawiki.widgets.expiry","m5uji",[169,34,196]],["mediawiki.widgets.CheckMatrixWidget","k9si1",[193]],["mediawiki.widgets.CategoryMultiselectWidget","x4tey",[54,196]],["mediawiki.widgets.SelectWithInputWidget","yzuek",[174,196]],["mediawiki.widgets.SelectWithInputWidget.styles","vkr7h"],["mediawiki.widgets.SizeFilterWidget","1hmr4",[176,196]],["mediawiki.widgets.SizeFilterWidget.styles","ceybj"],["mediawiki.widgets.MediaSearch","1y1s4",[54,81,196]],["mediawiki.widgets.Table","p2qhh",[196]],["mediawiki.widgets.TagMultiselectWidget","1erse",[196]],["mediawiki.widgets.UserInputWidget","jsk5k",[45,196]],["mediawiki.widgets.UsersMultiselectWidget","1m6vb",[45,196]],["mediawiki.widgets.NamespacesMultiselectWidget","pwj2l",[196]],["mediawiki.widgets.TitlesMultiselectWidget","gt95w",[163]],["mediawiki.widgets.TagMultiselectWidget.styles","1rjw4"],["mediawiki.widgets.SearchInputWidget","z70j2",[71,163,212]],["mediawiki.widgets.SearchInputWidget.styles","9327p"],[
"mediawiki.watchstar.widgets","1gkq3",[192]],["mediawiki.deflate","1ci7b"],["oojs","ewqeo"],["mediawiki.router","1ugrh",[191]],["oojs-router","m96yy",[189]],["oojs-ui","1jh3r",[199,196,201]],["oojs-ui-core","oyf3b",[106,189,195,194,203]],["oojs-ui-core.styles","19gxh"],["oojs-ui-core.icons","19qet"],["oojs-ui-widgets","1mfko",[193,198]],["oojs-ui-widgets.styles","12mn2"],["oojs-ui-widgets.icons","vc073"],["oojs-ui-toolbars","192gr",[193,200]],["oojs-ui-toolbars.icons","1jmmd"],["oojs-ui-windows","oqdww",[193,202]],["oojs-ui-windows.icons","12dhb"],["oojs-ui.styles.indicators","yfkub"],["oojs-ui.styles.icons-accessibility","t4iye"],["oojs-ui.styles.icons-alerts","17im0"],["oojs-ui.styles.icons-content","11m2w"],["oojs-ui.styles.icons-editing-advanced","1trwq"],["oojs-ui.styles.icons-editing-citation","1296s"],["oojs-ui.styles.icons-editing-core","1tew4"],["oojs-ui.styles.icons-editing-list","515b3"],["oojs-ui.styles.icons-editing-styling","14wuu"],["oojs-ui.styles.icons-interactions",
"1q3hv"],["oojs-ui.styles.icons-layout","frn82"],["oojs-ui.styles.icons-location","2txm8"],["oojs-ui.styles.icons-media","106e0"],["oojs-ui.styles.icons-moderation","14h31"],["oojs-ui.styles.icons-movement","h4324"],["oojs-ui.styles.icons-user","xjws1"],["oojs-ui.styles.icons-wikimedia","hyzxo"],["skins.minerva.base.styles","dxur8"],["skins.minerva.content.styles.images","1uytw"],["skins.minerva.icons.loggedin","1497x"],["skins.minerva.amc.styles","1k6o7"],["skins.minerva.overflow.icons","1lb5i"],["skins.minerva.icons.wikimedia","udfng"],["skins.minerva.icons.images.scripts.misc","1e3nv"],["skins.minerva.icons.page.issues.uncolored","150iu"],["skins.minerva.icons.page.issues.default.color","11o5l"],["skins.minerva.icons.page.issues.medium.color","p0ryz"],["skins.minerva.mainPage.styles","1i98b"],["skins.minerva.userpage.styles","ted72"],["skins.minerva.talk.styles","1umpv"],["skins.minerva.personalMenu.icons","q8ykg"],["skins.minerva.mainMenu.advanced.icons","q7k3r"],[
"skins.minerva.mainMenu.icons","1a2s8"],["skins.minerva.mainMenu.styles","1xyu9"],["skins.minerva.loggedin.styles","en17r"],["skins.minerva.scripts","1w41y",[80,87,159,274,226,228,229,227,235,236,239]],["skins.minerva.messageBox.styles","19ygu"],["skins.minerva.categories.styles","bcumo"],["skins.hypixelneue.styles","1o8lw"],["skins.hypixelneue.styles.fonts","17d8j"],["skins.hypixelneue.tabber.styles","1fo75"],["skins.hypixelneue.spritesheet.styles","1fcl5"],["skins.hypixelneue.scripts","191up"],["skins.hypixelneue.tabber.scripts","vjnmi"],["skins.hypixelneue.togglelist.scripts","4oi5l"],["skins.hypixelneue.spritesheet.scripts","1bdbn"],["ext.scribunto.errors","s78x0",[33]],["ext.scribunto.logs","c053i"],["ext.scribunto.edit","pr9mn",[25,45]],["ext.wikiEditor","1pc38",[30,33,112,81,163,208,209,210,211,215,42],3],["ext.wikiEditor.styles","rlj9c",[],3],["ext.wikiEditor.images","1mrgd"],["ext.wikiEditor.realtimepreview","1w5xs",[252,254,119,70,72,212]],["ext.codeEditor","1ma6m",[257],3],[
"jquery.codeEditor","17931",[259,258,252,201],3],["ext.codeEditor.icons","zxxxc"],["ext.codeEditor.ace","1qm3g",[],4],["ext.codeEditor.ace.modes","nnxj1",[259],4],["ext.cirrus.serp","jrrue",[80,190]],["ext.cirrus.explore-similar","1tlj9",[45,43]],["ext.oath.totp.showqrcode","1gdkt"],["ext.oath.totp.showqrcode.styles","16j3z"],["mobile.pagelist.styles","11rd0"],["mobile.pagesummary.styles","y26or"],["mobile.placeholder.images","1jw20"],["mobile.userpage.styles","1uooy"],["mobile.startup.images","g7xjz"],["mobile.init.styles","1yn0v"],["mobile.init","1qxre",[80,274]],["mobile.ooui.icons","1ro7a"],["mobile.user.icons","pao7b"],["mobile.startup","s0s6h",[118,190,72,43,160,162,81,272,265,266,267,269]],["mobile.editor.overlay","kzcg3",[47,88,64,161,165,276,274,273,192,209]],["mobile.editor.images","1jvnr"],["mobile.talk.overlays","1bu0i",[159,275]],["mobile.mediaViewer","1uhdl",[274]],["mobile.languages.structured","xq1pz",[274]],["mobile.special.mobileoptions.styles","n8f4t"],[
"mobile.special.mobileoptions.scripts","12rxl",[274]],["mobile.special.nearby.styles","1gtae"],["mobile.special.userlogin.scripts","19ke0"],["mobile.special.nearby.scripts","15fmh",[80,282,274]],["mobile.special.mobilediff.images","1vemu"],["ext.interwiki.specialpage","lsm82"],["ext.math.styles","1esxo"],["ext.math.scripts","tzadd"],["mw.widgets.MathWbEntitySelector","zc14e",[54,163,"mw.config.values.wbRepo",201]],["ext.math.visualEditor","1395j",[287,"ext.visualEditor.mwcore",207]],["ext.math.visualEditor.mathSymbolsData","ltjso",[290]],["ext.math.visualEditor.mathSymbols","18eu7",[291]],["ext.math.visualEditor.chemSymbolsData","ar9ku",[290]],["ext.math.visualEditor.chemSymbols","s750r",[293]],["ext.categoryTree","1j302",[45]],["ext.categoryTree.styles","1d80w"],["ext.inputBox.styles","1dv4m"],["ext.youtube.lazyload","16y1w"],["ext.cite.styles","1qz7m"],["ext.cite.style","6t36z"],["ext.cite.visualEditor.core","4m7e0",["ext.visualEditor.mwcore","ext.visualEditor.mwtransclusion"]],[
"ext.cite.visualEditor","s3t01",[300,299,301,"ext.visualEditor.base","ext.visualEditor.mediawiki",205,208,212]],["ext.cite.ux-enhancements","14f0k"],["ext.CodeMirror","1hni7",[305,30,33,81,211]],["ext.CodeMirror.data","1bn5s"],["ext.CodeMirror.lib","4t9ku"],["ext.CodeMirror.addons","1s5sd",[306]],["ext.CodeMirror.mode.mediawiki","11jkn",[306]],["ext.CodeMirror.lib.mode.css","ri6yn",[306]],["ext.CodeMirror.lib.mode.javascript","tkjyf",[306]],["ext.CodeMirror.lib.mode.xml","lulkh",[306]],["ext.CodeMirror.lib.mode.htmlmixed","55n3v",[309,310,311]],["ext.CodeMirror.lib.mode.clike","x6dn7",[306]],["ext.CodeMirror.lib.mode.php","d3qbf",[313,312]],["ext.CodeMirror.visualEditor.init","2cue5"],["ext.CodeMirror.visualEditor","1izb0",["ext.visualEditor.mwcore",45]],["mediawiki.messagePoster","13b1w",[54]]]);mw.config.set(window.RLCONF||{});mw.loader.state(window.RLSTATE||{});mw.loader.load(window.RLPAGEMODULES||[]);queue=window.RLQ||[];RLQ=[];RLQ.push=function(fn){if(typeof fn==='function'){fn();
}else{RLQ[RLQ.length]=fn;}};while(queue[0]){RLQ.push(queue.shift());}NORLQ={push:function(){}};}());}
