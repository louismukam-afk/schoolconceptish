// Liste limitée aux méthodes de lecture vérifiées dans les contrôleurs.
const {connect,OUT,sleep}=require('./browser.cjs');
const fs=require('node:fs'),path=require('node:path');
const routes=process.argv.length>2?process.argv.slice(2):['/inscriptions/enregistrement/create','/inscriptions/create/2004','/inscriptions/payement/create/2004','/inscriptions/payement/create-frais/2004','/inscriptions/enregistrement/edit/2004','/personnel/list','/admin/cycles','/admin/salles','/'];
(async()=>{const b=await connect({newTab:true});const inv=JSON.parse(fs.readFileSync(path.join(OUT,'inventaire.json'),'utf8'));try{
for(const route of routes){
await b.go(route);const p=inv.find(p=>p.route===route);if(!p)continue;
const height=await b.evaluate('document.documentElement.scrollHeight');
p.extraCaptures=[];
// Les tableaux longs ne nécessitent pas une image de chaque ligne. Les formulaires sont capturés par zones.
if(route.includes('/enregistrement/')||route.includes('/create/')){
for(let y=850,part=2;y<Math.min(height-350,4300);y+=850,part++){
await b.evaluate(`window.scrollTo(0,${y})`);await sleep(150);const name=p.capture.replace('captures/','').replace('.png','')+'-partie-'+part;await b.shot(name);p.extraCaptures.push({file:'captures/'+name+'.png',caption:'Suite du formulaire — zone '+part});
}}
await b.evaluate('window.scrollTo(0,0)');p.modals=[];
const targets=await b.evaluate(`JSON.stringify([...document.querySelectorAll('a[data-toggle="modal"],button[data-toggle="modal"]')].map(e=>({title:e.innerText.trim(),target:e.getAttribute('data-target')||e.getAttribute('href')})).filter(e=>e.target?.startsWith('#')&&/ajouter|créer|creer|nouveau|importer|modifier le mot de passe/i.test(e.title)&&document.querySelector(e.target)?.classList.contains('modal')))`);
for(const item of JSON.parse(targets).slice(0,3)){
await b.evaluate(`(()=>{const t=${JSON.stringify(item.target)};[...document.querySelectorAll('[data-toggle=modal]')].find(e=>(e.getAttribute('data-target')||e.getAttribute('href'))===t)?.click()})()`);await sleep(400);
const name=(route==='/'?'accueil':route.slice(1).replaceAll('/','-'))+'-fenetre-'+item.target.slice(1);await b.shot(name);p.modals.push({title:item.title,file:'captures/'+name+'.png'});
await b.evaluate(`document.querySelector(${JSON.stringify(item.target)}).querySelector('[data-dismiss=modal]')?.click()`);await sleep(350);
}
fs.writeFileSync(path.join(OUT,'inventaire.json'),JSON.stringify(inv,null,2));console.log(route,p.extraCaptures.length,p.modals.length);
}
}finally{b.close();}})().catch(e=>{console.error(e.message);process.exit(1)});
