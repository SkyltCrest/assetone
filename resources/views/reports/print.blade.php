<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Asset Report — {{ $generatedAt->format('d M Y') }}</title>
<meta name="color-scheme" content="light only">
<style>
    :root { color-scheme: light only; }
    * { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; box-sizing: border-box; }
    html, body { background: #fff; }
    body { margin: 32px; color: #152238; font-size: 12px; }
    .report-header { display: flex; justify-content: space-between; align-items: flex-start;
        border-bottom: 2px solid #0B3159; padding-bottom: 12px; margin-bottom: 16px; }
    .report-header h1 { font-size: 20px; margin: 0 0 2px; color: #0B3159; }
    .report-header .org { font-size: 12px; color: #6B7A90; }
    .report-meta { text-align: right; font-size: 11px; color: #6B7A90; line-height: 1.6; }
    .filters { background: #F4F6FA; border: 1px solid #E4E8F0; border-radius: 6px;
        padding: 10px 14px; margin-bottom: 16px; font-size: 11px; }
    .filters strong { color: #0B3159; }
    .filters .chip { display: inline-block; background: #fff; border: 1px solid #E4E8F0;
        border-radius: 12px; padding: 2px 10px; margin: 3px 4px 0 0; }
    .summary { margin-bottom: 16px; }
    .summary span { display: inline-block; margin-right: 24px; font-size: 12px; }
    .summary strong { font-size: 14px; color: #0B3159; }
    table { width: 100%; border-collapse: collapse; }
    thead th { background: #0B3159; color: #fff; text-align: left; padding: 7px 8px;
        font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; }
    tbody td { padding: 6px 8px; border-bottom: 1px solid #E4E8F0; }
    tbody tr:nth-child(even) { background: #F8FAFC; }
    tfoot th { padding: 7px 8px; border-top: 2px solid #0B3159; text-align: right; }
    .num { text-align: right; }
    .empty { text-align: center; padding: 24px; color: #6B7A90; }
    .footer { margin-top: 24px; font-size: 10px; color: #6B7A90; text-align: center;
        border-top: 1px solid #E4E8F0; padding-top: 8px; }
    .toolbar { margin-bottom: 16px; }
    .toolbar button { font-size: 12px; padding: 6px 14px; margin-right: 8px; cursor: pointer;
        border: 1px solid #0B3159; border-radius: 6px; background: #0B3159; color: #fff; }
    .toolbar button.secondary { background: #fff; color: #0B3159; }
    @media print { .toolbar { display: none; } body { margin: 0; } }
</style>
</head>
<body>

<div class="toolbar">
    <button onclick="window.print()">Print / Save as PDF</button>
    <button class="secondary" onclick="window.close()">Close</button>
</div>

<div class="report-header">
    <div>
        <h1>Asset Report</h1>
        <div class="org">MDPT Asset Management &mdash; AssetOne</div>
    </div>
    <div class="report-meta">
        Generated: {{ $generatedAt->format('d M Y, H:i') }}<br>
        @if($generatedBy)By: {{ $generatedBy->name }}<br>@endif
        Sorted by: {{ $allColumns[$sort] ?? $sort }} ({{ strtoupper($dir) }})
    </div>
</div>

<div class="filters">
    <strong>Filters applied:</strong>
    @if(count($filters))
        @foreach($filters as $label => $value)
            <span class="chip">{{ $label }}: {{ $value }}</span>
        @endforeach
    @else
        <span class="chip">None &mdash; all assets</span>
    @endif
</div>

<div class="summary">
    <span><strong>{{ number_format($reportCount) }}</strong> assets</span>
    <span><strong>{{ number_format($assignedCount) }}</strong> assigned</span>
    <span><strong>{{ number_format($unassignedCount) }}</strong> unassigned</span>
    <span>Total value: <strong>RM {{ number_format($totalValue, 2) }}</strong></span>
</div>

<table>
    <thead>
        <tr>
            <th>No.</th>
            @foreach($selectedColumns as $key)
                <th @class(['num' => $key === 'purchase_price'])>{{ $allColumns[$key] }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($assets as $i => $asset)
            <tr>
                <td>{{ $i + 1 }}</td>
                @foreach($selectedColumns as $key)
                    <td @class(['num' => $key === 'purchase_price'])>@include('reports.partials.cell', ['asset' => $asset, 'column' => $key, 'plain' => true])</td>
                @endforeach
            </tr>
        @empty
            <tr><td class="empty" colspan="{{ count($selectedColumns) + 1 }}">No assets match the selected filters.</td></tr>
        @endforelse
    </tbody>
    @if($reportCount > 0 && in_array('purchase_price', $selectedColumns, true))
        <tfoot>
            <tr>
                <th colspan="{{ array_search('purchase_price', $selectedColumns, true) + 1 }}">Total</th>
                <th class="num">{{ number_format($totalValue, 2) }}</th>
                @if(array_search('purchase_price', $selectedColumns, true) < count($selectedColumns) - 1)
                    <th colspan="{{ count($selectedColumns) - 1 - array_search('purchase_price', $selectedColumns, true) }}"></th>
                @endif
            </tr>
        </tfoot>
    @endif
</table>

<div class="footer">
    AssetOne &mdash; generated {{ $generatedAt->format('d M Y H:i') }} &middot; {{ number_format($reportCount) }} records
</div>

<script>
    window.addEventListener('load', function () {
        if (!new URLSearchParams(window.location.search).has('preview')) {
            window.print();
        }
    });
</script>

</body>
</html>
