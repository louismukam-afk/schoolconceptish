const {connect,OUT,sleep}=require('./browser.cjs');const fs=require('node:fs'),path=require('node:path');const {pathToFileURL}=require('node:url');
(async()=>{const b=await connect({newTab:true});const illustrations=[];try{
await b.call('Emulation.setScriptExecutionDisabled',{value:true});
const names=fs.readdirSync(path.join(OUT,'apercus')).filter(f=>f.endsWith('.html'));
for(const filename of names){const name=filename.slice(0,-5);await b.go(pathToFileURL(path.join(OUT,'apercus',filename)).href);
const printed=/^(pv-|releve-|attestation-)/.test(name);
await b.call('Emulation.setEmulatedMedia',{media:printed?'print':'screen'});
await b.call('Emulation.setDeviceMetricsOverride',{width:printed?1920:1440,height:printed?1250:1000,deviceScaleFactor:1,mobile:false});
const caption=name.startsWith('attestation-')?'EXEMPLE PÉDAGOGIQUE — DONNÉES FICTIVES — AUCUNE ATTESTATION DÉLIVRÉE':'APERÇU LOCAL — VUES DU PROJET — AUCUN ENREGISTREMENT';
await b.evaluate(`(()=>{const e=document.createElement('div');e.textContent=${JSON.stringify(caption)};e.style.cssText='position:fixed;bottom:3px;left:10px;z-index:999999;background:#fff7de;color:#69450a;border:1px solid #d1a44d;padding:5px 10px;font:14px Arial';document.body.append(e)})()`);
await sleep(200);
if(printed){
const box=await b.evaluate(`(()=>{const el=document.querySelector('.attestation-page,.soa-page,.releve');if(el){const r=el.getBoundingClientRect();return{x:Math.max(0,r.x+scrollX),y:Math.max(0,r.y+scrollY),width:Math.ceil(r.width),height:Math.min(2200,Math.ceil(r.height))}}return{x:0,y:0,width:Math.min(5000,document.documentElement.scrollWidth),height:Math.min(1500,document.documentElement.scrollHeight)}})()`);
const shot=await b.call('Page.captureScreenshot',{format:'png',captureBeyondViewport:true,clip:{...box,scale:1}});fs.writeFileSync(path.join(OUT,'captures','v2-'+name+'.png'),Buffer.from(shot.data,'base64'));
}else await b.shot('v2-'+name);
illustrations.push({name,file:'captures/v2-'+name+'.png',origin:printed?'Modèle rendu localement en lecture seule':'Formulaire rendu localement sans initialisation ni validation'});
console.log(name);
}
fs.writeFileSync(path.join(OUT,'illustrations-v2.json'),JSON.stringify(illustrations,null,2));
}finally{await b.call('Emulation.setScriptExecutionDisabled',{value:false});b.close();}})().catch(e=>{console.error(e.message);process.exit(1)});
