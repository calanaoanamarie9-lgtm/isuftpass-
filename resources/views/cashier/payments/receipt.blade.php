<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Official Receipt {{ $request->or_number }} - ISUFSTPASS
    </title>

    @vite(['resources/css/app.css'])

    @include('partials.pwa-head')

    <style>

        /* Short bond paper: 8.5in x 11in — receipts print smaller than forms */
        @page {
            size: 8.5in 11in;
            margin: 12mm 14mm;
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
            min-height: 279.4mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 14mm 16mm;
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
            background: #166534;
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

        .office-name {
            font-size: 12.5px;
            font-weight: 700;
            margin-top: 6px;
        }

        .header-line {
            border-bottom: 2px solid #1f2f46;
            margin-top: 8px;
        }

        /* ---- Title ---- */
        .form-title {
            text-align: center;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: 2px;
            margin: 16px 0 2px;
            color: #166534;
        }

        .form-subtitle {
            text-align: center;
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 18px;
        }

        /* ---- Meta row ---- */
        .meta-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 8px 24px;
            font-size: 12.5px;
            margin-bottom: 16px;
        }

        .meta-row b {
            color: #14532d;
        }

        /* ---- Details table ---- */
        .details {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 8px;
        }

        .details td {
            padding: 7px 8px;
            border-bottom: 1px solid #d1d5db;
            vertical-align: top;
        }

        .details .label {
            width: 34%;
            color: #6b7280;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .amount-box {
            margin-top: 14px;
            border: 1.5px solid #166534;
            border-radius: 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .amount-box .label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #166534;
            font-weight: 700;
        }

        .amount-box .value {
            font-size: 20px;
            font-weight: 800;
            color: #14532d;
        }

        .note-box {
            border: 1px solid #c7d6ec;
            background: #f4f8fe;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 11.5px;
            line-height: 1.7;
            margin-top: 16px;
        }

        .note-box b {
            color: #102d5b;
        }

        /* ---- Signatures ---- */
        .signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 60px;
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

        <a href="{{ url()->previous() }}">Back to payments</a>

        <button type="button" onclick="window.print()">
            Print Official Receipt
        </button>

    </div>

    <div class="sheet-viewport">

        <div class="document-page">

            {{-- HEADER --}}
            <div class="document-header">

                <div class="republic">
                    REPUBLIC OF THE PHILIPPINES
                </div>

                <div class="university-name">
                    ILOILO STATE UNIVERSITY OF FISHERIES SCIENCE AND TECHNOLOGY
                </div>

                <div class="location">
                    San Matias, Dingle, Iloilo
                </div>

                <div class="office-name">
                    UNIVERSITY CASHIER'S OFFICE
                </div>

                <div class="header-line"></div>

            </div>

            {{-- TITLE --}}
            <h1 class="form-title">
                OFFICIAL RECEIPT
            </h1>

            <p class="form-subtitle">
                ISUFSTPASS — Release of Credentials
            </p>

            {{-- META --}}
            <div class="meta-row">

                <span>
                    OR No.: <b>{{ $request->or_number }}</b>
                </span>

                <span>
                    Date: <b>{{ $request->paid_at?->format('F j, Y') }}</b>
                </span>

            </div>

            {{-- DETAILS --}}
            <table class="details">

                <tr>
                    <td class="label">Payor</td>
                    <td>{{ $request->student_name }}</td>
                </tr>

                <tr>
                    <td class="label">Request No.</td>
                    <td>{{ $request->request_number }}</td>
                </tr>

                <tr>
                    <td class="label">Particulars</td>
                    <td>{{ $request->documentsSummary() }}</td>
                </tr>

                <tr>
                    <td class="label">Mode of Payment</td>
                    <td>Over the Counter</td>
                </tr>

                <tr>
                    <td class="label">Date &amp; Time Received</td>
                    <td>{{ $request->paid_at?->format('F j, Y h:i A') }}</td>
                </tr>

            </table>

            {{-- AMOUNT --}}
            <div class="amount-box">

                <span class="label">
                    Amount Received
                </span>

                <span class="value">
                    ₱{{ number_format($request->totalFee(), 2) }}
                </span>

            </div>

            {{-- NOTE --}}
            <div class="note-box">

                <b>Present this Official Receipt</b> to the Registrar's
                Office — or submit its details through the ISUFSTPASS
                portal — for validation. Your credential is prepared for
                release only after the receipt has been validated.

            </div>

            {{-- SIGNATURES --}}
            <div class="signatures">

                <div class="signature-line">

                    <div class="signature-role">
                        Received by (Student / Representative)
                    </div>

                    &nbsp;

                </div>

                <div class="signature-line">

                    <div class="signature-role">
                        Cashier
                    </div>

                    <b>{{ $cashier->name }}</b>

                </div>

            </div>

        </div>

    </div>

</body>
</html>
