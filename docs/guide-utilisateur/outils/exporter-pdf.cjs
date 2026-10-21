const {connect,OUT}=require('./browser.cjs');
const fs=require('node:fs'),path=require('node:path');
const {pathToFileURL}=require('node:url');
(async()=>{const b=await connect({newTab:true});try{
await b.go(pathToFileURL(path.join(OUT,'GUIDE_UTILISATEUR.html')).href);
await b.call('Emulation.setEmulatedMedia',{media:'print'});
await b.evaluate('Promise.all([...document.images].map(i=>i.decode().catch(()=>{})))');
const check=await b.evaluate(`({title:document.title,images:document.images.length,broken:[...document.images].filter(i=>!i.complete||!i.naturalWidth).map(i=>i.src),screens:document.querySelectorAll('.screen').length,headings:document.querySelectorAll('h2').length})`);
if(check.broken.length)throw Error('Images non chargées : '+check.broken.length);
const result=await b.call('Page.printToPDF',{printBackground:true,preferCSSPageSize:true,displayHeaderFooter:true,headerTemplate:'<span></span>',footerTemplate:'<div style="font-family:Arial;font-size:8px;color:#637785;width:100%;margin:0 15mm;display:flex;justify-content:space-between"><span>SchoolConcept · IS-HINTEL — Guide d’utilisation · 21/10/2026</span><span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>',transferMode:'ReturnAsStream'});
const file=path.join(OUT,'GUIDE_UTILISATEUR.pdf');const fd=fs.openSync(file,'w');try{while(true){const chunk=await b.call('IO.read',{handle:result.stream,size:1024*1024});fs.writeSync(fd,Buffer.from(chunk.data,chunk.base64Encoded?'base64':'utf8'));if(chunk.eof)break;}}finally{fs.closeSync(fd);await b.call('IO.close',{handle:result.stream});}
await b.shot('apercu-guide');
const hash=require('node:crypto').createHash('sha256').update(fs.readFileSync(path.join(OUT,'GUIDE_UTILISATEUR.html'))).digest('hex');
fs.writeFileSync(path.join(OUT,'EXPORT_PDF.json'),JSON.stringify({htmlHash:hash,pdfBytes:fs.statSync(file).size,images:check.images,interfaceSheets:check.screens},null,2));
console.log(JSON.stringify({...check,pdfBytes:fs.statSync(file).size}));
}finally{b.close();}})().catch(e=>{console.error(e.message);process.exit(1)});
