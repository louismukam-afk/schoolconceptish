const {connect,BASE,OUT,sleep}=require('./browser.cjs');
const fs=require('node:fs'),path=require('node:path');
async function main(){
const b=await connect({newTab:true});
try{
let inventory=fs.existsSync(path.join(OUT,'inventaire.json'))?JSON.parse(fs.readFileSync(path.join(OUT,'inventaire.json'),'utf8')):[];
let routes=process.argv.slice(2);
if(routes[0]==='--menus') {
  const denied=/\/(?:auth|language)\/|\/admin\/(?:blk|unblk)$|destroy|delete|deactivate|activate|store|update|reset|disconnect/i;
  routes=[...new Set(inventory.filter(e=>['/inscriptions','/eleves','/personnel','/comptabilite','/admin'].includes(e.route)).flatMap(e=>e.links).map(l=>l.href).filter(h=>h.startsWith(BASE)&&!denied.test(h)).map(h=>h.slice(BASE.length).replace(/#.*$/,'')).filter(h=>h&&!inventory.some(e=>e.route===h)))];
  fs.writeFileSync(path.join(OUT,'parcours.json'),JSON.stringify(routes,null,2));
}
for(const route of routes){
const slug=route==='/'?'accueil':route.replace(/^\/+|\/+$/g,'').replace(/[^a-z0-9-]/gi,'-');
try{
await b.go(route);
await b.evaluate(`document.querySelectorAll('.phpdebugbar,.phpdebugbar-openhandler').forEach(e=>e.remove())`);
const state=JSON.parse(await b.inspect());
if(!state.url.startsWith(BASE))throw Error('La page active ne correspond pas à une interface de l’application.');
state.forms=state.forms.filter(f=> !/update-school-year|set-status/.test(f.action) && f.method!=='get' || f.method==='get');
state.forms.forEach(f=>f.fields=f.fields.filter(e=>!['hidden','password'].includes(e.type)));
const filename=slug+'.png';await b.shot(slug);
const entry={route,capture:'captures/'+filename,...state};
inventory=inventory.filter(e=>e.route!==route);inventory.push(entry);
fs.writeFileSync(path.join(OUT,'inventaire.json'),JSON.stringify(inventory,null,2));
console.log(JSON.stringify({route,title:state.title}));
}catch(e){console.log(JSON.stringify({route,error:e.message}));}
}
}finally{b.close();}
}
main().catch(e=>{console.error(e);process.exit(1)});
