const fs=require('node:fs'),path=require('node:path');const file=path.join(__dirname,'generer-guide.cjs');let s=fs.readFileSync(file,'utf8');
s=s.replace("histoire?'Historique des relevés':p.route.includes('attestation')", "histoire?(p.route.includes('attestation')?'Historique des attestations':'Historique des relevés'):p.route.includes('attestation')");
fs.writeFileSync(file,s);
