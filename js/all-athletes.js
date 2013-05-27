(function(c,d){function f(d,c){for(var r=decodeURI(d),r=G[c?"strict":"loose"].exec(r),f={attr:{},param:{},seg:{}},h=14;h--;)f.attr[H[h]]=r[h]||"";f.param.query={};f.param.fragment={};f.attr.query.replace(C,function(d,c,r){c&&(f.param.query[c]=r)});f.attr.fragment.replace(v,function(d,c,r){c&&(f.param.fragment[c]=r)});f.seg.path=f.attr.path.replace(/^\/+|\/+$/g,"").split("/");f.seg.fragment=f.attr.fragment.replace(/^\/+|\/+$/g,"").split("/");f.attr.base=f.attr.host?f.attr.protocol+"://"+f.attr.host+
(f.attr.port?":"+f.attr.port:""):"";return f}function h(c){c=c.tagName;return c!==d?I[c.toLowerCase()]:c}var I={a:"href",img:"src",form:"action",base:"href",script:"src",iframe:"src",link:"href"},H="source,protocol,authority,userInfo,user,password,host,port,relative,path,directory,file,query,fragment".split(","),y={anchor:"fragment"},G={strict:/^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,loose:/^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/},
C=/(?:^|&|;)([^&=;]*)=?([^&;]*)/g,v=/(?:^|&|;)([^&=;]*)=?([^&;]*)/g;c.fn.url=function(d){var f="";this.length&&(f=c(this).attr(h(this[0]))||"");return c.url(f,d)};c.url=function(c,h){1===arguments.length&&!0===c&&(h=!0,c=d);c=c||window.location.toString();return{data:f(c,h||!1),attr:function(c){c=y[c]||c;return c!==d?this.data.attr[c]:this.data.attr},param:function(c){return c!==d?this.data.param.query[c]:this.data.param.query},fparam:function(c){return c!==d?this.data.param.fragment[c]:this.data.param.fragment},
segment:function(c){if(c===d)return this.data.seg.path;c=0>c?this.data.seg.path.length+c:c-1;return this.data.seg.path[c]},fsegment:function(c){if(c===d)return this.data.seg.fragment;c=0>c?this.data.seg.fragment.length+c:c-1;return this.data.seg.fragment[c]}}}})(jQuery);(function(c,d,f,h,I,H){function y(a,b){var e=typeof a[b];return"function"==e||!!("object"==e&&a[b])||"unknown"==e}function G(){try{var a=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");J=Array.prototype.slice.call(a.GetVariable("$version").match(/(\d+),(\d+),(\d+),(\d+)/),1);P=9<parseInt(J[0],10)&&0<parseInt(J[1],10);return!0}catch(b){return!1}}function C(){if(!A){A=!0;for(var a=0;a<K.length;a++)K[a]();K.length=0}}function v(a,b){A?a.call(b):K.push(function(){a.call(b)})}function Z(){var a=parent;
if(""!==E)for(var b=0,e=E.split(".");b<e.length;b++)a=a[e[b]];return a.easyXDM}function o(a){var b=a.toLowerCase().match(L),a=b[2],e=b[3],b=b[4]||"";if("http:"==a&&":80"==b||"https:"==a&&":443"==b)b="";return a+"//"+e+b}function r(a){a=a.replace($,"$1/");if(!a.match(/^(http||https):\/\//)){var b="/"===a.substring(0,1)?"":f.pathname;"/"!==b.substring(b.length-1)&&(b=b.substring(0,b.lastIndexOf("/")+1));a=f.protocol+"//"+f.host+b+a}for(;U.test(a);)a=a.replace(U,"");return a}function B(a,b){var e="",
l=a.indexOf("#");-1!==l&&(e=a.substring(l),a=a.substring(0,l));var l=[],c;for(c in b)b.hasOwnProperty(c)&&l.push(c+"="+H(b[c]));return a+(V?"#":-1==a.indexOf("?")?"?":"&")+l.join("&")+e}function z(a){return"undefined"===typeof a}function p(a,b,e){var c,d;for(d in b)b.hasOwnProperty(d)&&(d in a?(c=b[d],"object"===typeof c?p(a[d],c,e):e||(a[d]=b[d])):a[d]=b[d]);return a}function x(a){if(z(Q)){var b=d.body.appendChild(d.createElement("form")),e=b.appendChild(d.createElement("input"));e.name=t+"TEST"+
W;Q=e!==b.elements[e.name];d.body.removeChild(b)}Q?b=d.createElement('<iframe name="'+a.props.name+'"/>'):(b=d.createElement("IFRAME"),b.name=a.props.name);b.id=b.name=a.props.name;delete a.props.name;a.onLoad&&u(b,"load",a.onLoad);if("string"==typeof a.container)a.container=d.getElementById(a.container);if(!a.container)p(b.style,{position:"absolute",top:"-2000px"}),a.container=d.body;e=a.props.src;delete a.props.src;p(b,a.props);b.border=b.frameBorder=0;b.allowTransparency=!0;a.container.appendChild(b);
b.src=e;a.props.src=e;return b}function T(a){var b=a.protocol,e;a.isHost=a.isHost||z(s.xdm_p);V=a.hash||!1;if(!a.props)a.props={};if(a.isHost){if(a.remote=r(a.remote),a.channel=a.channel||"default"+W++,a.secret=Math.random().toString(16).substring(2),z(b))o(f.href)==o(a.remote)?b="4":y(c,"postMessage")||y(d,"postMessage")?b="1":a.swf&&y(c,"ActiveXObject")&&G()?b="6":"Gecko"===navigator.product&&"frameElement"in c&&-1==navigator.userAgent.indexOf("WebKit")?b="5":a.remoteHelper?(a.remoteHelper=r(a.remoteHelper),
b="2"):b="0"}else{a.channel=s.xdm_c;a.secret=s.xdm_s;a.remote=s.xdm_e;var b=s.xdm_p,l;if(l=a.acl){a:{l=a.acl;var q=a.remote;"string"==typeof l&&(l=[l]);for(var i,k=l.length;k--;)if(i=l[k],i=RegExp("^"==i.substr(0,1)?i:"^"+i.replace(/(\*)/g,".$1").replace(/\?/g,".")+"$"),i.test(q)){l=!0;break a}l=!1}l=!l}if(l)throw Error("Access denied for "+a.remote);}a.protocol=b;switch(b){case "0":p(a,{interval:100,delay:2E3,useResize:!0,useParent:!1,usePolling:!1},!0);if(a.isHost){if(!a.local){b=f.protocol+"//"+
f.host;e=d.body.getElementsByTagName("img");for(q=e.length;q--;)if(l=e[q],l.src.substring(0,b.length)===b){a.local=l.src;break}if(!a.local)a.local=c}b={xdm_c:a.channel,xdm_p:0};a.local===c?(a.usePolling=!0,a.useParent=!0,a.local=f.protocol+"//"+f.host+f.pathname+f.search,b.xdm_e=a.local,b.xdm_pa=1):b.xdm_e=r(a.local);if(a.container)a.useResize=!1,b.xdm_po=1;a.remote=B(a.remote,b)}else p(a,{channel:s.xdm_c,remote:s.xdm_e,useParent:!z(s.xdm_pa),usePolling:!z(s.xdm_po),useResize:a.useParent?!1:a.useResize});
e=[new g.stack.HashTransport(a),new g.stack.ReliableBehavior({}),new g.stack.QueueBehavior({encode:!0,maxLength:4E3-a.remote.length}),new g.stack.VerifyBehavior({initiate:a.isHost})];break;case "1":e=[new g.stack.PostMessageTransport(a)];break;case "2":e=[new g.stack.NameTransport(a),new g.stack.QueueBehavior,new g.stack.VerifyBehavior({initiate:a.isHost})];break;case "3":e=[new g.stack.NixTransport(a)];break;case "4":e=[new g.stack.SameOriginTransport(a)];break;case "5":e=[new g.stack.FrameElementTransport(a)];
break;case "6":J||G(),e=[new g.stack.FlashTransport(a)]}e.push(new g.stack.QueueBehavior({lazy:a.lazy,remove:!0}));return e}function X(a){for(var b,e={incoming:function(a,b){this.up.incoming(a,b)},outgoing:function(a,b){this.down.outgoing(a,b)},callback:function(a){this.up.callback(a)},init:function(){this.down.init()},destroy:function(){this.down.destroy()}},c=0,d=a.length;c<d;c++){b=a[c];p(b,e,!0);if(0!==c)b.down=a[c-1];if(c!==d-1)b.up=a[c+1]}return b}function aa(a){a.up.down=a.down;a.down.up=a.up;
a.up=a.down=null}var M=this,W=Math.floor(1E4*Math.random()),R=Function.prototype,L=/^((http.?:)\/\/([^:\/\s]+)(:\d+)*)/,U=/[\-\w]+\/\.\.\//,$=/([^:])\/\//g,E="",g={},ba=c.easyXDM,t="easyXDM_",Q,V=!1,J,P,u,D;if(y(c,"addEventListener"))u=function(a,b,e){a.addEventListener(b,e,!1)},D=function(a,b,e){a.removeEventListener(b,e,!1)};else if(y(c,"attachEvent"))u=function(a,b,e){a.attachEvent("on"+b,e)},D=function(a,b,e){a.detachEvent("on"+b,e)};else throw Error("Browser not supported");var A=!1,K=[],N;"readyState"in
d?(N=d.readyState,A="complete"==N||~navigator.userAgent.indexOf("AppleWebKit/")&&("loaded"==N||"interactive"==N)):A=!!d.body;if(!A){if(y(c,"addEventListener"))u(d,"DOMContentLoaded",C);else if(u(d,"readystatechange",function(){"complete"==d.readyState&&C()}),d.documentElement.doScroll&&c===top){var Y=function(){if(!A){try{d.documentElement.doScroll("left")}catch(a){h(Y,1);return}C()}};Y()}u(c,"load",C)}var s=function(a){for(var a=a.substring(1).split("&"),b={},e,c=a.length;c--;)e=a[c].split("="),
b[e[0]]=I(e[1]);return b}(/xdm_e=/.test(f.search)?f.search:f.hash),S=function(){var a={},b={a:[1,2,3]};if("undefined"!=typeof JSON&&"function"===typeof JSON.stringify&&'{"a":[1,2,3]}'===JSON.stringify(b).replace(/\s/g,""))return JSON;if(Object.toJSON&&'{"a":[1,2,3]}'===Object.toJSON(b).replace(/\s/g,""))a.stringify=Object.toJSON;if("function"===typeof String.prototype.evalJSON&&(b='{"a":[1,2,3]}'.evalJSON(),b.a&&3===b.a.length&&3===b.a[2]))a.parse=function(a){return a.evalJSON()};return a.stringify&&
a.parse?(S=function(){return a},a):null};p(g,{version:"2.4.15.118",query:s,stack:{},apply:p,getJSONObject:S,whenReady:v,noConflict:function(a){c.easyXDM=ba;(E=a)&&(t="easyXDM_"+E.replace(".","_")+"_");return g}});g.DomHelper={on:u,un:D,requiresJSON:function(a){"object"==typeof c.JSON&&c.JSON||d.write('<script type="text/javascript" src="'+a+'"><\/script>')}};(function(){var a={};g.Fn={set:function(b,e){a[b]=e},get:function(b,e){var c=a[b];e&&delete a[b];return c}}})();g.Socket=function(a){var b=X(T(a).concat([{incoming:function(b,
e){a.onMessage(b,e)},callback:function(b){if(a.onReady)a.onReady(b)}}])),e=o(a.remote);this.origin=o(a.remote);this.destroy=function(){b.destroy()};this.postMessage=function(a){b.outgoing(a,e)};b.init()};g.Rpc=function(a,b){if(b.local)for(var e in b.local)if(b.local.hasOwnProperty(e)){var c=b.local[e];"function"===typeof c&&(b.local[e]={method:c})}var d=X(T(a).concat([new g.stack.RpcBehavior(this,b),{callback:function(b){if(a.onReady)a.onReady(b)}}]));this.origin=o(a.remote);this.destroy=function(){d.destroy()};
d.init()};g.stack.SameOriginTransport=function(a){var b,e,c,d;return b={outgoing:function(a,b,e){c(a);e&&e()},destroy:function(){e&&(e.parentNode.removeChild(e),e=null)},onDOMReady:function(){d=o(a.remote);a.isHost?(p(a.props,{src:B(a.remote,{xdm_e:f.protocol+"//"+f.host+f.pathname,xdm_c:a.channel,xdm_p:4}),name:t+a.channel+"_provider"}),e=x(a),g.Fn.set(a.channel,function(a){c=a;h(function(){b.up.callback(!0)},0);return function(a){b.up.incoming(a,d)}})):(c=Z().Fn.get(a.channel,!0)(function(a){b.up.incoming(a,
d)}),h(function(){b.up.callback(!0)},0))},init:function(){v(b.onDOMReady,b)}}};g.stack.FlashTransport=function(a){function b(a){h(function(){c.up.incoming(a,i)},0)}function e(b){var c=a.swf+"?host="+a.isHost,e="easyXDM_swf_"+Math.floor(1E4*Math.random());g.Fn.set("flash_loaded"+b.replace(/[\-.]/g,"_"),function(){g.stack.FlashTransport[b].swf=k=j.firstChild;for(var a=g.stack.FlashTransport[b].queue,c=0;c<a.length;c++)a[c]();a.length=0});a.swfContainer?j="string"==typeof a.swfContainer?d.getElementById(a.swfContainer):
a.swfContainer:(j=d.createElement("div"),p(j.style,P&&a.swfNoThrottle?{height:"20px",width:"20px",position:"fixed",right:0,top:0}:{height:"1px",width:"1px",position:"absolute",overflow:"hidden",right:0,top:0}),d.body.appendChild(j));var i="callback=flash_loaded"+b.replace(/[\-.]/g,"_")+"&proto="+M.location.protocol+"&domain="+M.location.href.match(L)[3]+"&port="+(M.location.href.match(L)[4]||"")+"&ns="+E;j.innerHTML="<object height='20' width='20' type='application/x-shockwave-flash' id='"+e+"' data='"+
c+"'><param name='allowScriptAccess' value='always'></param><param name='wmode' value='transparent'><param name='movie' value='"+c+"'></param><param name='flashvars' value='"+i+"'></param><embed type='application/x-shockwave-flash' FlashVars='"+i+"' allowScriptAccess='always' wmode='transparent' src='"+c+"' height='1' width='1'></embed></object>"}var c,q,i,k,j;return c={outgoing:function(b,c,e){k.postMessage(a.channel,b.toString());e&&e()},destroy:function(){try{k.destroyChannel(a.channel)}catch(b){}k=
null;q&&(q.parentNode.removeChild(q),q=null)},onDOMReady:function(){i=a.remote;g.Fn.set("flash_"+a.channel+"_init",function(){h(function(){c.up.callback(!0)})});g.Fn.set("flash_"+a.channel+"_onMessage",b);a.swf=r(a.swf);var d=a.swf.match(L)[3],m=function(){g.stack.FlashTransport[d].init=!0;k=g.stack.FlashTransport[d].swf;k.createChannel(a.channel,a.secret,o(a.remote),a.isHost);a.isHost&&(P&&a.swfNoThrottle&&p(a.props,{position:"fixed",right:0,top:0,height:"20px",width:"20px"}),p(a.props,{src:B(a.remote,
{xdm_e:o(f.href),xdm_c:a.channel,xdm_p:6,xdm_s:a.secret}),name:t+a.channel+"_provider"}),q=x(a))};g.stack.FlashTransport[d]&&g.stack.FlashTransport[d].init?m():g.stack.FlashTransport[d]?g.stack.FlashTransport[d].queue.push(m):(g.stack.FlashTransport[d]={queue:[m]},e(d))},init:function(){v(c.onDOMReady,c)}}};g.stack.PostMessageTransport=function(a){function b(b){var c;if(b.origin)c=o(b.origin);else if(b.uri)c=o(b.uri);else if(b.domain)c=f.protocol+"//"+b.domain;else throw"Unable to retrieve the origin of the event";
c==i&&b.data.substring(0,a.channel.length+1)==a.channel+" "&&e.up.incoming(b.data.substring(a.channel.length+1),c)}var e,d,g,i;return e={outgoing:function(b,c,e){g.postMessage(a.channel+" "+b,c||i);e&&e()},destroy:function(){D(c,"message",b);d&&(g=null,d.parentNode.removeChild(d),d=null)},onDOMReady:function(){i=o(a.remote);if(a.isHost){var k=function(i){i.data==a.channel+"-ready"&&(g="postMessage"in d.contentWindow?d.contentWindow:d.contentWindow.document,D(c,"message",k),u(c,"message",b),h(function(){e.up.callback(!0)},
0))};u(c,"message",k);p(a.props,{src:B(a.remote,{xdm_e:o(f.href),xdm_c:a.channel,xdm_p:1}),name:t+a.channel+"_provider"});d=x(a)}else u(c,"message",b),g="postMessage"in c.parent?c.parent:c.parent.document,g.postMessage(a.channel+"-ready",i),h(function(){e.up.callback(!0)},0)},init:function(){v(e.onDOMReady,e)}}};g.stack.FrameElementTransport=function(a){var b,e,g,q;return b={outgoing:function(a,b,c){g.call(this,a);c&&c()},destroy:function(){e&&(e.parentNode.removeChild(e),e=null)},onDOMReady:function(){q=
o(a.remote);if(a.isHost)p(a.props,{src:B(a.remote,{xdm_e:o(f.href),xdm_c:a.channel,xdm_p:5}),name:t+a.channel+"_provider"}),e=x(a),e.fn=function(a){delete e.fn;g=a;h(function(){b.up.callback(!0)},0);return function(a){b.up.incoming(a,q)}};else{if(d.referrer&&o(d.referrer)!=s.xdm_e)c.top.location=s.xdm_e;g=c.frameElement.fn(function(a){b.up.incoming(a,q)});b.up.callback(!0)}},init:function(){v(b.onDOMReady,b)}}};g.stack.NameTransport=function(a){function b(b){j.contentWindow.sendMessage(b,a.remoteHelper+
(k?"#_3":"#_2")+a.channel)}function c(){k?(2===++m||!k)&&i.up.callback(!0):(b("ready"),i.up.callback(!0))}function d(a){i.up.incoming(a,F)}function f(){w&&h(function(){w(!0)},0)}var i,k,j,n,m,w,F,O;return i={outgoing:function(a,c,e){w=e;b(a)},destroy:function(){j.parentNode.removeChild(j);j=null;k&&(n.parentNode.removeChild(n),n=null)},onDOMReady:function(){k=a.isHost;m=0;F=o(a.remote);a.local=r(a.local);k?(g.Fn.set(a.channel,function(b){k&&"ready"===b&&(g.Fn.set(a.channel,d),c())}),O=B(a.remote,
{xdm_e:a.local,xdm_c:a.channel,xdm_p:2}),p(a.props,{src:O+"#"+a.channel,name:t+a.channel+"_provider"}),n=x(a)):(a.remoteHelper=a.remote,g.Fn.set(a.channel,d));j=x({props:{src:a.local+"#_4"+a.channel},onLoad:function da(){var b=j||this;D(b,"load",da);g.Fn.set(a.channel+"_load",f);(function fa(){"function"==typeof b.contentWindow.sendMessage?c():h(fa,50)})()}})},init:function(){v(i.onDOMReady,i)}}};g.stack.HashTransport=function(a){function b(){if(n){var a=n.location.href,b="",c=a.indexOf("#");-1!=
c&&(b=a.substring(c));b&&b!=f&&(f=b,e.up.incoming(f.substring(f.indexOf("_")+1),F))}}var e,d,g,i,f,j,n,m,w,F;return e={outgoing:function(b){if(m)b=a.remote+"#"+j++ +"_"+b,(d||!w?m.contentWindow:m).location=b},destroy:function(){c.clearInterval(g);(d||!w)&&m.parentNode.removeChild(m);m=null},onDOMReady:function(){d=a.isHost;i=a.interval;f="#"+a.channel;j=0;w=a.useParent;F=o(a.remote);if(d){a.props={src:a.remote,name:t+a.channel+"_provider"};if(w)a.onLoad=function(){n=c;g=setInterval(b,i);e.up.callback(!0)};
else{var O=0,ca=a.delay/50;(function ea(){if(++O>ca)throw Error("Unable to reference listenerwindow");try{n=m.contentWindow.frames[t+a.channel+"_consumer"]}catch(c){}n?(g=setInterval(b,i),e.up.callback(!0)):h(ea,50)})()}m=x(a)}else n=c,g=setInterval(b,i),w?(m=parent,e.up.callback(!0)):(p(a,{props:{src:a.remote+"#"+a.channel+new Date,name:t+a.channel+"_consumer"},onLoad:function(){e.up.callback(!0)}}),m=x(a))},init:function(){v(e.onDOMReady,e)}}};g.stack.ReliableBehavior=function(){var a,b,c=0,d=0,
g="";return a={incoming:function(i,f){var j=i.indexOf("_"),h=i.substring(0,j).split(","),i=i.substring(j+1);h[0]==c&&(g="",b&&b(!0));0<i.length&&(a.down.outgoing(h[1]+","+c+"_"+g,f),d!=h[1]&&(d=h[1],a.up.incoming(i,f)))},outgoing:function(f,h,j){g=f;b=j;a.down.outgoing(d+","+ ++c+"_"+f,h)}}};g.stack.QueueBehavior=function(a){function b(){if(a.remove&&0===d.length)aa(c);else if(!f&&!(0===d.length||k)){f=!0;var g=d.shift();c.down.outgoing(g.data,g.origin,function(a){f=!1;g.callback&&h(function(){g.callback(a)},
0);b()})}}var c,d=[],f=!0,g="",k,j=0,n=!1,m=!1;return c={init:function(){z(a)&&(a={});if(a.maxLength)j=a.maxLength,m=!0;a.lazy?n=!0:c.down.init()},callback:function(a){f=!1;var d=c.up;b();d.callback(a)},incoming:function(b,d){if(m){var f=b.indexOf("_"),h=parseInt(b.substring(0,f),10);g+=b.substring(f+1);0===h&&(a.encode&&(g=I(g)),c.up.incoming(g,d),g="")}else c.up.incoming(b,d)},outgoing:function(g,f,i){a.encode&&(g=H(g));var h=[],k;if(m){for(;0!==g.length;)k=g.substring(0,j),g=g.substring(k.length),
h.push(k);for(;k=h.shift();)d.push({data:h.length+"_"+k,origin:f,callback:0===h.length?i:null})}else d.push({data:g,origin:f,callback:i});n?c.down.init():b()},destroy:function(){k=!0;c.down.destroy()}}};g.stack.VerifyBehavior=function(a){function b(){d=Math.random().toString(16).substring(2);c.down.outgoing(d)}var c,d,g;return c={incoming:function(f,h){var j=f.indexOf("_");-1===j?f===d?c.up.callback(!0):g||(g=f,a.initiate||b(),c.down.outgoing(f)):f.substring(0,j)===g&&c.up.incoming(f.substring(j+
1),h)},outgoing:function(a,b,g){c.down.outgoing(d+"_"+a,b,g)},callback:function(){a.initiate&&b()}}};g.stack.RpcBehavior=function(a,b){function c(a){a.jsonrpc="2.0";f.down.outgoing(h.stringify(a))}function d(a,b){var g=Array.prototype.slice;return function(){var d=arguments.length,f,h={method:b};0<d&&"function"===typeof arguments[d-1]?(1<d&&"function"===typeof arguments[d-2]?(f={success:arguments[d-2],error:arguments[d-1]},h.params=g.call(arguments,0,d-2)):(f={success:arguments[d-1]},h.params=g.call(arguments,
0,d-1)),n[""+ ++j]=f,h.id=j):h.params=g.call(arguments,0);if(a.namedParams&&1===h.params.length)h.params=h.params[0];c(h)}}function g(a,b,d,f){if(d){var h,i;b?(h=function(a){h=R;c({id:b,result:a})},i=function(a,d){i=R;var f={id:b,error:{code:-32099,message:a}};if(d)f.error.data=d;c(f)}):h=i=R;"[object Array]"===Object.prototype.toString.call(f)||(f=[f]);try{var j=d.method.apply(d.scope,f.concat([h,i]));z(j)||h(j)}catch(k){i(k.message)}}else b&&c({id:b,error:{code:-32601,message:"Procedure not found."}})}
var f,h=b.serializer||S(),j=0,n={};return f={incoming:function(a){a=h.parse(a);if(a.method)b.handle?b.handle(a,c):g(a.method,a.id,b.local[a.method],a.params);else{var d=n[a.id];a.error?d.error&&d.error(a.error):d.success&&d.success(a.result);delete n[a.id]}},init:function(){if(b.remote)for(var c in b.remote)b.remote.hasOwnProperty(c)&&(a[c]=d(b.remote[c],c));f.down.init()},destroy:function(){for(var c in b.remote)b.remote.hasOwnProperty(c)&&a.hasOwnProperty(c)&&delete a[c];f.down.destroy()}}};M.easyXDM=
g})(window,document,location,window.setTimeout,decodeURIComponent,encodeURIComponent);"undefined"==typeof CFW&&(CFW={});jQuery.extend(CFW,{domain:/[.]local/.test(window.location.host)||/yestech/.test(window.location.host)||/squatch/.test(window.location.host)?"http://tameron.herokuapp.com":"http://cfwhiteboard.com"});jQuery(function(){CFW_OPTIONS.athletes_page_path=jQuery.url(CFW_OPTIONS.athletes_page_permalink).attr("path");CFW.athletesRouter=new CFW.AthletesRouter;Backbone.history.start({pushState:!0,root:CFW_OPTIONS.athletes_page_path})});
(function(c){CFW.AthletesRouter=Backbone.Router.extend({routes:{},initialize:function(){this.route("","index");this.route(/(.+)/,"show");return this},_trackPageview:function(){var c=Backbone.history.getFragment();if("undefined"!=typeof _gaq)return _gaq.push(["_trackPageview","/"+c])},index:function(){this.pageView=new CFW.AthletesPage({el:c("body")})},show:function(d){this.pageView=new CFW.AthletePage({el:c("body"),athlete:d})}});CFW.PageBase=Backbone.View.extend({title:function(c){return"undefined"==
typeof c?this._getTraced(".cfw-title-tracer:not(ul *, ol *)"):this._setTraced(".cfw-title-tracer:not(ul *, ol *)",c)},content:function(c){return"undefined"==typeof c?this._getTraced(".cfw-content-tracer"):this._setTraced(".cfw-content-tracer",c)},category:function(c){return"undefined"==typeof c?this._getTraced(".cfw-category-tracer"):this._setTraced(".cfw-category-tracer",c)},tags:function(c){return"undefined"==typeof c?this._getTraced(".cfw-tags-tracer"):this._setTraced(".cfw-tags-tracer",c)},_setTraced:function(c,
f){this.$(c).empty().append(f);"undefined"!=typeof Cufon&&Cufon.refresh();return this},_getTraced:function(c){return this.$(c).children()},openSocket:function(c){return new easyXDM.Socket({remote:c,container:this.socketContainer,onMessage:_.bind(this.onSocketMessage,this),props:{seamless:!0,style:{width:"100%",height:"2000px",border:"none",background:"transparent","min-width":"480px","max-width":"none"}}})},onSocketMessage:function(d){d=c.parseJSON(d);if("undefined"!=typeof d){if("undefined"!=typeof d.navigationRequest){Backbone.history.fragment=
null;var f="undefined"==typeof d.trigger?!0:d.trigger,h="undefined"==typeof d.replace?!1:d.replace;CFW.athletesRouter.navigate(d.navigationRequest.replace(CFW_OPTIONS.athletes_page_permalink,""),{trigger:f,replace:h})}if("undefined"!=typeof d.socketLocationRequest)c(this.socketContainer).empty(),this.openSocket(d.socketLocationRequest),Backbone.history.fragment=null,CFW.athletesRouter.navigate(window.location.href.replace(CFW_OPTIONS.athletes_page_permalink,""));"undefined"!=typeof d.heightRequest&&
c(this.socketContainer).find("iframe").height(d.heightRequest);"undefined"!=typeof d.cssRequest&&c(document.body).append("<style>"+d.cssRequest+"</style>")}}});CFW.AthletesPage=CFW.PageBase.extend({initialize:function(){return this.render()},render:function(){var d=c("<span />");this.socketContainer=d[0];this.openSocket(CFW.domain+"/affiliates/"+CFW_OPTIONS.affiliate_id+"/athletes?baseurl="+CFW_OPTIONS.athletes_page_permalink+"&theme="+CFW_OPTIONS.athletes_theme+"&time_offset="+(new Date).getTimezoneOffset());
this.content(d);(d=(c(".cfw-content-tracer").offset()||{top:!1}).top)&&c(window).scrollTop()>d&&c("html, body").animate({scrollTop:d-50},300);return this}});CFW.AthletePage=CFW.PageBase.extend({initialize:function(){return this.render()},render:function(){var d=c("<span />");this.socketContainer=d[0];var f=this.options.athlete.split("?"),h=CFW.domain+"/affiliates/"+CFW_OPTIONS.affiliate_id+"/athletes/"+f[0]+"?baseurl="+CFW_OPTIONS.athletes_page_permalink+"&theme="+CFW_OPTIONS.athletes_theme+"&time_offset="+
(new Date).getTimezoneOffset();1<f.length&&(h+="&"+f[1],CFW.athletesRouter.navigate(f[0],{trigger:!1,replace:!0}));this.openSocket(h);this.content(d);(d=(c(".cfw-content-tracer").offset()||{top:!1}).top)&&c(window).scrollTop()>d&&c("html, body").animate({scrollTop:d-50},300);return this}})})(jQuery);
