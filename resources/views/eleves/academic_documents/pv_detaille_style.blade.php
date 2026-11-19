.document-pv .pv-detail{font-size:{{ count($sessions)>18?5:6 }}px;break-inside:avoid;page-break-inside:avoid}
.document-pv .pv-detail h3{text-align:center;font-size:8px;margin:2mm 0 1mm;text-transform:uppercase}
.document-pv .pv-detail table{width:100%;table-layout:fixed;border-collapse:collapse;margin:0 0 2mm}
.document-pv .pv-detail-legend{font-size:{{ count($sessions)>30?5:6 }}px}
.document-pv .pv-detail-legend td{width:33.333%;border:1px solid #555;padding:2px 3px;text-align:left;line-height:1.15}
.document-pv .pv-detail-legend small{white-space:nowrap;font-size:0.9em}
.document-pv .pv-detail-matrix{font-size:{{ count($sessions)>18?5:6 }}px}
.document-pv .pv-detail-matrix th,.document-pv .pv-detail-matrix td{border:1px solid #222;padding:2px 1px;text-align:center;line-height:1.1;overflow-wrap:anywhere}
.document-pv .pv-detail-matrix th{background:#eee}
.document-pv .pv-detail-matrix th:nth-child(1){width:3%}
.document-pv .pv-detail-matrix th:nth-child(2){width:8%}
.document-pv .pv-detail-matrix th:nth-child(3){width:12%}
.document-pv .pv-detail-matrix .pv-detail-subject-heading{background:#ddd}
.document-pv .pv-detail-matrix .pv-detail-name{text-align:left}
.document-pv .pv-detail-matrix .pv-detail-final{font-weight:bold}
.document-pv .pv-detail-matrix .pv-detail-final small{display:block;font-weight:normal}
@media print{.document-pv .pv-detail,.document-pv .pv-detail-matrix tr{break-inside:avoid;page-break-inside:avoid}}
