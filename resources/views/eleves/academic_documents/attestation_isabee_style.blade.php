.modele-isabee .certificate-isabee {
    height: {{ $page_size==='a3'?271:188 }}mm;
    min-height: 0;
    padding: {{ $page_size==='a3'?13:9 }}mm {{ $page_size==='a3'?17:13 }}mm;
    border: {{ $page_size==='a3'?9:7 }}px double #0876bd;
    outline: 2px solid #a7cde5;
    outline-offset: -{{ $page_size==='a3'?15:12 }}px;
    box-shadow: inset 0 0 0 3px #e9f2f7, inset 0 0 0 5px #328cc0;
    font-size: {{ $page_size==='a3'?17:12 }}px;
    line-height: 1.12;
    justify-content: flex-start;
    overflow: hidden;
}
.certificate-isabee:before,.certificate-isabee:after { display:none; }
.certificate-isabee .isabee-certificate-border { inset: {{ $page_size==='a3'?4:3 }}mm; border:1px solid #78b5d4; box-shadow:none; }
.certificate-isabee>.isabee-corner { position:absolute; z-index:2; width:{{ $page_size==='a3'?10:7 }}mm; height:{{ $page_size==='a3'?10:7 }}mm; border:4px double #086eae; background:#cde5f1; box-shadow:inset 0 0 0 2px #fff; }
.isabee-corner-tl { top:0; left:0; }.isabee-corner-tr { top:0; right:0; }.isabee-corner-bl { bottom:0; left:0; }.isabee-corner-br { bottom:0; right:0; }
.certificate-isabee>.isabee-watermark { position:absolute; top:31%; left:28%; width:44%; height:42%; object-fit:contain; opacity:.06; pointer-events:none; }
.certificate-isabee>:not(.isabee-certificate-border):not(.isabee-corner):not(.isabee-watermark) { position:relative; z-index:1; }
.certificate-isabee .isabee-certificate-top { display:grid; grid-template-columns:23% 10% 12% 10% 23%; justify-content:space-between; align-items:start; gap:1%; text-align:center; }
.certificate-isabee .isabee-institution { min-width:0; font-size:{{ $page_size==='a3'?11:7 }}px; line-height:1.15; overflow-wrap:break-word; }
.certificate-isabee .isabee-institution strong:first-child { font-size:130%; }
.certificate-isabee .isabee-certificate-logo img { display:block; width:100%; height:{{ $page_size==='a3'?36:25 }}mm; max-height:none; object-fit:contain; }
.certificate-isabee .isabee-certificate-heading { text-align:center; margin:{{ $page_size==='a3'?9:6 }}mm 0 {{ $page_size==='a3'?5:3 }}mm; }
.certificate-isabee .isabee-certificate-heading h1 { margin:0; color:#111; font-size:{{ $page_size==='a3'?18:13 }}px; line-height:1.1; }
.certificate-isabee .isabee-certificate-heading>strong { display:block; font-size:{{ $page_size==='a3'?17:12 }}px; line-height:1.1; }
.certificate-isabee .isabee-certificate-body { flex:none; display:flex; flex-direction:column; justify-content:flex-start; min-height:0; }
.certificate-isabee .isabee-certificate-body p { margin:0 0 {{ $page_size==='a3'?4:2 }}mm; text-align:justify; }
.certificate-isabee .isabee-certificate-body p:last-child { margin-bottom:0; }
.certificate-isabee .isabee-certificate-identity { display:grid; grid-template-columns:1.25fr 1fr; column-gap:8%; row-gap:{{ $page_size==='a3'?5:3 }}mm; margin:{{ $page_size==='a3'?4:2 }}mm 6%; }
.certificate-isabee .isabee-certificate-identity>div { min-width:0; overflow-wrap:break-word; }
.certificate-isabee .isabee-certificate-results { display:flex; justify-content:space-between; gap:5%; margin:{{ $page_size==='a3'?6:4 }}mm 5% 0; }
.certificate-isabee .isabee-certificate-results>div:last-child { text-align:right; }
.certificate-isabee .isabee-certificate-signatures { display:flex; justify-content:space-between; gap:5%; margin-top:1mm; }
.certificate-isabee .isabee-certificate-signatures>strong { max-width:45%; }
.certificate-isabee .isabee-certificate-signatures>strong:last-child { text-align:right; }
.certificate-isabee .isabee-certificate-issued { text-align:center; font-weight:bold; margin:1mm 0 0; }
.certificate-isabee .isabee-certificate-bottom { position:absolute!important; bottom:{{ $page_size==='a3'?32:21 }}mm; left:10%; width:80%; text-align:center; font-size:{{ $page_size==='a3'?11:8 }}px; line-height:1.1; }
@media print {
    .modele-isabee .certificate-isabee { height:{{ $page_size==='a3'?271:188 }}mm; break-after:page; page-break-after:always; }
    .modele-isabee .certificate-isabee:last-of-type { break-after:auto; page-break-after:auto; }
}
