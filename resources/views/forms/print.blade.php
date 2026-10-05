<!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $meta['code'] }} — {{ $meta['title'] }}</title>
<meta name="color-scheme" content="light only">
<style>
    :root { color-scheme: light only; }
    * { font-family: Arial, Helvetica, sans-serif; box-sizing: border-box; }
    html, body { background: #E9EDF3; margin: 0; }
    body { color: #000; font-size: 11pt; padding: 16px; }

    .toolbar { max-width: 210mm; margin: 0 auto 12px; display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .toolbar button { font-size: 12px; padding: 6px 14px; cursor: pointer;
        border: 1px solid #0B3159; border-radius: 6px; background: #0B3159; color: #fff; }
    .toolbar button.secondary { background: #fff; color: #0B3159; }
    .toolbar .hint { font-size: 12px; color: #4A5870; margin-left: 4px; }
    .none { max-width: 210mm; margin: 0 auto; background: #fff; padding: 32px; text-align: center; color: #4A5870; }

    /* One A4 sheet per form */
    .sheet { width: 210mm; min-height: 297mm; margin: 0 auto 16px; background: #fff; padding: 14mm 16mm 16mm;
        box-shadow: 0 1px 6px rgba(0, 0, 0, .18); page-break-after: always; break-after: page; }
    .sheet:last-of-type { page-break-after: auto; break-after: auto; }
    .circular { display: flex; justify-content: space-between; font-size: 10pt; margin-bottom: 9mm; }
    .code { text-align: right; font-weight: bold; font-size: 12pt; }
    h1 { font-size: 12pt; text-align: center; margin: 0; }

    /* Values typed in by AssetOne, and blanks that can be typed into before printing */
    .v { display: inline-block; min-width: 12mm; min-height: 1.2em; outline: none; white-space: pre-wrap; word-break: break-word; }
    td > .v, .line > .v { display: block; }
    .v:hover, .v:focus { background: #FFF6CC; }
    .line { border-bottom: 1px dotted #000; min-height: 1.5em; }

    /* KEW.PA-9 */
    .pa9 .ref { text-align: right; margin: 6mm 0 7mm; }
    .pa9 .ref .v { min-width: 30mm; border-bottom: 1px dotted #000; text-align: left; }
    .pa9 table { width: 100%; border-collapse: collapse; }
    .pa9 td, .pa9 th { border: 1px solid #000; padding: 3px 6px; vertical-align: top; font-size: 10pt; }
    .pa9 .applicant { margin: 4mm 0 7mm; }
    .pa9 .applicant td { height: 9.5mm; }
    .pa9 .applicant .k { font-weight: bold; width: 21%; white-space: nowrap; }
    .pa9 .items th { background: #D9D9D9; font-size: 9pt; vertical-align: middle; text-align: center; }
    .pa9 .items td { height: 9.5mm; font-size: 9pt; }
    .pa9 .items td.c { text-align: center; padding: 3px 2px; }
    .pa9 .items td.c .v { min-width: 0; }
    .pa9 .sign td { width: 50%; padding: 12mm 8px 5mm; font-size: 10pt; }
    .pa9 .sign .dots { letter-spacing: 1px; }
    .pa9 .sign .who { display: flex; margin-top: 2.5mm; }
    .pa9 .sign .who b { width: 19mm; }
    .pa9 .sign .who .v { flex: 1; margin-left: 2mm; }

    /* KEW.PA-10 */
    .pa10 h1 { font-size: 13pt; margin: 3mm 0 5mm; }
    .pa10 h2 { font-size: 11pt; background: #D9D9D9; margin: 6mm 0 4mm; padding: 1px 2px; }
    .pa10 .q { display: flex; align-items: flex-start; margin-bottom: 3.5mm; }
    .pa10 .q .n { width: 9mm; }
    .pa10 .q .k { width: 71mm; padding-right: 4mm; }
    .pa10 .q .a { flex: 1; display: flex; }
    .pa10 .q .a .line { flex: 1; margin-left: 1.5mm; }
    .pa10 .ext { display: flex; margin: -1.5mm 0 3.5mm 82mm; }
    .pa10 .ext .line { flex: 1; }
    .pa10 .ext .line.short { flex: 0 0 24mm; }
    .pa10 .remark { display: flex; margin-top: 5mm; }
    .pa10 .remark .line { flex: 1; margin-left: 2mm; min-height: 3em; }
    .pa10 .signature { margin-top: 14mm; width: 75mm; }
    .pa10 .signature .cap { text-align: center; }
    .pa10 .who { display: flex; margin-top: 4mm; width: 110mm; }
    .pa10 .who span:first-child { width: 28mm; }
    .pa10 .who .line { flex: 1; margin-left: 1.5mm; }
    .pa10 .note { margin-top: 7mm; font-size: 10pt; font-weight: bold; font-style: italic; }

    @page { size: A4; margin: 0; }
    @media print {
        html, body { background: #fff; }
        body { padding: 0; }
        .toolbar { display: none; }
        .sheet { margin: 0; box-shadow: none; }
        .v:hover, .v:focus { background: none; }
    }
</style>
</head>
<body>

<div class="toolbar">
    <button onclick="window.print()">Print / Save as PDF</button>
    <button class="secondary" onclick="window.close()">Close</button>
    <span class="hint">{{ $forms->count() }} {{ \Illuminate\Support\Str::plural('form', $forms->count()) }} &middot; click any field to fill in or correct it before printing.</span>
</div>

@forelse($forms as $data)
    @include('forms.partials.'.$form, ['data' => $data])
@empty
    <div class="none">No records match the selected filters, so there is nothing to print.</div>
@endforelse

</body>
</html>
