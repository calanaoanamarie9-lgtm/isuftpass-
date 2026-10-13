<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Order of Payment - {{ $request->op_number }} - ISUFSTPASS
    </title>

    @vite(['resources/css/app.css'])

    @include('partials.pwa-head')

    <style>

        /* Long bond paper: 8.5in x 13in */
        @page {
            size: 8.5in 13in;
            margin: 10mm 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e5e7eb;
            font-family: Arial, Helvetica, sans-serif;
            color: #1f2937;
        }

        .document-page {
            width: 215.9mm;
            min-height: 330.2mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 10mm 12mm;
            transform-origin: top left;
        }

        .sheet-viewport {
            width: 100%;
            overflow: hidden;
        }

        /* Responsive on screen; fixed paper size in print */
        @media screen {
            .document-page {
                width: 100%;
                max-width: 215.9mm;
                min-height: auto;
                padding: 16px;
                margin: 16px auto;
            }
            .sheet-viewport {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* ---- Print bar (screen only) ---- */
        .print-bar {
            max-width: 215.9mm;
            margin: 20px auto 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .print-bar a {
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .print-bar button {
            background: #123b78;
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: 10px 22px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        /* ---- Header ---- */
        .document-header {
            width: 100%;
            padding: 6px 0 5px;
            color: #1f2f46;
            font-family: Arial, sans-serif;
        }

        .header-main {
            display: grid;
            grid-template-columns: 100px 1fr 100px;
            align-items: center;
            width: 100%;
        }

        .header-logo img {
            max-width: 82px;
            max-height: 82px;
        }

        .left-logo {
            display: flex;
            justify-content: flex-start;
        }

        .right-logo {
            display: flex;
            justify-content: flex-end;
        }

        .right-logo img {
            max-width: 69px;
            max-height: 69px;
        }

        .header-content {
            text-align: center;
        }

        .republic {
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .university-name {
            font-size: 15px;
            font-weight: 800;
            margin: 2px 0;
        }

        .location {
            font-size: 12px;
        }

        .office-section {
            text-align: center;
            margin-top: 6px;
        }

        .office-name {
            font-size: 12.5px;
            font-weight: 700;
        }

        .contact-info {
            font-size: 10.5px;
            margin-top: 2px;
        }

        .header-line {
            border-bottom: 2px solid #1f2f46;
            margin-top: 6px;
        }

        /* ---- Title ---- */
        .form-title {
            text-align: center;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 2px;
            margin: 16px 0 4px;
            color: #102d5b;
        }

        .form-subtitle {
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 16px;
        }

        /* ---- Meta row ---- */
        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 32px;
            font-size: 12px;
            margin-bottom: 14px;
        }

        .meta-row b {
            color: #102d5b;
        }

        /* ---- Info grid ---- */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 32px;
            font-size: 12px;
            margin-bottom: 16px;
        }

        .info-grid .label {
            color: #6b7280;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ---- Fee table ---- */
        .fee-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .fee-table th {
            text-align: left;
            border-bottom: 1.5px solid #1f2f46;
            padding: 6px 8px;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .fee-table td {
            border-bottom: 1px solid #d1d5db;
            padding: 7px 8px;
        }

        .fee-table .amount {
            text-align: right;
            white-space: nowrap;
        }

        .fee-table tfoot td {
            border-bottom: 0;
            border-top: 1.5px solid #1f2f46;
            font-weight: 800;
            font-size: 13px;
        }

        /* ---- Note box ---- */
        .note-box {
            border: 1px solid #c7d6ec;
            background: #f4f8fe;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 11.5px;
            line-height: 1.7;
            margin-top: 14px;
        }

        .note-box b {
            color: #102d5b;
        }

        /* ---- Signatures ---- */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 56px;
            font-size: 12px;
        }

        .signature-line {
            border-top: 1px solid #374151;
            padding-top: 5px;
            text-align: center;
        }

        .signature-role {
            font-size: 10.5px;
            color: #6b7280;
        }

        @media print {
            body {
                background: #fff;
            }

            .print-bar {
                display: none;
            }

            .document-page {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="print-bar">

        <a href="{{ url()->previous() }}">Back to request</a>

        <button type="button" onclick="window.print()">
            Print Order of Payment
        </button>

    </div>

    <div class="sheet-viewport">

        <div class="document-page">

            {{-- HEADER --}}
            <div class="document-header">

                <div class="header-main">

                    <div class="header-logo left-logo">
                        <img
                            src="{{ asset('images/requisition-header-left-logo.png') }}"
                            alt="ISUFST Logo"
                        >
                    </div>

                    <div class="header-content">

                        <div class="republic">
                            REPUBLIC OF THE PHILIPPINES
                        </div>

                        <div class="university-name">
                            ILOILO STATE UNIVERSITY OF FISHERIES SCIENCE AND TECHNOLOGY
                        </div>

                        <div class="location">
                            San Matias, Dingle, Iloilo
                        </div>

                    </div>

                    <div class="header-logo right-logo">
                        <img
                            src="{{ asset('images/requisition-header-right-logo.png') }}"
                            alt="Bagong Pilipinas"
                        >
                    </div>

                </div>

                <div class="office-section">

                    <div class="office-name">
                        OFFICE OF THE REGISTRAR
                    </div>

                    <div class="contact-info">
                        Website: isufst.edu.ph &nbsp;|&nbsp; Contact No. +639634638274
                        &nbsp;|&nbsp; Email: cict_dingle@isufst.edu.ph
                    </div>

                </div>

                <div class="header-line"></div>

            </div>

            {{-- TITLE --}}
            <h1 class="form-title">
                ORDER OF PAYMENT
            </h1>

            <p class="form-subtitle">
                Official Billing Statement — Release of Credentials
            </p>

            {{-- META --}}
            <div class="meta-row">

                <span>
                    OP No.: <b>{{ $request->op_number }}</b>
                </span>

                <span>
                    Request No.: <b>{{ $request->request_number }}</b>
                </span>

                <span>
                    Date Issued: <b>{{ $request->for_signature_at?->format('F j, Y') ?? $request->created_at->format('F j, Y') }}</b>
                </span>

            </div>

            {{-- STUDENT --}}
            <div class="info-grid">

                <div>
                    <div class="label">Name of Student / Representative</div>
                    <div>{{ $request->student_name }}</div>
                </div>

                <div>
                    <div class="label">Contact No.</div>
                    <div>{{ $request->student_contact ?: '—' }}</div>
                </div>

                <div>
                    <div class="label">Address</div>
                    <div>{{ $request->student_address ?: '—' }}</div>
                </div>

                <div>
                    <div class="label">Course / Year Level</div>
                    <div>{{ $request->student_course_year ?: '—' }}</div>
                </div>

            </div>

            {{-- FEE TABLE --}}
            <table class="fee-table">

                <thead>
                    <tr>
                        <th>Particulars</th>
                        <th class="amount">Amount</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($request->documents as $document)
                        <tr>
                            <td>{{ $document->name }}</td>
                            <td class="amount">₱{{ number_format((float) $document->fee, 2) }}</td>
                        </tr>
                    @endforeach

                    @if ($request->others_specification)
                        <tr>
                            <td>{{ $request->others_specification }}</td>
                            <td class="amount">—</td>
                        </tr>
                    @endif

                </tbody>

                <tfoot>
                    <tr>
                        <td>TOTAL AMOUNT DUE</td>
                        <td class="amount">₱{{ number_format($request->totalFee(), 2) }}</td>
                    </tr>
                </tfoot>

            </table>

            {{-- INSTRUCTIONS --}}
            <div class="note-box">

                <b>Instructions:</b>

                Settle the total amount due at the
                <b>University Cashier's Office</b> (over the counter) and
                claim your <b>Official Receipt</b>. Present that Official
                Receipt back to the <b>Registrar's Office</b> — or submit
                its details through the ISUFSTPASS portal — for validation
                before your credential is prepared for release.

            </div>

            {{-- SIGNATURES --}}
            <div class="signatures">

                <div class="signature-line">

                    <div class="signature-role">
                        Prepared by
                    </div>

                    <b>Registrar</b>

                </div>

                <div class="signature-line">

                    <div class="signature-role">
                        Received by (Student / Representative)
                    </div>

                    &nbsp;

                </div>

            </div>

        </div>

    </div>

</body>
</html>
