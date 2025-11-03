<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
</head>

<body style="font-family: DejaVu Sans, sans-serif; color:#000; font-size:12px; margin:0; padding:0;">
@php
    $mapPath  = public_path('images/map.png');
    $mapData  = file_exists($mapPath) ? base64_encode(file_get_contents($mapPath)) : null;

    // choose a logo that exists (reuse from before if you like)
    $logoPath = public_path('images/CIPWhite.webp');
    if (!file_exists($logoPath)) {
        foreach (['sl1.png','sl2.png','sl3.png'] as $f) {
            if (file_exists(public_path("images/$f"))) { $logoPath = public_path("images/$f"); break; }
        }
    }
    $ext  = $logoPath ? strtolower(pathinfo($logoPath, PATHINFO_EXTENSION)) : null;
    $mime = $ext === 'png' ? 'image/png' : ($ext === 'jpg' || $ext === 'jpeg' ? 'image/jpeg' : 'image/webp');
    $logoData = $logoPath ? base64_encode(file_get_contents($logoPath)) : null;
@endphp


    {{-- ===== HEADER ===== --}}
<div style="position:relative; height:180px; background:#000; overflow:hidden;">
  {{-- @if($mapData)
    <img
      src="data:image/png;base64,{{ $mapData }}"
      alt=""
      style="position:absolute; top:0; left:0; width:100%; height:100%; opacity:0.13; z-index:1; display:block;"
    />
  @endif --}}

  <table width="100%" cellspacing="0" cellpadding="0"
         style="position:relative; height:600px; z-index:2; background:transparent; color:#fff; padding:35px 25px;">
    <tr height="600px;">
      <td width="50%" valign="middle" style="padding:10px;">
        @if($logoData)
          <img src="data:{{ $mime }};base64,{{ $logoData }}" alt="Creative IT Park" style="height:60px; display:block;">
        @else
          <strong>Creative IT Park</strong>
        @endif
      </td>
      <td width="50%" align="right" valign="middle" style="font-size:12px; line-height:1.8;">
        <div style="font-weight:bold; font-size:16px;">Creative IT Park</div>
        <div>03126503550</div>
        <div>info@creativeitpark.org</div>
        <div>Blue Area, Islamabad</div>
      </td>
    </tr>
  </table>
</div>

{{-- map image for debugging --}}
@if($mapData)
    <img
      src="data:image/png;base64,{{ $mapData }}"
      alt=""
      style="position:absolute; top:-250; left:0; width:100%; height:100%; opacity:0.13; z-index:1; display:block;"
    />
@endif

    {{-- ===== INVOICE TITLE & INFO ===== --}}
    <table width="100%" cellspacing="0" cellpadding="5" style="margin:25px 0 10px 0;">
        <tr>
            <td align="center" style="font-size:20px; font-weight:bold;">Invoice</td>
        </tr>
    </table>

    <table width="100%" cellspacing="0" cellpadding="5" style="margin-bottom:10px;">
        <tr>
            <td width="60%" valign="top">
                <strong>Billed To</strong><br>
                {{ optional($invoice->client)->name ?? 'Client Name' }}<br>
                {{ optional($invoice->client)->email ?? 'client@email.com' }}
            </td>
            <td width="40%" valign="top" align="right">
                <table cellspacing="0" cellpadding="3" align="right" style="font-size:12px;">
                    <tr>
                        <td align="right"><strong>Invoice Number:</strong></td>
                        <td>{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td align="right"><strong>Invoice Date:</strong></td>
                        <td>{{ $invoice->issue_date->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td align="right"><strong>Due Date:</strong></td>
                        <td>{{ $invoice->due_date->format('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td colspan="2" align="right">
                            <div
                                style="display:inline-block; border:1px solid red; color:red; padding:2px 10px; font-weight:bold; border-radius:3px;">
                                UNPAID</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ===== ITEMS TABLE (with totals included) ===== --}}
    <table width="100%" cellspacing="0" cellpadding="8" border="1"
        style="border-collapse:collapse; border-color:#dcdcdc; font-size:12px;">
        <thead>
            <tr style="background:#ff9900; color:#fff; text-align:center;">
                <th align="left" style="padding:8px;">Description</th>
                <th width="15%">Quantity</th>
                <th width="20%">Unit Price (USD)</th>
                <th width="15%">Tax</th>
                <th width="20%">Amount (USD)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td style="padding:8px;">{{ $item->item_name }}</td>
                    <td align="center">{{ $item->quantity }} {{ optional($item->unit)->unit_type }}</td>
                    <td align="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td align="right">
                        @if ($item->taxes)
                            @foreach (json_decode($item->taxes) as $tax)
                                @php $taxData = \App\Models\InvoiceItems::taxbyid($tax)->first(); @endphp
                                {{ $taxData ? $taxData->tax_name . ' ' . $taxData->rate_percent . '%' : '' }}
                            @endforeach
                        @endif
                    </td>
                    <td align="right">{{ number_format($item->amount, 2) }}</td>
                </tr>

                {{-- Description + Image directly under item --}}
                @if ($item->item_summary || $item->invoiceItemImage)
                    <tr>
                        <td colspan="5" style="border-top:0; padding:5px 10px; font-size:11px; color:#444;">
                            {!! nl2br($item->item_summary) !!}
                            @if ($item->invoiceItemImage)
                                <img src="{{ public_path('storage/' . $item->invoiceItemImage->filename) }}"
                                    width="80" height="80"
                                    style="margin-top:5px; border:1px solid #ccc; border-radius:4px;">
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach

            {{-- Totals merged into the same table --}}
            <tr>
                <td colspan="3" style="border:none;"></td>
                <td align="right" style="padding:6px; font-weight:bold;">Sub Total</td>
                <td align="right" style="padding:6px;">{{ number_format($invoice->sub_total, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" style="border:none;"></td>
                <td align="right" style="padding:6px; font-weight:bold;">Discount: 5%</td>
                <td align="right" style="padding:6px;">{{ number_format(($invoice->sub_total * 5) / 100, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" style="border:none;"></td>
                <td align="right" style="padding:6px; font-weight:bold;">Total</td>
                <td align="right" style="padding:6px;">{{ number_format($invoice->total, 2) }}</td>
            </tr>
            <tr style="background:#ff9900; color:#fff;">
                <td colspan="3" style="border:none;"></td>
                <td align="right" style="padding:6px; font-weight:bold;">Total Paid</td>
                <td align="right" style="padding:6px;">0.00 USD</td>
            </tr>
            <tr style="background:#ff9900; color:#fff;">
                <td colspan="3" style="border:none;"></td>
                <td align="right" style="padding:6px; font-weight:bold;">Total Due</td>
                <td align="right" style="padding:6px;">{{ number_format($invoice->amountDue(), 2) }} USD</td>
            </tr>
        </tbody>
    </table>


    {{-- ===== PAYMENT SECTION ===== --}}
    <table width="100%" cellspacing="0" cellpadding="8"
        style="margin-top:20px; border-collapse:collapse;">
        <tr>
            <td width="70%">
                <strong style="font-size:13px;">PLEASE MAKE A PAYMENT TO</strong><br><br>
                <table cellspacing="0" cellpadding="3" style="font-size:12px; line-height:1.8;">
                    <tr>
                        <td width="130" style="font-weight:bold;">Account Title:</td>
                        <td>Creative IT Park (Private) Limited</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Account Number:</td>
                        <td>3552301000005914</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">IBAN:</td>
                        <td>PK13FAYS3552301000005914</td>
                    </tr>
                    <tr>
                        <td style="font-weight:bold;">Bank Address:</td>
                        <td>Faysal Bank Limited, Blue Area Branch Islamabad</td>
                    </tr>
                </table>
            </td>
            <td width="30%" align="right" valign="bottom">
                <strong>Signature:</strong><br><br>
                <img src="{{ public_path('images/company-stamp.png') }}" alt="Stamp" style="height:60px;">
            </td>
        </tr>
    </table>

    {{-- ===== NOTE + TERMS SECTION (separate, below payment) ===== --}}
    <table width="100%" cellspacing="0" cellpadding="8" style="margin-top:20px; font-size:12px;">
        <tr valign="top">
            <td width="70%">
                <strong>Note</strong><br>
                Repellendus Consequ
            </td>
            <td width="30%" align="right">
                <strong>Terms and Conditions</strong><br>
                Thank you for your business.
            </td>
        </tr>
    </table>

</body>

</html>
