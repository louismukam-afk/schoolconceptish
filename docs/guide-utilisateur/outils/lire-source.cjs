const fs=require('node:fs');const [file,start='1',count='120']=process.argv.slice(2);
fs.readFileSync(file,'utf8').split(/\r?\n/).slice(+start-1,+start-1+(+count)).forEach((s,i)=>console.log(`${+start+i}: ${s.length>1000?'[Ligne longue omise]':s}`));
