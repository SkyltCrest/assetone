{{--
    KEW.PA-10 — Borang Aduan Kerosakan Aset Alih.
      $data   one reported issue, with the asset's earlier maintenance spend
    Bahagian III is the head of department's decision, which AssetOne does not
    record, so it is always left to be completed by hand.
--}}
@php
    $report = $data['report'];
    $asset = $report->asset;
    $date = fn ($value) => $value ? $value->format('d/m/Y') : '';
    $money = fn ($value) => $value !== null ? 'RM '.number_format((float) $value, 2) : '';
    $reported = $date($report->created_at);
    $withPost = fn ($person) => $person ? $person->name.($person->position ? ', '.$person->position : '') : '';

    $partOne = [
        ['Jenis Aset', $asset->type->name ?? $asset->category->name ?? ''],
        ['Nombor Siri Pendaftaran Aset / Komponen', $asset->asset_code ?? ''],
        ['Pengguna Terakhir', $asset->custodian->name ?? $report->reporter->name ?? ''],
        ['Tarikh Kerosakan', $reported],
        ['Perihal Kerosakan', $report->description],
        ['Nama dan Jawatan', $withPost($report->reporter)],
    ];
@endphp

<section class="sheet pa10">
    <div class="circular"><span>Pekeliling Perbendaharaan Malaysia</span><span>AM 2.4 Lampiran B</span></div>
    <div class="code">KEW.PA &ndash; 10</div>
    <h1>BORANG ADUAN KEROSAKAN ASET ALIH</h1>

    <h2>Bahagian I (Untuk diisi oleh Pengadu)</h2>
    @foreach($partOne as $i => [$label, $value])
        <div class="q">
            <span class="n">{{ $i + 1 }}.</span><span class="k">{{ $label }}</span>
            <span class="a">: <span class="line"><span class="v" contenteditable="true">{{ $value }}</span></span></span>
        </div>
    @endforeach
    <div class="ext">
        <span class="line"><span class="v" contenteditable="true"></span></span>
        <span>EXT:</span>
        <span class="line short"><span class="v" contenteditable="true"></span></span>
    </div>
    <div class="q">
        <span class="n">7.</span><span class="k">Tarikh</span>
        <span class="a">: <span class="line"><span class="v" contenteditable="true">{{ $reported }}</span></span></span>
    </div>

    <h2>Bahagian II (Untuk diisi oleh Pegawai Aset / Pegawai Teknikal)</h2>
    <div class="q">
        <span class="n">8.</span><span class="k">Jumlah Kos Penyelenggaraan Terdahulu</span>
        <span class="a">: <span class="line"><span class="v" contenteditable="true">{{ $report->verified_at ? $money($data['previousCost']) : '' }}</span></span></span>
    </div>
    <div class="q">
        <span class="n">9.</span><span class="k">Anggaran Kos Penyelenggaraan</span>
        <span class="a">: <span class="line"><span class="v" contenteditable="true">{{ $money($data['estimate']) }}</span></span></span>
    </div>
    <div class="q">
        <span class="n">10.</span><span class="k">Syor dan Ulasan</span>
        <span class="a">: <span class="line" style="min-height:4.5em"><span class="v" contenteditable="true">{{ $data['recommendation'] }}</span></span></span>
    </div>
    <div class="q">
        <span class="n">11.</span><span class="k">Nama dan Jawatan</span>
        <span class="a">: <span class="line" style="min-height:3em"><span class="v" contenteditable="true">{{ $withPost($report->verifier) }}</span></span></span>
    </div>
    <div class="q">
        <span class="n">12.</span><span class="k">Tarikh</span>
        <span class="a">: <span class="line"><span class="v" contenteditable="true">{{ $date($report->verified_at) }}</span></span></span>
    </div>

    <h2>Bahagian III (Keputusan Ketua Jabatan / Bahagian / Seksyen / Unit)</h2>
    <div>Diluluskan /Tidak Diluluskan *</div>
    <div class="remark"><span>Ulasan:</span><span class="line"><span class="v" contenteditable="true"></span></span></div>

    <div class="signature">
        <div class="line"></div>
        <div class="cap">Tandatangan</div>
    </div>
    @foreach(['Nama', 'Jawatan', 'Tarikh'] as $label)
        <div class="who"><span>{{ $label }}</span>: <span class="line"><span class="v" contenteditable="true"></span></span></div>
    @endforeach

    <div class="note">Nota: * Potong mana yang berkenaan</div>
</section>
