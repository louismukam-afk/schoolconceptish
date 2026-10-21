const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const {spawn} = require('node:child_process');
const BASE = 'http://127.0.0.1:8080/ISH/app/public';
const OUT = path.resolve(__dirname, '..');
const sleep = ms => new Promise(r => setTimeout(r, ms));
async function connect({newTab=false}={}) {
  const port = 9337;
  let info;
  try { info = await (await fetch(`http://127.0.0.1:${port}/json/list`)).json(); } catch {
    const profile = fs.mkdtempSync(path.join(os.tmpdir(), 'ish-documentation-'));
    const chrome = spawn('C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check', `--remote-debugging-port=${port}`, `--user-data-dir=${profile}`, 'about:blank'], {windowsHide:true, detached:true, stdio:'ignore'});
    chrome.unref();
    for(let i=0;i<30;i++) { await sleep(300); try {info = await (await fetch(`http://127.0.0.1:${port}/json/list`)).json(); break;}catch{} }
  }
  const tab = newTab ? await (await fetch(`http://127.0.0.1:${port}/json/new?about:blank`,{method:'PUT'})).json() : info.find(t=>t.type==='page');
  const ws = new WebSocket(tab.webSocketDebuggerUrl);
  await new Promise((resolve,reject)=>{const timer=setTimeout(()=>reject(Error('Connexion au navigateur expirée')),15000);ws.addEventListener('open',()=>{clearTimeout(timer);resolve();},{once:true});ws.addEventListener('error',e=>{clearTimeout(timer);reject(Error('WebSocket: '+e.message));},{once:true});});
  let id=0; const pending = new Map();
  ws.addEventListener('message', event=>{const m=JSON.parse(event.data); if(pending.has(m.id)){const p=pending.get(m.id);pending.delete(m.id);m.error?p.reject(Error(JSON.stringify(m.error))):p.resolve(m.result);}});
  const call = (method,params={})=>new Promise((resolve,reject)=>{const n=++id;const timer=setTimeout(()=>{pending.delete(n);reject(Error('Délai CDP: '+method));},method==='Page.printToPDF'?180000:30000);pending.set(n,{resolve:r=>{clearTimeout(timer);resolve(r);},reject:e=>{clearTimeout(timer);reject(e);}});ws.send(JSON.stringify({id:n,method,params}));});
  await call('Page.enable'); await call('Runtime.enable');
  await call('Emulation.setDeviceMetricsOverride',{width:1440,height:1000,deviceScaleFactor:1,mobile:false});
  const evaluate = async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true}); if(r.exceptionDetails) throw Error(r.exceptionDetails.exception?.description||r.exceptionDetails.text);return r.result.value;};
  const go = async url=>{await call('Page.navigate',{url:url.startsWith('http')||url.startsWith('file:')?url:BASE+url});await sleep(950);for(let i=0;i<40;i++){try{if(await evaluate('document.readyState === "complete"'))break;}catch{}await sleep(250);}await sleep(400);};
  const shot = async name=>{await evaluate(`(()=>{let s=document.createElement('style');s.textContent='.phpdebugbar,.phpdebugbar-openhandler,.phpdebugbar-mini-design{display:none!important}';document.head.append(s);document.querySelectorAll('input[type=password]').forEach(x=>x.value='');})()`);const r=await call('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});fs.writeFileSync(path.join(OUT,'captures',name+'.png'),Buffer.from(r.data,'base64'));};
  const inspect = ()=>evaluate(`JSON.stringify({url:location.href,title:document.title,text:document.body.innerText.slice(0,24000),links:[...document.querySelectorAll('a[href]')].map(a=>({text:a.innerText.trim(),href:a.getAttribute('href')})),forms:[...document.forms].map(f=>({action:f.action,method:f.method,fields:[...f.elements].map(e=>({tag:e.tagName,type:e.type,name:e.name,label:e.labels?.[0]?.innerText,placeholder:e.placeholder,options:e.tagName==='SELECT'?[...e.options].slice(0,60).map(o=>({value:o.value,text:o.text})):undefined}))}))})`);
  return {call,evaluate,go,shot,inspect,close:()=>ws.close()};
}
module.exports={connect,BASE,OUT,sleep};
if(require.main===module) (async()=>{const b=await connect();try{const [cmd,arg]=process.argv.slice(2);if(cmd==='go')await b.go(arg);if(cmd==='eval')console.log(await b.evaluate(arg));else if(cmd==='shot')await b.shot(arg);else console.log(await b.inspect());}finally{b.close();}})().catch(e=>{console.error(e.message);process.exit(1);});
