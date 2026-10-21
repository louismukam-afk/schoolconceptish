// Ajustements éditoriaux et contrôle des ressources avant génération.
const fs=require('node:fs'),path=require('node:path');const dir=path.resolve(__dirname,'..');
const file=path.join(__dirname,'generer-guide.cjs');let text=fs.readFileSync(file,'utf8');
text=text.replace("para('Écran indisponible lors de la visite. '+msg,'warning')","para('Écran indisponible lors de la visite. '+problem(p),'warning')");
text=text.replace('let num=3;const coverage=[];','let num=3;let ficheNo=0;const coverage=[];');
text=text.replace('`Fiche ${num-2} — ${s.title}`','`Fiche ${++ficheNo} — ${s.title}`');
fs.writeFileSync(file,text);
const c=JSON.parse(fs.readFileSync(path.join(dir,'contenu.json'),'utf8'));
if(!c.chapters[3].paragraphs.some(p=>p.includes('blocages du navigateur')))c.chapters[3].paragraphs.push('L’écran de sélection des relevés Soa a provoqué des blocages du navigateur pendant la visite ; sa capture n’a pas pu être obtenue. Son historique est documenté. Les sélections et la génération Soa restent à vérifier en session fonctionnelle.');
fs.writeFileSync(path.join(dir,'contenu.json'),JSON.stringify(c,null,2));
console.log('Textes prêts.');
