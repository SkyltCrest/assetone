{{--
    KEW.PA-9 — Borang Permohonan Pergerakan / Pinjaman Aset Alih.
      $data      one loan: borrower, department and the assignments made that day
      $minRows   asset rows on the printed form; shorter loans are padded with blanks
--}}
@php
    $date = fn ($value) => $value ? $value->format('d/m/Y') : '';
    $items = $data['items'];
    $blankRows = max(0, $minRows - $items->count());

    // Caption, then the name / post / date under each signature line.
    $signatures = [
        [
            ['Tandatangan Peminjam', $data['applicant'], $data['applicantPosition'], $date($data['borrowed'])],
            ['Tandatangan Pelulus', $data['issuer'], $data['issuerPosition'], $date($data['approved'])],
        ],
        [
            ['Tandatangan Pemulang', $data['returned'] ? $data['applicant'] : '', $data['returned'] ? $data['applicantPosition'] : '', $date($data['returned'])],
            ['Tandatangan Penerima', '', '', $date($data['returned'])],
        ],
    ];
@endphp

<section class="sheet pa9">
    <div class="circular"><span>Pekeliling Perbendaharaan Malaysia</span><span>AM 2.4 Lampiran A</span></div>
    <div class="code">KEW.PA-9</div>
    <div class="ref">No. Permohonan : <span class="v" contenteditable="true">{{ $data['number'] }}</span></div>
    <h1>BORANG PERMOHONAN PERGERAKAN/ PINJAMAN ASET ALIH</h1>

    <table class="applicant">
        <tr>
            <td class="k">Nama Pemohon :</td>
            <td><span class="v" contenteditable="true">{{ $data['applicant'] }}</span></td>
            <td class="k">Tujuan :</td>
            <td><span class="v" contenteditable="true">{{ $data['purpose'] }}</span></td>
        </tr>
        <tr>
            <td class="k">Jawatan :</td>
            <td><span class="v" contenteditable="true">{{ $data['applicantPosition'] }}</span></td>
            <td class="k">Tempat Digunakan:</td>
            <td><span class="v" contenteditable="true">{{ $data['place'] }}</span></td>
        </tr>
        <tr>
            <td class="k">Bahagian :</td>
            <td><span class="v" contenteditable="true">{{ $data['department'] }}</span></td>
            <td class="k">Nama Pengeluar:</td>
            <td><span class="v" contenteditable="true">{{ $data['issuer'] }}</span></td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th rowspan="2" style="width:5%">Bil.</th>
                <th rowspan="2" style="width:15%">No. Siri Pendaftaran</th>
                <th rowspan="2">Keterangan Aset</th>
                <th colspan="2">Tarikh</th>
                <th rowspan="2" style="width:9%">(Lulus/ Tidak Lulus)</th>
                <th colspan="2">Tarikh</th>
                <th rowspan="2" style="width:11%">Catatan</th>
            </tr>
            <tr>
                <th style="width:11%">Dipinjam</th>
                <th style="width:11%">Dijangka Pulang</th>
                <th style="width:12%">Dipulangkan</th>
                <th style="width:11%">Diterima</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                @php
                    // The officer approves a loan by issuing it; a pending or refused handover is left for them to mark.
                    $approved = in_array($item->status, [\App\Models\AssetAssignment::STATUS_ASSIGNED, \App\Models\AssetAssignment::STATUS_UNASSIGNED], true);
                    $remark = $item->status === \App\Models\AssetAssignment::STATUS_REJECTED
                        ? trim('Ditolak oleh peminjam. '.$item->rejection_reason)
                        : (string) $item->return_note;
                @endphp
                <tr>
                    <td class="c">{{ $loop->iteration }}</td>
                    <td><span class="v" contenteditable="true">{{ $item->asset->asset_code ?? '' }}</span></td>
                    <td><span class="v" contenteditable="true">{{ $item->asset->name ?? '' }}</span></td>
                    <td class="c"><span class="v" contenteditable="true">{{ $date($item->assigned_date) }}</span></td>
                    <td class="c"><span class="v" contenteditable="true">{{ $date($item->due_date) }}</span></td>
                    <td class="c"><span class="v" contenteditable="true">{{ $approved ? 'Lulus' : '' }}</span></td>
                    <td class="c"><span class="v" contenteditable="true">{{ $date($item->returned_date) }}</span></td>
                    <td class="c"><span class="v" contenteditable="true">{{ $date($item->returned_date) }}</span></td>
                    <td><span class="v" contenteditable="true">{{ $remark }}</span></td>
                </tr>
            @endforeach
            @for($i = 0; $i < $blankRows; $i++)
                <tr>
                    <td class="c"></td>
                    @for($c = 0; $c < 8; $c++)<td><span class="v" contenteditable="true"></span></td>@endfor
                </tr>
            @endfor
        </tbody>
    </table>

    <table class="sign">
        @foreach($signatures as $pair)
            <tr>
                @foreach($pair as [$caption, $name, $position, $signed])
                    <td>
                        <div class="dots">…………………………………</div>
                        <div>({{ $caption }})</div>
                        <div class="who"><b>Nama</b>: <span class="v" contenteditable="true">{{ $name }}</span></div>
                        <div class="who"><b>Jawatan</b>: <span class="v" contenteditable="true">{{ $position }}</span></div>
                        <div class="who"><b>Tarikh</b>: <span class="v" contenteditable="true">{{ $signed }}</span></div>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
</section>
