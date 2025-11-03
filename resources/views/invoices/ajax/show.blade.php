<style>
    #logo {
        height: 50px;
    }

    #signatory img {
        height: 95px;
        margin-bottom: -40px;
        margin-top: 5px;
        margin-right: 15px;
    }
</style>
<style>
    @page {
        size: A4;
        margin: 25mm 15mm 15mm 15mm;
        /* top, right, bottom, left */
    }

    @page :first {
        margin-top: 0mm;
        /* remove top margin for first page */
    }

    @media print {

        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        .no-print {
            display: none;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50px;
            background: linear-gradient(0deg, #ff9900 50%, #ffb84d 100%);
            color: #fff;
            text-align: center;
            line-height: 50px;
            font-size: 11pt;
        }




    }

    body,
    html {
        margin: 0;
        padding: 0;
        font-family: 'Times New Roman', Times, serif;
        background: #fff;
        color: #000;
        height: 100%;
    }

    .page {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: white;
        display: flex;
        flex-direction: column;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        position: relative;
        page-break-after: auto;
    }

    /* HEADER */
    .header {
        height: 220px;
        background: linear-gradient(180deg, #000 70%, #1a1a1a 100%);
        color: white;
        padding: 20px 40px;
        position: relative;
        overflow: hidden;
    }

    .header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('{{ asset('images/map.png') }}') center / cover no-repeat;
        opacity: 0.13
    }

    .me-2 {
        margin-right: 1.0rem !important;
    }

    .fs-5 {
        font-size: 1.5rem !important;
    }

    .topright {
        position: absolute;
        top: 205px;
        /* same as header height */
        right: 30px;
        z-index: 10;
    }

    .bottomleft {
        position: absolute;
        bottom: 60px;
        /* same as footer height */
        left: 30px;
        z-index: 10;
        transform: translate(20px, 0) rotate(180deg) scale(1.2);


    }


    .logo-container {
        position: relative;
        z-index: 2;
    }

    .logo-svg {
        height: 60px;
        margin-right: 15px;
    }

    .company-name {
        line-height: 1.1;
    }

    .company-name .main {
        font-size: 28px;
        letter-spacing: 1px;
    }

    .company-name .creative {
        color: white;
    }

    .company-name .it {
        color: #ff9900;
        font-style: italic;
    }

    .company-name .park {
        color: white;
    }

    .company-name small {
        font-size: 13px;
        color: #ccc;
        font-weight: normal;
    }

    .contact-info {
        font-size: 14px;
        line-height: 1.7;
        padding-left: 38px;

    }

    .contact-info .icon {
        width: 14px;
        height: 14px;
        fill: #ff9900;
        margin-right: 6px;
    }

    .contact-info a {
        color: white;
        text-decoration: none;
    }

    /* CONTENT */
    #content {
        flex: 1;
        padding: 50px 60px;
        font-size: 12pt;
        line-height: 1.8;
    }

    #content p {
        margin-bottom: 16px;
    }

    .signature-block {
        margin-top: 50px;
    }

    .signature-line {
        width: 220px;
        border-top: 1px solid #000;
        position: relative;
        margin: 40px 0 10px;
    }

    /* .stamp {
                position: absolute;
                left: 70px;
                top: -30px;
                width: 90px;
                height: 90px;
                background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="45" fill="none" stroke="%23000" stroke-width="2"/><circle cx="50" cy="50" r="35" fill="none" stroke="%23000" stroke-width="1"/><text x="50" y="45" font-size="9" font-family="Arial" text-anchor="middle" fill="%23000">CREATIVE</text><text x="50" y="58" font-size="9" font-family="Arial" text-anchor="middle" fill="%23000">IT PARK</text></svg>') center/contain no-repeat;
                opacity: 0.7;
            } */

    .name-title {
        font-weight: bold;
        margin-bottom: 2px;
    }

    .designation {
        font-size: 11pt;
        color: #333;
    }

    /* FOOTER */
    .footer {
        height: 60px;
        background: linear-gradient(0deg, #ff9900 50%, #ffb84d 100%);
        position: relative;
        overflow: hidden;
    }

    .footer::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 20px;

    }
</style>

@php
    $addPaymentPermission = user()->permission('add_payments');
    $deleteInvoicePermission = user()->permission('delete_invoices');
    $editInvoicePermission = user()->permission('edit_invoices');
@endphp

@if (!in_array('client', user_roles()))
    @if (!is_null($invoice->last_viewed))
        <x-alert type="info">
            {{ $invoice->client->name_salutation }} @lang('app.viewedOn')
            {{ $invoice->last_viewed->timezone($settings->timezone)->translatedFormat($settings->date_format) }}
            @lang('app.at')
            {{ $invoice->last_viewed->timezone($settings->timezone)->translatedFormat($settings->time_format) }}
            @lang('app.usingIpAddress'):{{ $invoice->ip_address }}
        </x-alert>
    @endif
@endif

<!-- INVOICE CARD START -->
@if (!is_null($invoice->client_id) && !is_null($invoice->clientDetails))
    @php
        $client = $invoice->client;
    @endphp
@elseif (
    !is_null($invoice->project) &&
        !is_null($invoice->project->client) &&
        !is_null($invoice->project->client->clientDetails))
    @php
        $client = $invoice->project->client;
    @endphp
@endif

@if (!$invoice->send_status && $invoice->status != 'canceled' && $invoice->amountDue() > 0)
    <x-alert icon="info-circle" type="warning">
        @lang('messages.unsentInvoiceInfo')
    </x-alert>
@endif

<div class="card border-0 invoice">
    <style>
        #logo {
            height: 50px;
        }

        #signatory img {
            height: 95px;
            margin-bottom: -40px;
            margin-top: 5px;
            margin-right: 15px;
        }
    </style>
    <style>
        @page {
            size: A4;
            margin: 25mm 15mm 15mm 15mm;
        }

        @page :first {
            margin-top: 0mm;
        }

        @media print {

            body,
            html {
                margin: 0;
                padding: 0;
                height: 100%;
            }

            .no-print {
                display: none;
            }

            .footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 50px;
                background: linear-gradient(0deg, #ff9900 50%, #ffb84d 100%);
                color: #fff;
                text-align: center;
                line-height: 50px;
                font-size: 11pt;
            }
        }

        body,
        html {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: #fff;
            color: #000;
            height: 100%;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            page-break-after: auto;
        }

        /* HEADER - Two Column Design */
        .header {
            height: 100px;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        .header-left {
            width: 50%;
            background: #000;
            padding: 20px 30px;
            display: flex;
            align-items: center;
        }

        .header-right {
            width: 50%;
            padding: 20px 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-size: 11px;
            line-height: 1.6;
            color: #ffffff;
        }

        .logo-svg {
            height: 60px;
            margin-right: 15px;
        }

        .contact-info {
            font-size: 11px;
            line-height: 1.6;
            text-align: right;
        }

        .contact-info .icon {
            width: 14px;
            height: 14px;
            margin-right: 6px;
        }

        .contact-info a {
            color: #000;
            text-decoration: none;
        }

        .contact-info .d-flex {
            justify-content: flex-end;
            margin-bottom: 3px;
        }

        /* CONTENT */
        #content {
            flex: 1;
            padding: 30px 60px;
            font-size: 12pt;
            line-height: 1.8;
        }

        #content p {
            margin-bottom: 16px;
        }

        /* Invoice Title Centered */
        .invoice-title-center {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            padding: 20px 0;
            color: #000;
        }

        .signature-block {
            margin-top: 50px;
        }

        .signature-line {
            width: 220px;
            border-top: 1px solid #000;
            position: relative;
            margin: 40px 0 10px;
        }

        .name-title {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .designation {
            font-size: 11pt;
            color: #333;
        }

        /* FOOTER */
        .footer {
            height: 60px;
            background: linear-gradient(0deg, #ff9900 50%, #ffb84d 100%);
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 20px;
        }

        /* Table Styling to Match Image */
        .invoice-table-wrapper table {
            font-size: 12px;
        }

        .inv-detail thead tr {
            background: #ff9900 !important;
            color: #000 !important;
        }

        .inv-detail thead th {
            background: #ff9900 !important;
            color: #000 !important;
            font-weight: bold !important;
            border: 1px solid #e69500 !important;
        }

        .bg-light-grey {
            background: #ff9900 !important;
            color: #000 !important;
            font-weight: bold !important;
        }

        .me-2 {
            margin-right: 1.0rem !important;
        }

        .fs-5 {
            font-size: 1.5rem !important;
        }
    </style>
    <style>
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('{{ asset('images/map.png') }}') center/cover no-repeat;
            opacity: 0.13;
        }

        .header-table {
            width: 100%;
            position: relative;
            z-index: 2;
        }

        .logo-cell {
            width: 55%;
            vertical-align: middle;
        }

        .info-cell {
            width: 45%;
            vertical-align: middle;
            text-align: right;
        }

        .logo-svg {
            height: 70px;
            width: auto;
        }

        .contact-info {
            font-size: 13px;
            line-height: 1.8;
            color: #fff;
        }

        .contact-info div {
            margin-bottom: 4px;
        }

        .contact-info a {
            color: white;
            text-decoration: none;
        }

        .company-name {
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 6px;
        }
    </style>

    <!-- CARD BODY START -->
    <div class="card-body">

        <!-- HEADER -->
        <header class="header"
            style="
    height: 220px;
    background: linear-gradient(180deg, #000 70%, #1a1a1a 100%);
    color: white;
    padding: 30px 50px;
    position: relative;
    overflow: hidden;">
            <table class="header-table">
                <tr>
                    {{-- Left: Company Logo --}}
                    <td class="logo-cell">
                        <img src="{{ invoice_setting()->logo_url }}" alt="{{ company()->company_name }}"
                            class="logo-svg">
                    </td>

                    {{-- Right: Company Info --}}
                    <td class="info-cell">
                        <div class="contact-info">
                            {{-- Company Name --}}
                            <div class="company-name">
                                {{ company()->company_name }}
                            </div>

                            {{-- Phone --}}
                            @if (company()->company_phone)
                                <div>
                                    {{ company()->company_phone }}
                                </div>
                            @endif

                            {{-- Email --}}
                            @if (company()->company_email)
                                <div>
                                    <a href="mailto:{{ company()->company_email }}">{{ company()->company_email }}</a>
                                </div>
                            @endif

                            {{-- Address --}}
                            @if (!is_null($settings) && $invoice->address)
                                <div>
                                    {!! nl2br($invoice->address->address) !!}
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </header>
        <!-- HEADER End -->

        <div class="topright">
            <svg width="162" height="130" viewBox="0 0 272 187" fill="none" xmlns="http://www.w3.org/2000/svg"
                opacity="0.2">
                <path
                    d="M266.994 37.102V75.53C269.828 75.443 271.998 77.897 271.998 80.863C271.998 84.173 269.304 86.866 265.994 86.866C264.156 86.866 262.528 86.018 261.426 84.713L228.272 103.847C228.515 104.499 228.666 105.195 228.666 106.2C228.666 109.24 225.973 112.22 222.662 112.22C219.351 112.22 216.658 109.24 216.658 106.2C216.658 105.177 216.813 104.463 217.68 103.798L183.931 84.673C182.829 86.001 181.187 86.866 179.33 86.866C177.473 86.866 175.831 86.001 174.729 84.673L141.593 103.797C141.848 104.462 142.3 105.177 142.3 106.2C142.3 108.898 139.833 111.352 136.999 111.831V150.006C139.833 150.485 142.3 153.3 142.3 156.6C142.3 156.636 141.853 157.326 141.614 158.63L174.731 177.084C175.833 175.757 177.474 174.894 179.33 174.894C182.641 174.894 185.335 177.585 185.335 180.895C185.335 184.205 182.641 186.896 179.33 186.896C176.2 186.896 173.326 184.205 173.326 180.895C173.326 180.16 173.478 179.463 173.721 178.812L140.611 159.703C139.509 161.038 137.862 162.8 135.999 162.8C134.136 162.8 132.49 161.039 131.388 159.704L98.277 178.812C98.52 179.464 98.671 180.16 98.671 180.895C98.671 184.205 95.978 186.896 92.667 186.896C89.356 186.896 86.663 184.205 86.663 180.895C86.663 180.16 86.814 179.464 87.57 178.813L53.946 159.704C52.844 161.039 51.198 162.8 49.335 162.8C46.25 162.8 43.331 159.217 43.331 156.6C43.331 153.3 45.501 150.486 48.334 150.006V111.831C45.501 111.351 43.331 108.898 43.331 106.2C43.331 105.195 43.482 104.499 43.725 103.847L10.571 84.714C9.469 86.018 7.842 86.866 6.4 86.866C2.693 86.866 0 84.173 0 80.865C0 77.897 2.169 75.443 5.3 75.53V36.756C2.169 36.276 0 33.823 0 30.855C0 27.546 2.693 24.853 6.4 24.853C7.877 24.853 9.531 25.733 10.633 27.081L43.725 8.72C43.482 7.331 43.331 6.635 43.331 5.9C43.331 2.591 46.25 0 49.335 0C52.646 0 55.339 2.591 55.339 5.9C55.339 6.635 55.188 7.331 54.945 8.72L88.37 27.081C89.139 25.733 90.794 24.853 92.667 24.853C94.54 24.853 96.195 25.733 97.297 27.081L130.389 8.72C130.146 7.331 129.994 6.635 129.994 5.9C129.994 2.591 132.688 0 135.999 0C139.309 0 142.3 2.591 142.3 5.9C142.3 6.635 141.852 7.331 141.609 8.72L174.895 27.193C175.994 26.68 177.567 25.2 179.33 25.2C181.94 25.2 182.667 26.68 183.766 27.192L217.52 8.72C216.809 7.331 216.658 6.635 216.658 5.9C216.658 2.591 219.351 0 222.662 0C225.973 0 228.666 2.591 228.666 5.9C228.666 6.635 228.515 7.331 228.272 8.72L261.558 27.192C262.657 26.68 264.23 25.2 265.994 25.2C269.304 25.2 271.998 27.892 271.998 31.202C271.998 34.168 269.828 36.623 266.994 37.102ZM179.33 184.897C181.538 184.897 183.333 183.103 183.333 180.895C183.333 178.689 181.538 176.894 179.33 176.894C177.123 176.894 175.328 178.689 175.328 180.895C175.328 183.103 177.123 184.897 179.33 184.897ZM265.994 84.866C268.201 84.866 269.997 83.07 269.997 80.863C269.997 78.657 268.201 76.863 265.994 76.863C263.787 76.863 261.991 78.657 261.991 80.863C261.991 83.07 263.787 84.866 265.994 84.866ZM222.662 110.22C224.869 110.22 226.665 108.138 226.665 106.2C226.665 103.723 224.869 102.19 222.662 102.19C220.455 102.19 218.659 103.723 218.659 106.2C218.659 108.138 220.455 110.22 222.662 110.22ZM179.33 84.866C181.538 84.866 183.333 83.071 183.333 80.865C183.333 78.657 181.538 76.864 179.33 76.864C177.123 76.864 175.327 78.657 175.327 80.865C175.327 83.071 177.123 84.866 179.33 84.866ZM140.1 106.2C140.1 103.723 138.206 102.19 135.999 102.19C133.791 102.19 131.996 103.723 131.996 106.2C131.996 108.138 133.791 110.22 135.999 110.22C138.206 110.22 140.1 108.138 140.1 106.2ZM135.999 160.7C138.206 160.7 140.1 158.114 140.1 156.6C140.1 153.7 138.206 152.4 135.999 152.4C133.791 152.4 131.996 153.7 131.996 156.6C131.996 158.114 133.791 160.7 135.999 160.7ZM92.667 184.897C94.874 184.897 96.67 183.103 96.67 180.895C96.67 178.689 94.874 176.894 92.667 176.894C90.46 176.894 88.664 178.689 88.664 180.895C88.664 183.103 90.46 184.897 92.667 184.897ZM54.95 158.63L88.66 177.085C89.168 175.758 90.81 174.894 92.667 174.894C94.524 174.894 96.166 175.758 97.268 177.085L130.384 158.63C130.145 157.326 129.994 156.636 129.994 156.6C129.994 153.3 132.164 150.486 134.998 150.006V111.831C132.164 111.351 129.994 108.898 129.994 106.2C129.994 105.195 130.146 104.499 130.389 103.847L97.235 84.714C96.132 86.018 94.505 86.866 92.667 86.866C90.811 86.866 89.169 86.001 88.67 84.675L54.93 103.799C55.184 104.463 55.339 105.177 55.339 106.2C55.339 108.898 53.169 111.352 50.336 111.831V150.006C53.169 150.485 55.339 153.3 55.339 156.6C55.339 156.636 55.189 157.326 54.95 158.63ZM96.67 80.865C96.67 78.657 94.874 76.864 92.667 76.864C90.46 76.864 88.664 78.657 88.664 80.865C88.664 83.071 90.46 84.866 92.667 84.866C94.874 84.866 96.67 83.071 96.67 80.865ZM45.332 156.6C45.332 158.114 47.128 160.7 49.335 160.7C51.542 160.7 53.338 158.114 53.338 156.6C53.338 153.7 51.542 152.4 49.335 152.4C47.128 152.4 45.332 153.7 45.332 156.6ZM49.335 110.22C51.542 110.22 53.338 108.138 53.338 106.2C53.338 103.723 51.542 102.19 49.335 102.19C47.128 102.19 45.332 103.723 45.332 106.2C45.332 108.138 47.128 110.22 49.335 110.22ZM2.1 80.865C2.1 83.071 3.796 84.866 6.4 84.866C8.211 84.866 10.6 83.071 10.6 80.865C10.6 78.657 8.211 76.864 6.4 76.864C3.796 76.864 2.1 78.657 2.1 80.865ZM6.4 26.854C3.796 26.854 2.1 28.649 2.1 30.855C2.1 33.062 3.796 34.858 6.4 34.858C8.211 34.858 10.6 33.062 10.6 30.855C10.6 28.649 8.211 26.854 6.4 26.854ZM49.335 1.898C47.128 1.898 45.332 3.693 45.332 5.9C45.332 8.106 47.128 10 49.335 10C51.542 10 53.338 8.106 53.338 5.9C53.338 3.693 51.542 1.898 49.335 1.898ZM87.43 28.817L53.936 9.709C52.834 11.037 51.192 12 49.335 12C47.478 12 45.837 11.037 44.734 9.709L11.627 28.817C11.86 29.456 12.8 30.136 12.8 30.855C12.8 33.823 9.838 36.277 7.4 36.756V75.53C9.838 75.443 12.8 77.897 12.8 80.865C12.8 81.618 11.853 82.332 11.598 83.87L44.734 102.121C45.837 100.793 47.478 100.19 49.335 100.19C51.174 100.19 52.801 100.776 53.903 102.081L87.57 83.38C86.814 82.296 86.663 81.6 86.663 80.865C86.663 77.897 88.833 75.443 91.666 75.53V36.756C88.833 36.276 86.663 33.823 86.663 30.855C86.663 30.136 86.811 29.456 87.43 28.817ZM92.667 26.854C90.46 26.854 88.664 28.649 88.664 30.855C88.664 33.062 90.46 34.858 92.667 34.858C94.874 34.858 96.67 33.062 96.67 30.855C96.67 28.649 94.874 26.854 92.667 26.854ZM135.999 1.898C133.791 1.898 131.996 3.693 131.996 5.9C131.996 8.106 133.791 10 135.999 10C138.206 10 140.1 8.106 140.1 5.9C140.1 3.693 138.206 1.898 135.999 1.898ZM173.798 28.869L140.6 9.709C139.497 11.037 137.856 12 135.999 12C134.142 12 132.5 11.037 131.398 9.709L98.29 28.817C98.523 29.456 98.671 30.136 98.671 30.855C98.671 33.823 96.501 36.277 93.667 36.756V75.53C96.501 75.443 98.671 77.897 98.671 80.865C98.671 81.618 98.516 82.332 98.261 83.87L131.398 102.121C132.5 100.793 134.142 100.19 135.999 100.19C137.836 100.19 139.463 100.776 140.566 102.08L173.72 83.36C173.477 82.295 173.326 81.599 173.326 80.865C173.326 77.897 175.496 75.443 178.329 75.53V37.102C175.496 36.622 173.326 34.168 173.326 31.202C173.326 30.375 173.494 29.587 173.798 28.869ZM179.33 27.2C177.123 27.2 175.328 29.85 175.328 31.202C175.328 33.408 177.123 35.202 179.33 35.202C181.538 35.202 183.333 33.408 183.333 31.202C183.333 29.85 181.538 27.2 179.33 27.2ZM222.662 1.898C220.455 1.898 218.659 3.693 218.659 5.9C218.659 8.106 220.455 10 222.662 10C224.869 10 226.665 8.106 226.665 5.9C226.665 3.693 224.869 1.898 222.662 1.898ZM260.462 28.869L227.263 9.709C226.161 11.037 224.519 12 222.662 12C220.805 12 219.163 11.037 218.61 9.709L184.863 28.869C185.166 29.587 185.335 30.375 185.335 31.202C185.335 34.168 183.165 36.623 180.331 37.102V75.53C183.164 75.443 185.334 77.897 185.334 80.865C185.334 81.599 185.183 82.295 184.94 83.36L218.95 102.08C219.197 100.776 220.824 100.19 222.662 100.19C224.519 100.19 226.161 100.793 227.263 102.121L260.399 83.86C260.145 82.331 259.989 81.617 259.989 80.863C259.989 77.897 262.159 75.443 264.993 75.53V37.102C262.159 36.622 259.989 34.168 259.989 31.202C259.989 30.375 260.158 29.587 260.462 28.869ZM265.994 27.2C263.787 27.2 261.991 29.85 261.991 31.202C261.991 33.408 263.787 35.202 265.994 35.202C268.201 35.202 269.997 33.408 269.997 31.202C269.997 29.85 268.201 27.2 265.994 27.2Z"
                    fill="grey"></path>
                <defs>
                    <linearGradient id="paint0_linear_663_106" x1="0" y1="187" x2="0"
                        y2="0" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#F57C00"></stop>
                        <stop offset="0.75" stop-color="#F57C00" stop-opacity="0.5"></stop>
                        <stop offset="1" stop-color="#F57C00" stop-opacity="0"></stop>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <!-- Invoice Title Centered -->
        <div class="invoice-title-center">@lang('app.invoice')</div>

        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                <i class="fa fa-check"></i> {!! $message !!}
            </div>
            <?php Session::forget('success'); ?>
        @endif

        @if ($message = Session::get('error'))
            <div class="custom-alerts alert alert-danger fade in">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button>
                {!! $message !!}
            </div>
            <?php Session::forget('error'); ?>
        @endif

        <div class="invoice-table-wrapper">
            <table width="100%">
                <tr>
                    <td height="20"></td>
                </tr>
            </table>
            <table width="100%">
                <tr class="inv-unpaid">
                    <td class="f-14 text-dark">
                        <p class="mb-0 text-left">
                            @if ($invoice->client || $invoice->clientDetails)
                                <span class="text-dark-grey font-weight-bold">@lang('modules.invoices.billedTo')</span>
                                <br>

                                @if ($invoice->client && $invoice->client->name && invoice_setting()->show_client_name == 'yes')
                                    {{ $invoice->client->name_salutation }}<br>
                                @endif

                                @if ($invoice->client && $invoice->client->email && invoice_setting()->show_client_email == 'yes')
                                    {{ $invoice->client->email }}<br>
                                @endif

                                @if ($invoice->client && $invoice->client->mobile && invoice_setting()->show_client_phone == 'yes')
                                    {{ $invoice->client->mobile_with_phonecode }}<br>
                                @endif

                                @if (
                                    $invoice->clientDetails &&
                                        $invoice->clientDetails->company_name &&
                                        invoice_setting()->show_client_company_name == 'yes')
                                    {{ $invoice->clientDetails->company_name }}<br>
                                @endif

                                @if (
                                    $invoice->clientDetails &&
                                        $invoice->clientDetails->address &&
                                        invoice_setting()->show_client_company_address == 'yes')
                                    {!! nl2br($invoice->clientDetails->address) !!}
                                @endif

                            @endif

                            @if ($invoiceSetting->show_project == 1 && isset($invoice->project))
                                <br><br>
                                <span class="text-dark-grey ">@lang('modules.invoices.projectName')</span>
                                <br>
                                {{ $invoice->project->project_name }}
                            @endif

                            @if ($invoiceSetting->show_gst == 'yes' && !is_null($client->clientDetails->gst_number))
                                @if ($client->clientDetails->tax_name)
                                    <br>{{ $client->clientDetails->tax_name }}:
                                    {{ $client->clientDetails->gst_number }}
                                @else
                                    <br>@lang('app.gstIn'): {{ $client->clientDetails->gst_number }}
                                @endif
                            @endif
                        </p>
                    </td>
                    @if ($invoice->show_shipping_address == 'yes')
                        <td class="f-14 text-black">
                            <p class="mb-0 text-left"><span
                                    class="text-dark-grey font-weight-bold">@lang('app.shippingAddress')</span><br>
                                {!! nl2br($client->clientDetails->shipping_address) !!}</p>
                        </td>
                    @endif
                    <td align="right" class="mt-2 mt-lg-0 mt-md-0">
                        @if ($invoice->clientDetails->company_logo)
                            <img src="{{ $invoice->clientDetails->image_url }}"
                                alt="{{ $invoice->clientDetails->company_name }}" class="logo"
                                style="height:50px;" />
                            <br><br><br>
                        @endif
                        <table class="inv-num-date text-dark f-13 mt-3" style="float: right;">
                            <tr>
                                <td class="font-weight-bold" style="padding: 5px 10px; border:none;">
                                    @lang('modules.invoices.invoiceNumber'):</td>
                                <td style="padding: 5px 10px; border:none;">{{ $invoice->invoice_number }}</td>
                            </tr>
                            @if ($creditNote)
                                <tr>
                                    <td class="font-weight-bold" style="padding: 5px 10px; border:none;">
                                        @lang('app.credit-note'):</td>
                                    <td style="padding: 5px 10px; border:none;">{{ $creditNote->cn_number }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="font-weight-bold" style="padding: 5px 10px; border:none;">
                                    @lang('modules.invoices.invoiceDate'):</td>
                                <td style="padding: 5px 10px; border:none;">
                                    {{ $invoice->issue_date->translatedFormat(company()->date_format) }}
                                </td>
                            </tr>

                            @if (empty($invoice->order_id) && $invoice->status === 'unpaid' && $invoice->due_date->year > 1)
                                <tr>
                                    <td class="font-weight-bold" style="padding: 5px 10px; border:none;">
                                        @lang('app.dueDate'):</td>
                                    <td style="padding: 5px 10px; border:none;">
                                        {{ $invoice->due_date->translatedFormat(company()->date_format) }}
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
                <tr class="inv-unpaid">
                    <td class="f-14 text-dark">
                    </td>
                    @if ($invoice->show_shipping_address == 'yes')
                        <td class="f-14 text-black">
                            <p class="mb-0 text-left"><span
                                    class="text-dark-grey font-weight-bold">@lang('app.shippingAddress')</span><br>
                                {!! nl2br($client->clientDetails->shipping_address) !!}</p>
                        </td>
                    @endif
                    <td align="right" class="mt-2 mt-lg-0 mt-md-0">
                        @if ($invoice->clientDetails->company_logo)
                            <img src="{{ $invoice->clientDetails->image_url }}"
                                alt="{{ $invoice->clientDetails->company_name }}" class="logo"
                                style="height:50px;" />
                            <br><br><br>
                        @endif
                        <br>
                        @if ($invoice->credit_note)
                            <span class="unpaid text-warning border-warning rounded">@lang('app.credit-note')</span>
                        @else
                            <span
                                class="unpaid {{ $invoice->status == 'partial' ? 'text-primary border-primary' : '' }}
                            {{ $invoice->status == 'paid' ? 'text-success border-success' : '' }} rounded f-15 ">
                                @lang('modules.invoices.' . $invoice->status)
                            </span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td height="30" colspan="2"></td>
                </tr>
            </table>
            <table width="100%" class="inv-desc d-none d-lg-table d-md-table">
                <tr>
                    <td colspan="2">
                        <table class="inv-detail f-14 table-responsive-sm" width="100%">
                            <tr class="i-d-heading bg-light-grey text-dark-grey font-weight-bold">
                                <td class="border-right-0" width="35%">@lang('app.description')</td>
                                @if ($invoiceSetting->hsn_sac_code_show)
                                    <td class="border-right-0 border-left-0" align="right">@lang('app.hsnSac')</td>
                                @endif
                                <td class="border-right-0 border-left-0" align="right">
                                    @lang('modules.invoices.qty')
                                </td>
                                <td class="border-right-0 border-left-0" align="right">
                                    @lang('modules.invoices.unitPrice') ({{ $invoice->currency->currency_code }})
                                </td>
                                <td class="border-right-0 border-left-0" align="right">@lang('modules.invoices.tax')</td>
                                <td class="border-left-0" align="right"
                                    width="{{ $invoiceSetting->hsn_sac_code_show ? '17%' : '20%' }}">
                                    @lang('modules.invoices.amount')
                                    ({{ $invoice->currency->currency_code }})
                                </td>
                            </tr>
                            @foreach ($invoice->items->sortBy('field_order') as $item)
                                @if ($item->type == 'item')
                                    <tr class="text-dark font-weight-semibold f-13">
                                        <td>{{ $item->item_name }}</td>
                                        @if ($invoiceSetting->hsn_sac_code_show)
                                            <td align="right">{{ $item->hsn_sac_code }}</td>
                                        @endif
                                        <td align="right">{{ $item->quantity }} @if ($item->unit)
                                                <br><span
                                                    class="f-11 text-dark-grey">{{ $item->unit->unit_type }}</span>
                                            @endif
                                        </td>
                                        <td align="right">
                                            {{ currency_format($item->unit_price, $invoice->currency_id, false) }}</td>
                                        <td align="right">{{ $item->tax_list }}</td>
                                        <td align="right">
                                            {{ currency_format($item->amount, $invoice->currency_id, false) }}
                                        </td>
                                    </tr>
                                    @if ($item->item_summary || $item->invoiceItemImage)
                                        <tr class="text-dark f-12">
                                            <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '6' : '5' }}"
                                                class="border-bottom-0">
                                                {!! nl2br($item->item_summary) !!}
                                                @if ($item->invoiceItemImage)
                                                    <p class="mt-2">
                                                        <a href="javascript:;" class="img-lightbox"
                                                            data-image-url="{{ $item->invoiceItemImage->file_url }}">
                                                            <img src="{{ $item->invoiceItemImage->file_url }}"
                                                                width="80" height="80" class="img-thumbnail">
                                                        </a>
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endif
                            @endforeach

                            <tr>
                                <td colspan="{{ $invoiceSetting->hsn_sac_code_show ? '4' : '3' }}"
                                    class="blank-td border-bottom-0 border-left-0 border-right-0"></td>
                                <td class="p-0 border-right-0" align="right">
                                    <table width="100%">
                                        <tr class="text-dark-grey" align="right">
                                            <td class="w-50 border-top-0 border-left-0">
                                                @lang('modules.invoices.subTotal')</td>
                                        </tr>
                                        @if ($discount != 0 && $discount != '')
                                            <tr class="text-dark-grey" align="right">
                                                <td class="w-50 border-top-0 border-left-0">
                                                    @lang('modules.invoices.discount'): {{ $discountType }}</td>
                                            </tr>
                                        @endif
                                        @foreach ($taxes as $key => $tax)
                                            <tr class="text-dark-grey" align="right">
                                                <td class="w-50 border-top-0 border-left-0">
                                                    {{ $key }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class=" text-dark-grey font-weight-bold" align="right">
                                            <td class="w-50 border-bottom-0 border-left-0">
                                                @lang('modules.invoices.total')</td>
                                        </tr>
                                        <tr class="bg-light-grey text-dark f-w-500 f-16" align="right">
                                            <td class="w-50 border-bottom-0 border-left-0">
                                                @lang('modules.invoices.total')
                                                @lang('modules.invoices.paid')</td>
                                        </tr>
                                        <tr class="bg-light-grey text-dark f-w-500 f-16" align="right">
                                            <td class="w-50 border-bottom-0 border-left-0">
                                                @lang('modules.invoices.total')
                                                @lang('modules.invoices.due')</td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="p-0 border-left-0" align="right">
                                    <table width="100%">
                                        <tr class="text-dark-grey" align="right">
                                            <td class="border-top-0 border-right-0">
                                                {{ currency_format($invoice->sub_total, $invoice->currency_id, false) }}
                                            </td>
                                        </tr>
                                        @if ($discount != 0 && $discount != '')
                                            <tr class="text-dark-grey" align="right">
                                                <td class="border-top-0 border-right-0">
                                                    {{ currency_format($discount, $invoice->currency_id, false) }}</td>
                                            </tr>
                                        @endif
                                        @foreach ($taxes as $key => $tax)
                                            <tr class="text-dark-grey" align="right">
                                                <td class="border-top-0 border-right-0">
                                                    {{ currency_format($tax, $invoice->currency_id, false) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class=" text-dark-grey font-weight-bold" align="right">
                                            <td class="border-bottom-0 border-right-0">
                                                {{ currency_format($invoice->total, $invoice->currency_id, false) }}
                                            </td>
                                        </tr>
                                        <tr class="bg-light-grey text-dark f-w-500 f-16" align="right">
                                            <td class="border-bottom-0 border-right-0">
                                                {{ currency_format($invoice->amountPaid(), $invoice->currency_id, false) }}
                                                {{ $invoice->currency->currency_code }}</td>
                                        </tr>
                                        <tr class="bg-light-grey text-dark f-w-500 f-16" align="right">
                                            <td class="border-bottom-0 border-right-0">
                                                {{ currency_format($invoice->amountDue(), $invoice->currency_id, false) }}
                                                {{ $invoice->currency->currency_code }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>

                </tr>
            </table>
            <table width="100%" class="inv-desc-mob d-block d-lg-none d-md-none">

                @foreach ($invoice->items->sortBy('field_order') as $item)
                    @if ($item->type == 'item')
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang('app.description')</th>
                            <td class="p-0 ">
                                <table>
                                    <tr width="100%" class="font-weight-semibold f-13">
                                        <td class="border-left-0 border-right-0 border-top-0">
                                            {{ $item->item_name }}</td>
                                    </tr>
                                    @if ($item->item_summary != '' || $item->invoiceItemImage)
                                        <tr>
                                            <td class="border-left-0 border-right-0 border-bottom-0 f-12">
                                                {!! nl2br(pdfStripTags($item->item_summary)) !!}
                                                @if ($item->invoiceItemImage)
                                                    <p class="mt-2">
                                                        <a href="javascript:;" class="img-lightbox"
                                                            data-image-url="{{ $item->invoiceItemImage->file_url }}">
                                                            <img src="{{ $item->invoiceItemImage->file_url }}"
                                                                width="80" height="80" class="img-thumbnail">
                                                        </a>
                                                    </p>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang('modules.invoices.qty')</th>
                            <td width="50%">{{ $item->quantity }}</td>
                        </tr>
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang('modules.invoices.unitPrice')
                                ({{ $invoice->currency->currency_code }})
                            </th>
                            <td width="50%">{{ currency_format($item->unit_price, $invoice->currency_id, false) }}
                            </td>
                        </tr>
                        <tr>
                            <th width="50%" class="bg-light-grey text-dark-grey font-weight-bold">
                                @lang('modules.invoices.amount')
                                ({{ $invoice->currency->currency_code }})
                            </th>
                            <td width="50%">{{ currency_format($item->amount, $invoice->currency_id, false) }}</td>
                        </tr>
                        <tr>
                            <td height="3" class="p-0 " colspan="2"></td>
                        </tr>
                    @endif
                @endforeach

                <tr>
                    <th width="50%" class="text-dark-grey font-weight-normal">@lang('modules.invoices.subTotal')
                    </th>
                    <td width="50%" class="text-dark-grey font-weight-normal">
                        {{ currency_format($invoice->sub_total, $invoice->currency_id, false) }}</td>
                </tr>
                @if ($discount != 0 && $discount != '')
                    <tr>
                        <th width="50%" class="text-dark-grey font-weight-normal">@lang('modules.invoices.discount')
                        </th>
                        <td width="50%" class="text-dark-grey font-weight-normal">
                            {{ currency_format($discount, $invoice->currency_id, false) }}</td>
                    </tr>
                @endif

                @foreach ($taxes as $key => $tax)
                    <tr>
                        <th width="50%" class="text-dark-grey font-weight-normal">{{ $key }}</th>
                        <td width="50%" class="text-dark-grey font-weight-normal">
                            {{ currency_format($tax, $invoice->currency_id, false) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th width="50%" class="text-dark-grey font-weight-bold">@lang('modules.invoices.total')</th>
                    <td width="50%" class="text-dark-grey font-weight-bold">
                        {{ currency_format($invoice->total, $invoice->currency_id, false) }}</td>
                </tr>
                <tr>
                    <th width="50%" class="f-16 bg-light-grey text-dark font-weight-bold">
                        @lang('app.totalDue')
                    </th>
                    <td width="50%" class="f-16 bg-light-grey text-dark font-weight-bold">
                        {{ currency_format($invoice->amountDue(), $invoice->currency_id, false) }}
                        {{ $invoice->currency->currency_code }}</td>
                </tr>
            </table>

            @includeIf('invoices.payment_details')
<!-- PLEASE MAKE A PAYMENT TO Section -->
<table width="100%" style="margin-top: 30px;">
    <tr>
        <td style="vertical-align: top; width: 70%;">
            <h4 style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #000;">
                PLEASE MAKE A PAYMENT TO
            </h4>

            <table width="100%" style="font-size: 12px; line-height: 1.8;">
                <!-- Account Title -->
                <tr>
                    <td style="width: 150px; font-weight: bold; color: #333;">Account Title:</td>
                    <td style="color: #333;">Creative IT Park (Private) Limited</td>
                </tr>

                <!-- Account Number -->
                <tr>
                    <td style="width: 150px; font-weight: bold; color: #333;">Account Number:</td>
                    <td style="color: #333;">3552301000005914</td>
                </tr>

                <!-- IBAN -->
                <tr>
                    <td style="width: 150px; font-weight: bold; color: #333;">IBAN:</td>
                    <td style="color: #333;">PK13FAYS3552301000005914</td>
                </tr>

                <!-- Bank Address -->
                <tr>
                    <td style="width: 150px; font-weight: bold; color: #333; vertical-align: top;">
                        Bank Address:
                    </td>
                    <td style="color: #333;">Faysal Bank Limited, Blue Area Branch Islamabad</td>
                </tr>
            </table>
        </td>

        {{-- Signature Section --}}
        <td style="vertical-align: bottom; text-align: right; width: 30%;">
            <table style="width: 100%; text-align: right;">
                <tr>
                    <td style="font-size: 12px; color: #000;">
                        <strong>Signature:</strong>
                    </td>
                    <td style="text-align: right; vertical-align: bottom;">
                        @if ($invoiceSetting->authorised_signatory_signature)
                            <img src="{{ $invoiceSetting->authorised_signatory_signature_url }}"
                                alt="Signature" style="height: 45px; margin-left: 10px;">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right; padding-top: 10px;">
                        <img src="{{ asset('images/company-stamp.png') }}" alt="Company Stamp"
                            style="height: 80px; opacity: 0.85;">
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


            <table class="inv-note">
                <tr>
                    <td height="30" colspan="2"></td>
                </tr>
                <tr>
                    <td>@lang('app.note')</td>
                    <td style="text-align: right;">@lang('modules.invoiceSettings.invoiceTerms')</td>
                </tr>
                <tr>
                    <td style="vertical-align: text-top">
                        <p class="text-dark-grey">{!! !empty($invoice->note) ? nl2br($invoice->note) : '--' !!}</p>
                    </td>
                    <td style="text-align: right;">
                        <p class="text-dark-grey">{!! nl2br($invoiceSetting->invoice_terms) !!}</p>
                    </td>
                </tr>
                @if ($invoiceSetting->other_info)
                    <tr>
                        <td>
                            <p class="text-dark-grey">{!! nl2br($invoiceSetting->other_info) !!}</p>
                        </td>
                    </tr>
                @endif

                <tr>
                    <td colspan="2" align="right">
                        <table>

                            @if (
                                $invoiceSetting->authorised_signatory &&
                                    $invoiceSetting->authorised_signatory_signature &&
                                    $invoice->status == 'paid')
                                <tr align="right">
                                    <td id="signatory">
                                        <img src="{{ $invoiceSetting->authorised_signatory_signature_url }}"
                                            alt="{{ $company->company_name }}" /><br><br>
                                        <p style="margin-top: 25px;">@lang('modules.invoiceSettings.authorisedSignatory')</p>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table>
                            <tr>
                                @if (isset($taxes) && invoice_setting()->tax_calculation_msg == 1)
                                    <p class="text-dark-grey">
                                        @if ($invoice->calculate_tax == 'after_discount')
                                            @lang('messages.calculateTaxAfterDiscount')
                                        @else
                                            @lang('messages.calculateTaxBeforeDiscount')
                                        @endif
                                    </p>
                                @endif
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <!-- CARD BODY END -->
    <!-- CARD FOOTER START -->
    <div class="card-footer bg-white border-0 d-flex justify-content-start py-0 py-lg-4 py-md-4 mb-4 mb-lg-3 mb-md-3 ">

        <div class="d-flex">
            <div class="inv-action mr-3 mr-lg-3 mr-md-3 dropup">
                <button class="dropdown-toggle btn-primary" type="button" id="dropdownMenuButton"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@lang('app.action')
                    <span><i class="fa fa-chevron-up f-15"></i></span>
                </button>
                <!-- DROPDOWN - INFORMATION -->
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton" tabindex="0">

                    @if ($invoice->status == 'paid' && !in_array('client', user_roles()) && $invoice->amountPaid() == 0)
                        <li>
                            <a class="dropdown-item f-14 text-dark"
                                href="{{ route('invoices.edit', [$invoice->id]) }}">
                                <i class="fa fa-edit f-w-500 mr-2 f-11"></i> @lang('app.edit')
                            </a>
                        </li>
                    @endif

                    @php
                        $trashBtn =
                            !is_null($invoice->project) && is_null($invoice->project->deleted_at)
                                ? true
                                : (is_null($invoice->project)
                                    ? true
                                    : false);
                    @endphp

                    @if (
                        $trashBtn &&
                            $invoice->status != 'paid' &&
                            $invoice->status != 'canceled' &&
                            is_null($invoice->invoice_recurring_id) &&
                            ($editInvoicePermission == 'all' ||
                                ($editInvoicePermission == 'added' && $invoice->added_by == user()->id) ||
                                ($editInvoicePermission == 'owned' && $invoice->client_id == user()->id) ||
                                ($editInvoicePermission == 'both' &&
                                    ($invoice->client_id == user()->id || $invoice->added_by == user()->id))))
                        <li>
                            <a class="dropdown-item f-14 text-dark"
                                href="{{ route('invoices.edit', [$invoice->id]) }}">
                                <i class="fa fa-edit f-w-500 mr-2 f-11"></i> @lang('app.edit')
                            </a>
                        </li>
                    @endif

                    @if (
                        ($firstInvoice->id == $invoice->id && $invoice->status == 'unpaid' && $deleteInvoicePermission == 'all') ||
                            ($deleteInvoicePermission == 'added' &&
                                ($invoice->added_by == user()->id || $invoice->added_by == $userId) &&
                                $firstInvoice->id == $invoice->id))
                        <li>
                            <a class="dropdown-item f-14 text-dark delete-invoice" href="javascript:;"
                                data-invoice-id="{{ $invoice->id }}">
                                <i class="fa fa-trash f-w-500 mr-2 f-11"></i> @lang('app.delete')
                            </a>
                        </li>
                    @endif

                    <li>
                        <a class="dropdown-item f-14 text-dark"
                            href="{{ route('invoices.download', [$invoice->id]) }}">
                            <i class="fa fa-download f-w-500 mr-2 f-11"></i> @lang('app.download')
                        </a>
                    </li>

                    @if ($invoice->status == 'paid' && $invoice->file != null)
                        <li>
                            <a class="dropdown-item f-14 text-dark"
                                href="{{ route('invoices.download', [$invoice->id, 'download-uploaded' => true]) }}">
                                <i class="fa fa-download f-w-500 mr-2 f-11"></i> @lang('app.download') @lang('app.uploadedFile')
                            </a>
                        </li>
                    @endif

                    @if ($invoice->status != 'canceled' && !$invoice->credit_note && !in_array('client', user_roles()))
                        <li>
                            <a class="dropdown-item f-14 text-dark sendButton" href="javascript:;"
                                data-invoice-id="{{ $invoice->id }}" data-type="send">
                                <i class="fa fa-paper-plane f-w-500 mr-2 f-11"></i> @lang('app.send')
                            </a>
                        </li>
                        @if ($invoice->send_status == 0)
                            <li>
                                <a class="dropdown-item f-14 text-dark sendButton" href="javascript:;"
                                    data-toggle="tooltip" data-original-title="@lang('messages.markSentInfo')"
                                    data-invoice-id="{{ $invoice->id }}" data-type="mark_as_send">
                                    <i class="fa fa-paper-plane f-w-500 mr-2 f-11"></i> @lang('app.markSent')
                                </a>
                            </li>
                        @endif
                    @endif

                    @if ($invoice->status == 'pending-confirmation' && !in_array('client', user_roles()) && !empty($invoice->payment))
                        <a class="dropdown-item approveButton" href="javascript:;" data-toggle="tooltip"
                            data-invoice-id={{ $invoice->id }}>
                            <i class="fa fa-check mr-2"></i>@lang('app.approve')
                        </a>
                    @endif

                    @if ($invoice->status == 'paid' && !in_array('client', user_roles()) && $invoice->credit_note == 0)
                        <a class="dropdown-item invoice-upload" data-toggle="tooltip"
                            data-original-title="@lang('messages.uploadOtherInvoice')" href="javascript:;" data-toggle="tooltip"
                            data-invoice-id="{{ $invoice->id }}">
                            <i class="fa fa-upload mr-2"></i>@lang('app.upload')
                        </a>
                    @endif

                    @if ($invoice->status != 'canceled')
                        @if ($invoice->clientDetails)
                            @if (!is_null($invoice->clientDetails->shipping_address))
                                @if ($invoice->show_shipping_address == 'yes')
                                    <li>
                                        <a class="dropdown-item f-14 text-dark toggle-shipping-address"
                                            href="javascript:;" data-invoice-id="{{ $invoice->id }}">
                                            <i class="fa fa-eye-slash f-w-500 mr-2 f-11"></i> @lang('app.hideShippingAddress')
                                        </a>
                                    </li>
                                @else
                                    <li>
                                        <a class="dropdown-item f-14 text-dark toggle-shipping-address"
                                            href="javascript:;" data-invoice-id="{{ $invoice->id }}">
                                            <i class="fa fa-eye f-w-500 mr-2 f-11"></i> @lang('app.showShippingAddress')
                                        </a>
                                    </li>
                                @endif
                            @else
                                <li>
                                    <a class="dropdown-item f-14 text-dark add-shipping-address" href="javascript:;"
                                        data-invoice-id="{{ $invoice->id }}">
                                        <i class="fa fa-plus f-w-500 mr-2 f-11"></i> @lang('app.addShippingAddress')
                                    </a>
                                </li>
                            @endif
                        @else
                            @if ($invoice->project->clientDetails)
                                @if (!is_null($invoice->project->clientDetails->shipping_address))
                                    @if ($invoice->show_shipping_address == 'yes')
                                        <li>
                                            <a class="dropdown-item f-14 text-dark toggle-shipping-address"
                                                href="javascript:;" data-invoice-id="{{ $invoice->id }}">
                                                <i class="fa fa-eye-slash f-w-500 mr-2 f-11"></i> @lang('app.hideShippingAddress')
                                            </a>
                                        </li>
                                    @else
                                        <li>
                                            <a class="dropdown-item f-14 text-dark toggle-shipping-address"
                                                href="javascript:;" data-invoice-id="{{ $invoice->id }}">
                                                <i class="fa fa-eye f-w-500 mr-2 f-11"></i> @lang('app.showShippingAddress')
                                            </a>
                                        </li>
                                    @endif
                                @else
                                    <li>
                                        <a class="dropdown-item f-14 text-dark add-shipping-address"
                                            href="javascript:;" data-invoice-id="{{ $invoice->id }}">
                                            <i class="fa plus f-w-500 mr-2 f-11"></i> @lang('app.addShippingAddress')
                                        </a>
                                    </li>
                                @endif
                            @endif
                        @endif
                    @endif

                    @if (
                        $invoice->status != 'paid' &&
                            $invoice->status != 'draft' &&
                            $invoice->status != 'canceled' &&
                            !in_array('client', user_roles()) &&
                            $invoice->send_status == 1)
                        <li>
                            <a class="dropdown-item f-14 text-dark reminderButton" href="javascript:;"
                                data-invoice-id="{{ $invoice->id }}">
                                <i class="fa fa-bell f-w-500 mr-2 f-11"></i> @lang('app.paymentReminder')
                            </a>
                        </li>
                    @endif

                    @if (
                        !in_array('client', user_roles()) &&
                            in_array('payments', $user->modules) &&
                            $invoice->credit_note == 0 &&
                            $invoice->status != 'draft' &&
                            $invoice->status != 'paid' &&
                            $invoice->status != 'canceled' &&
                            $invoice->status != 'pending-confirmation' &&
                            $invoice->send_status)
                        @if ($addPaymentPermission == 'all' || ($addPaymentPermission == 'added' && $invoice->added_by == user()->id))
                            <li>
                                <a class="dropdown-item f-14 text-dark openRightModal"
                                    data-redirect-url="{{ route('invoices.show', $invoice->id) }}"
                                    href="{{ route('payments.create') . '?invoice_id=' . $invoice->id . '&default_client=' . $invoice->client_id }}"
                                    data-invoice-id="{{ $invoice->id }}">
                                    <i class="fa fa-plus f-w-500 mr-2 f-11"></i> @lang('modules.payments.addPayment')
                                </a>
                            </li>
                        @endif
                    @endif

                    @if (
                        $invoice->credit_note == 0 &&
                            $invoice->status != 'draft' &&
                            $invoice->status != 'canceled' &&
                            $invoice->status != 'unpaid' &&
                            !in_array('client', user_roles()))
                        @if ($invoice->amountPaid() > 0)
                            @if ($invoice->status == 'paid')
                                <a class="dropdown-item"
                                    href="{{ route('creditnotes.create') . '?invoice=' . $invoice->id }}"><i
                                        class="fa fa-plus mr-2"></i>@lang('modules.credit-notes.addCreditNote')</a>
                            @else
                                <a class="dropdown-item unpaidAndPartialPaidCreditNote" data-toggle="tooltip"
                                    data-invoice-id="{{ $invoice->id }}" href="javascript:;"><i
                                        class="fa fa-plus mr-2"></i>@lang('modules.credit-notes.addCreditNote')</a>
                            @endif
                        @endif
                    @endif

                    @if (!in_array($invoice->status, ['canceled', 'draft']) && !$invoice->credit_note && $invoice->send_status)
                        <li>
                            <a class="dropdown-item f-14 text-dark btn-copy" href="javascript:;"
                                data-clipboard-text="{{ url()->temporarySignedRoute('front.invoice', now()->addDays(\App\Models\GlobalSetting::SIGNED_ROUTE_EXPIRY), $invoice->hash) }}">
                                <i class="fa fa-copy f-w-500  mr-2 f-12"></i>
                                @lang('modules.invoices.copyPaymentLink')
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item f-14 text-dark"
                                href="{{ url()->temporarySignedRoute('front.invoice', now()->addDays(\App\Models\GlobalSetting::SIGNED_ROUTE_EXPIRY), $invoice->hash) }}"
                                target="_blank">
                                <i class="fa fa-external-link-alt f-w-500  mr-2 f-12"></i>
                                @lang('modules.payments.paymentLink')
                            </a>
                        </li>
                    @endif

                    @if ($addInvoicesPermission == 'all' || $addInvoicesPermission == 'added')
                        <a href="{{ route('invoices.create') . '?invoice=' . $invoice->id }}"
                            class="dropdown-item"><i class="fa fa-copy mr-2"></i> @lang('app.createDuplicate')
                        </a>
                    @endif

                    @if (
                        $firstInvoice->id != $invoice->id &&
                            ($invoice->status == 'unpaid' || $invoice->status == 'draft') &&
                            !in_array('client', user_roles()))
                        <li>
                            <a class="dropdown-item f-14 text-dark cancel-invoice"
                                data-invoice-id="{{ $invoice->id }}" href="javascript:;">
                                <i class="fa fa-times f-w-500  mr-2 f-12"></i>
                                @lang('app.cancel')
                            </a>
                        </li>
                    @endif

                    @if ($invoice->appliedCredits() > 0)
                        <li>
                            <a class="dropdown-item f-14 text-dark openRightModal"
                                href="{{ route('invoices.applied_credits', $invoice->id) }}">
                                <i class="fa fa-money-bill-alt f-w-500  mr-2 f-12"></i>
                                @lang('app.viewInvoicePayments')
                            </a>
                        </li>
                    @endif
                </ul>
            </div>

            {{-- PAYMENT GATEWAY --}}
            @if (in_array('client', user_roles()) &&
                    $invoice->total > 0 &&
                    in_array($invoice->status, ['unpaid', 'partial']) &&
                    ($credentials->show_pay || $methods->count() > 0) &&
                    !(
                        !empty($invoice->payment) &&
                        isset($invoice->payment[0]->gateway) &&
                        $invoice->payment[0]->gateway == 'Offline'
                    ))

                <div class="inv-action payNowButton mr-3 mr-lg-3 mr-md-3 dropup">
                    <button class="dropdown-toggle btn-primary rounded mr-3 mr-lg-0 mr-md-0 f-15" type="button"
                        id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">@lang('modules.invoices.payNow')
                        <span><i class="fa fa-chevron-down f-15"></i></span>
                    </button>
                    <!-- DROPDOWN - INFORMATION -->
                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton"
                        tabindex="0">
                        @if ($credentials->stripe_status == 'active')
                            <li>
                                <a class="dropdown-item f-14 text-dark" href="javascript:;"
                                    data-invoice-id="{{ $invoice->id }}" id="stripeModal">
                                    <i class="fab fa-stripe-s f-w-500 mr-2 f-11"></i>
                                    @lang('modules.invoices.payStripe')
                                </a>
                            </li>
                        @endif
                        @if ($credentials->paystack_status == 'active')
                            <li>
                                <a class="dropdown-item f-14 text-dark" href="javascript:void(0);"
                                    data-invoice-id="{{ $invoice->id }}" id="paystackModal">
                                    <img style="height: 15px;"
                                        src="https://s3-eu-west-1.amazonaws.com/pstk-integration-logos/paystack.jpg">
                                    @lang('modules.invoices.payPaystack')</a>
                            </li>
                        @endif
                        @if ($credentials->flutterwave_status == 'active')
                            <li>
                                <a class="dropdown-item f-14 text-dark" href="javascript:void(0);"
                                    data-invoice-id="{{ $invoice->id }}" id="flutterwaveModal">
                                    <img style="height: 15px;" src="{{ asset('img/flutterwave.png') }}">
                                    @lang('modules.invoices.payFlutterwave')</a>
                            </li>
                        @endif
                        @if ($credentials->payfast_status == 'active')
                            <li>
                                <a class="dropdown-item f-14 text-dark" href="javascript:void(0);" id="payfastModal">
                                    <img style="height: 15px;" src="{{ asset('img/payfast.png') }}">
                                    @lang('modules.invoices.payPayfast')</a>
                            </li>
                        @endif

                        @if ($credentials->square_status == 'active')
                            <li>
                                <a class="dropdown-item f-14 text-dark" href="javascript:void(0);" id="squareModal">
                                    <img style="height: 15px;" src="{{ asset('img/square.svg') }}">
                                    @lang('modules.invoices.paySquare')</a>
                            </li>
                        @endif

                        @if ($credentials->authorize_status == 'active')
                            <li>
                                <a class="dropdown-item f-14 text-dark" href="javascript:void(0);"
                                    data-invoice-id="{{ $invoice->id }}" id="authorizeModal">
                                    <img style="height: 15px;" src="{{ asset('img/authorize.png') }}">
                                    @lang('modules.invoices.payAuthorize')</a>
                            </li>
                        @endif

                        @if ($credentials->mollie_status == 'active')
                            <li>
                                <a class="dropdown-item f-14 text-dark" href="javascript:void(0);"
                                    data-invoice-id="{{ $invoice->id }}" id="mollieModal">
                                    <img style="height: 20px;" src="{{ asset('img/mollie.png') }}">
                                    @lang('modules.invoices.payMollie')</a>
                            </li>
                        @endif
                        @if ($credentials->razorpay_status == 'active')
                            <li>
                                <a class="dropdown-item f-14 text-dark" href="javascript:;"
                                    id="razorpayPaymentButton">
                                    <i class="fa fa-credit-card f-w-500 mr-2 f-11"></i>
                                    @lang('modules.invoices.payRazorpay')
                                </a>
                            </li>
                        @endif
                        @if ($credentials->paypal_status == 'active')
                            <li>
                                <a class="dropdown-item f-14 text-dark"
                                    href="{{ route('paypal', [$invoice->id]) }}">
                                    <i class="fab fa-paypal f-w-500 mr-2 f-11"></i> @lang('modules.invoices.payPaypal')
                                </a>
                            </li>
                        @endif

                        @if ($methods->count() > 0)
                            <li>
                                <a class="dropdown-item f-14 text-dark" href="javascript:;" id="offlinePaymentModal"
                                    data-invoice-id="{{ $invoice->id }}">
                                    <i class="fa fa-money-bill f-w-500 mr-2 f-11"></i>
                                    @lang('modules.invoices.payOffline')
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
            {{-- PAYMENT GATEWAY --}}

            <x-forms.button-cancel :link="route('invoices.index')" class="border-0 mr-3">@lang('app.cancel')
            </x-forms.button-cancel>

        </div>


    </div>
    <!-- CARD FOOTER END -->

</div>
<!-- INVOICE CARD END -->

{{-- Custom fields data --}}
@if (isset($fields) && count($fields) > 0)
    <div class="row mt-4">
        <!-- TASK STATUS START -->
        <div class="col-md-12">
            <x-cards.data>
                <h5 class="mb-3"> @lang('modules.projects.otherInfo')</h5>
                <x-forms.custom-field-show :fields="$fields" :model="$invoice"></x-forms.custom-field-show>
            </x-cards.data>
        </div>
    </div>
@endif

@if (count($invoice->files) > 0)
    <div class="bg-white mt-4 pl-3 pt-3">
        <h5>{{ __('modules.invoiceFiles') }}</h5>
        <div class="d-flex flex-wrap" id="invoice-file-list">
            @forelse($invoice->files as $file)
                <x-file-card :fileName="$file->filename" :dateAdded="$file->created_at->diffForHumans()">
                    <x-file-view-thumbnail :file="$file"></x-file-view-thumbnail>

                    @if ($viewPermission == 'all' || ($viewPermission == 'added' && $file->added_by == user()->id))
                        <x-slot name="action">
                            <div class="dropdown ml-auto file-action">
                                <button class="btn btn-lg f-14 p-0 text-lightest  rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                    aria-labelledby="dropdownMenuLink" tabindex="0">
                                    @if ($viewPermission == 'all' || ($viewPermission == 'added' && $file->added_by == user()->id))
                                        @if ($file->icon == 'images')
                                            <a class="img-lightbox cursor-pointer d-block text-dark-grey f-13 pt-3 px-3"
                                                data-image-url="{{ $file->file_url }}"
                                                href="javascript:;">@lang('app.view')</a>
                                        @else
                                            <a class="cursor-pointer d-block text-dark-grey f-13 pt-3 px-3 "
                                                target="_blank" href="{{ $file->file_url }}">@lang('app.view')</a>
                                        @endif
                                        <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 "
                                            href="{{ route('invoice-files.download', md5($file->id)) }}">@lang('app.download')</a>
                                    @endif

                                    @if ($deletePermission == 'all' || ($deletePermission == 'added' && $file->added_by == user()->id))
                                        <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-file"
                                            data-row-id="{{ $file->id }}"
                                            href="javascript:;">@lang('app.delete')</a>
                                    @endif
                                </div>
                            </div>
                        </x-slot>
                    @endif

                </x-file-card>
            @empty
                <x-cards.no-record :message="__('messages.noFileUploaded')" icon="file" />
            @endforelse

        </div>
    </div>
@endif

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>

<script>
    var clipboard = new ClipboardJS('.btn-copy');

    clipboard.on('success', function(e) {
        Swal.fire({
            icon: 'success',
            text: '@lang('app.copied')',
            toast: true,
            position: 'top-end',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false,
            customClass: {
                confirmButton: 'btn btn-primary',
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
        })
    });

    $('body').on('click', '#stripeModal', function() {
        let invoiceId = $(this).data('invoice-id');
        let queryString = "?invoice_id=" + invoiceId;
        let url = "{{ route('invoices.stripe_modal') }}" + queryString;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '#paystackModal', function() {
        let id = $(this).data('invoice-id');
        let queryString = "?id=" + id + "&type=invoice";
        let url = "{{ route('front.paystack_modal') }}" + queryString;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })

    $('body').on('click', '#flutterwaveModal', function() {
        let id = $(this).data('invoice-id');
        let queryString = "?id=" + id + "&type=invoice";
        let url = "{{ route('front.flutterwave_modal') }}" + queryString;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })

    $('body').on('click', '#authorizeModal', function() {
        let id = $(this).data('invoice-id');
        let queryString = "?id=" + id + "&type=invoice";
        let url = "{{ route('front.authorize_modal') }}" + queryString;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })

    $('body').on('click', '#mollieModal', function() {
        let id = $(this).data('invoice-id');
        let queryString = "?id=" + id + "&type=invoice";
        let url = "{{ route('front.mollie_modal') }}" + queryString;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })

    $('body').on('click', '#payfastModal', function() {
        // Block model UI until payment happens
        $.easyBlockUI();

        $.easyAjax({
            url: "{{ route('payfast_public') }}",
            type: "POST",
            blockUI: true,
            data: {
                id: '{{ $invoice->id }}',
                type: 'invoice',
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status == 'success') {
                    $('body').append(response.form);
                    $('#payfast-pay-form').submit();
                }
            }
        });
    });

    $('body').on('click', '#squareModal', function() {
        // Block model UI until payment happens
        $.easyBlockUI();

        $.easyAjax({
            url: "{{ route('square_public') }}",
            type: "POST",
            blockUI: true,
            data: {
                id: '{{ $invoice->id }}',
                type: 'invoice',
                _token: '{{ csrf_token() }}'
            }
        });
    });

    $('body').on('click', '#offlinePaymentModal', function() {
        let invoiceId = $(this).data('invoice-id');
        let queryString = "?invoice_id=" + invoiceId;
        let url = "{{ route('invoices.offline_payment_modal') }}" + queryString;

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    @if ($credentials->razorpay_status == 'active')
        $('body').on('click', '#razorpayPaymentButton', function() {
            var amount = {{ number_format((float) $invoice->amountDue(), 2, '.', '') * 100 }};
            var invoiceId = {{ $invoice->id }};
            var clientEmail = "{{ $user->email }}";

            var options = {
                "key": "{{ $credentials->razorpay_mode == 'test' ? $credentials->test_razorpay_key : $credentials->live_razorpay_key }}",
                "amount": amount,
                "currency": '{{ $invoice->currency->currency_code }}',
                "name": "{{ $companyName }}",
                "description": "Invoice Payment",
                "image": "{{ company()->logo_url }}",
                "handler": function(response) {
                    confirmRazorpayPayment(response.razorpay_payment_id, invoiceId);
                },
                "payment": {
                    "capture": "automatic",
                    "capture_options": {
                        "automaticexpiryperiod": 12,
                        "manualexpiryperiod": 7200,
                        "refund_speed": "optimum"
                    },
                },
                "modal": {
                    "ondismiss": function() {
                        // On dismiss event
                    }
                },
                "prefill": {
                    "email": clientEmail
                },
                "notes": {
                    "purchase_id": invoiceId, //invoice ID
                    "type": "invoice"
                }
            };
            var rzp1 = new Razorpay(options);

            rzp1.open();
        })

        //Confirmation after transaction
        function confirmRazorpayPayment(id, invoiceId) {
            // Block UI immediatly after payment modal disappear
            $.easyBlockUI();

            $.easyAjax({
                type: 'POST',
                url: "{{ route('pay_with_razorpay', [$invoice->company->hash]) }}",
                data: {
                    paymentId: id,
                    invoiceId: invoiceId,
                    _token: '{{ csrf_token() }}'
                }
            });
        }
    @endif

    $('body').on('click', '.sendButton', function() {
        var id = $(this).data('invoice-id');
        var token = "{{ csrf_token() }}";
        var type = $(this).data('type');

        var url = "{{ route('invoices.send_invoice', ':id') }}";
        url = url.replace(':id', id);

        $.easyAjax({
            type: 'POST',
            url: url,
            container: '.content-wrapper',
            blockUI: true,
            data: {
                '_token': token,
                'data_type': type,
                'type': 'send'
            },
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        });
    });

    $('body').on('click', '.approveButton', function() {
        var id = $(this).data('invoice-id');
        var url = "{{ route('invoices.approve_offline_invoice', ':id') }}";
        url = url.replace(':id', id);

        var token = "{{ csrf_token() }}";
        $.easyAjax({
            type: 'POST',
            url: url,
            container: '#invoices-table',
            blockUI: true,
            data: {
                '_token': token
            },
            success: function(response) {
                if (response.status == "success") {
                    showTable();
                }
            }
        });
    });

    $('body').on('click', '.reminderButton', function() {
        var id = $(this).data('invoice-id');
        var token = "{{ csrf_token() }}";

        var url = "{{ route('invoices.payment_reminder', ':id') }}";
        url = url.replace(':id', id);

        $.easyAjax({
            type: 'GET',
            container: '#invoices-table',
            blockUI: true,
            url: url,
            success: function(response) {
                if (response.status == "success") {
                    $.unblockUI();
                }
            }
        });
    });

    $('body').on('click', '.cancel-invoice', function() {
        var id = $(this).data('invoice-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.invoiceText')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('app.yes')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var token = "{{ csrf_token() }}";

                var url = "{{ route('invoices.update_status', ':id') }}";
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'GET',
                    url: url,
                    container: '#invoices-table',
                    blockUI: true,
                    success: function(response) {
                        if (response.status == "success") {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });

    $('body').on('click', '.delete-invoice', function() {
        var id = $(this).data('invoice-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var token = "{{ csrf_token() }}";

                var url = "{{ route('invoices.destroy', ':id') }}";
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            window.location.href = "{{ route('invoices.index') }}";
                        }
                    }
                });
            }
        });
    });

    $('body').on('click', '.toggle-shipping-address', function() {
        let invoiceId = $(this).data('invoice-id');

        let url = "{{ route('invoices.toggle_shipping_address', ':id') }}";
        url = url.replace(':id', invoiceId);

        $.easyAjax({
            url: url,
            type: 'GET',
            container: '#invoices-table',
            blockUI: true,
            success: function(response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }
        });
    });

    $('body').on('click', '.add-shipping-address', function() {
        let invoiceId = $(this).data('invoice-id');

        var url = "{{ route('invoices.shipping_address_modal', [':id']) }}";
        url = url.replace(':id', invoiceId);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.invoice-upload', function() {
        var invoiceId = $(this).data('invoice-id');
        const url = "{{ route('invoices.file_upload') }}?invoice_id=" + invoiceId;
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.unpaidAndPartialPaidCreditNote', function() {
        var id = $(this).data('invoice-id');

        Swal.fire({
            title: "@lang('messages.confirmation.createCreditNotes')",
            text: "@lang('messages.creditText')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('app.yes')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('creditnotes.create') }}?invoice=:id";
                url = url.replace(':id', id);

                location.href = url;
            }
        });
    });

    $('body').on('click', '.delete-file', function() {
        let id = $(this).data('row-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('invoice-files.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            $('#invoice-file-list').html(response.view);
                        }
                    }
                });
            }
        });
    });
</script>
