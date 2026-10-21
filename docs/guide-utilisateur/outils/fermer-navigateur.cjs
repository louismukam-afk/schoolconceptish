// Ferme uniquement le navigateur de documentation lancé sur le port dédié 9337.
(async()=>{
const version=await(await fetch('http://127.0.0.1:9337/json/version')).json();
const ws=new WebSocket(version.webSocketDebuggerUrl);
await new Promise((resolve,reject)=>{const timeout=setTimeout(()=>{ws.close();reject(Error('Fermeture expirée'));},5000);ws.addEventListener('open',()=>ws.send(JSON.stringify({id:1,method:'Browser.close'})),{once:true});ws.addEventListener('close',()=>{clearTimeout(timeout);resolve();},{once:true});ws.addEventListener('error',()=>{clearTimeout(timeout);resolve();},{once:true});});
console.log('Navigateur de documentation fermé.');
})().catch(e=>{console.error(e.message);process.exit(1)});
