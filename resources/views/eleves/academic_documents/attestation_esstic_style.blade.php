.modele-esstic .certificate-esstic {
    height: {{ $page_size==='a3'?271:188 }}mm;
    min-height: 0;
    padding: {{ $page_size==='a3'?13:9 }}mm {{ $page_size==='a3'?17:13 }}mm {{ $page_size==='a3'?15:11 }}mm;
    border: {{ $page_size==='a3'?9:7 }}px double #147cb2;
    outline: 2px solid #8dbed4;
    outline-offset: -{{ $page_size==='a3'?15:12 }}px;
    box-shadow: inset 0 0 0 3px #e2edf2, inset 0 0 0 5px #3b8db7;
    font-size: {{ $page_size==='a3'?18:13 }}px;
    line-height: 1.13;
    overflow: hidden;
    background: #fff;
    justify-content: flex-start;
}
.certificate-esstic:before, .certificate-esstic:after { display: none; }
.certificate-esstic>.esstic-certificate-frame { position: absolute; inset: {{ $page_size==='a3'?4:3 }}mm; border: 1px solid #9ac6dc; pointer-events: none; }
.certificate-esstic>.esstic-certificate-corner { position: absolute; z-index: 2; width: {{ $page_size==='a3'?10:7 }}mm; height: {{ $page_size==='a3'?10:7 }}mm; border: 4px double #0b6ea4; background: #d9eaf2; box-shadow: inset 0 0 0 2px #fff; }
.corner-tl { top: 0; left: 0; }.corner-tr { top: 0; right: 0; }.corner-bl { bottom: 0; left: 0; }.corner-br { bottom: 0; right: 0; }
.certificate-esstic .esstic-watermark { position: absolute; top: 27%; left: 20%; width: 60%; height: 58%; object-fit: contain; opacity: .07; z-index: 0; pointer-events: none; }
.certificate-esstic>.esstic-watermark-name { position: absolute; bottom: {{ $page_size==='a3'?19:14 }}mm; left: 9%; width: 82%; z-index: 0; text-align: center; font-size: {{ $page_size==='a3'?53:37 }}px; font-weight: bold; color: rgba(29, 93, 143, .055); white-space: nowrap; overflow: hidden; }
.certificate-esstic>:not(.esstic-watermark):not(.esstic-watermark-name):not(.esstic-certificate-frame):not(.esstic-certificate-corner) { position: relative; z-index: 1; }
.certificate-esstic .esstic-certificate-top { display: grid; grid-template-columns: 23% 10% 12% 10% 23%; justify-content: space-between; align-items: start; gap: 1%; text-align: center; }
.certificate-esstic .esstic-certificate-institution { font-size: {{ $page_size==='a3'?12:8 }}px; line-height: 1.13; overflow-wrap: break-word; }
.certificate-esstic .esstic-certificate-institution strong:first-child { font-size: 130%; }
.certificate-esstic .esstic-certificate-logo img { display: block; width: 100%; height: {{ $page_size==='a3'?36:25 }}mm; max-height: none; object-fit: contain; }
.certificate-esstic .esstic-certificate-heading { text-align: center; margin: {{ $page_size==='a3'?10:7 }}mm 0 {{ $page_size==='a3'?5:3 }}mm; }
.certificate-esstic .esstic-certificate-heading h1 { margin: 0; color: #111; font-size: {{ $page_size==='a3'?19:13 }}px; line-height: 1.12; }
.certificate-esstic .esstic-certificate-heading>strong { display: block; font-size: {{ $page_size==='a3'?17:12 }}px; line-height: 1.1; }
.certificate-esstic .esstic-certificate-body { flex: none; display: flex; flex-direction: column; justify-content: flex-start; min-height: 0; }
.certificate-esstic .esstic-certificate-body p { margin: 0 0 {{ $page_size==='a3'?5:3 }}mm; text-align: justify; }
.certificate-esstic .esstic-certificate-body p:last-child { margin-bottom: 0; }
.certificate-esstic .esstic-certificate-body em { font-style: normal; }
.certificate-esstic .esstic-certificate-identity { display: grid; grid-template-columns: 1.25fr 1fr; column-gap: 8%; row-gap: {{ $page_size==='a3'?5:3 }}mm; margin: {{ $page_size==='a3'?4:2 }}mm 6%; }
.certificate-esstic .esstic-certificate-identity>div { min-width: 0; overflow-wrap: break-word; }
.certificate-esstic .esstic-certificate-identity em { font-size: 90%; }
.certificate-esstic .esstic-certificate-results { display: flex; justify-content: space-between; gap: 5%; margin: {{ $page_size==='a3'?6:4 }}mm 5% 0; text-align: left; }
.certificate-esstic .esstic-certificate-results>div { flex: 1; }
.certificate-esstic .esstic-certificate-results>div:last-child { text-align: center; }
.certificate-esstic .esstic-certificate-issued { text-align: center; font-weight: bold; margin: 0; }
.certificate-esstic .esstic-certificate-signatures { display: flex; justify-content: space-between; align-items: flex-start; gap: 5%; min-height: {{ $page_size==='a3'?30:19 }}mm; margin-top: 1mm; }
.certificate-esstic .esstic-certificate-signatures>strong { max-width: 44%; }
.certificate-esstic .esstic-certificate-signatures>strong:last-child { text-align: right; }
.certificate-esstic .esstic-certificate-bottom { position: absolute!important; bottom: {{ $page_size==='a3'?46:34 }}mm; left: 10%; width: 80%; text-align: center; font-size: {{ $page_size==='a3'?10:7 }}px; line-height: 1.1; }
@media print {
    .modele-esstic .certificate-esstic { height: {{ $page_size==='a3'?271:188 }}mm; break-after: page; page-break-after: always; }
    .modele-esstic .certificate-esstic:last-of-type { break-after: auto; page-break-after: auto; }
}
