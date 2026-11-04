/* Dimensions et typographie propres au format choisi. */
body{font-size:{{ $paper_font }}px;width:{{ $paper_width-2*$paper_margin }}mm;max-width:calc(100% - 32px);margin:16px auto}
table{max-width:100%}td,th{overflow-wrap:anywhere;padding:{{ $page_size==='a3'?7:5 }}px}th{vertical-align:middle}
.branding-title,.official-headings>div{min-width:0;overflow-wrap:anywhere}
.paper-{{ $page_size }} .results-matrix{table-layout:fixed;font-size:{{ $matrix_font }}px}
.paper-{{ $page_size }} .results-matrix td,.paper-{{ $page_size }} .results-matrix th{padding:{{ $page_size==='a3'?4:3 }}px}
.results-matrix>thead>tr:first-child>th:first-child{width:{{ $page_size==='a3'?28:20 }}px}
.results-matrix>thead>tr:first-child>th:nth-child(2){width:{{ $page_size==='a3'?95:70 }}px}
.paper-{{ $page_size }} .results-matrix>thead>tr:first-child>th:nth-child(3){width:{{ $page_size==='a3'?150:110 }}px}
.results-matrix>thead>tr:first-child>th:nth-last-child(-n+3){width:{{ $page_size==='a3'?60:40 }}px}
.paper-a3 .document-footer{font-size:11px}.paper-a3 h2{font-size:20px}.paper-a3 .notice{font-size:12px}
.paper-a3 .branding-row{min-height:102px;gap:16px}.paper-a3 .campus-logo-block{flex-basis:120px}
.paper-a3 .campus-logo{max-width:114px;max-height:102px}.paper-a3 .tutelle-logos{flex-basis:168px;gap:8px}
.paper-a3 .tutelle-logos img{max-width:78px;max-height:90px}.paper-a3 .polytechnique-logo{max-width:108px;max-height:102px}
.paper-a3 .branding-polytechnique .campus-logo-block,.paper-a3 .branding-polytechnique .tutelle-logos{flex-basis:120px}
.paper-a3 .branding-title strong{font-size:21px}.paper-a3 .branding-title span,.paper-a3 .school span,.paper-a3 .official-headings{font-size:12px}
.paper-a3 .attestation{font-size:19px;padding:30px}.paper-a3 .signature{margin:42px 0 72px}
.paper-a3.document-pv_groupes table:not(.results-matrix){font-size:11px}
@media print{body{width:auto;max-width:none;margin:0}table{width:100%}.toolbar{display:none}}

.module-matrix small{display:block}.module-average{background:#e8eef5;font-weight:bold}.module-heading{background:#eee}.module-transcript .module-average th{background:#e8eef5}
