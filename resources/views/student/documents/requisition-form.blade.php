<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Requisition Form - {{ $request->request_number }} - ISUFSTPASS
    </title>

    @vite(['resources/css/app.css'])

    @include('partials.pwa-head')

    <style>

        /* =========================================
           PRINT PAGE - long bond paper: 8.5in x 13in
        ========================================= */

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


        /* =========================================
           DOCUMENT PAGE
        ========================================= */

        .document-page {
            width: 215.9mm;
            min-height: 330.2mm;
            margin: 20px auto;
            background: #ffffff;
            padding: 10mm 12mm;
        }


        /* =========================================
           TEXT HEADER
        ========================================= */

        /* =========================================
           DOCUMENT HEADER
        ========================================= */

        .document-header {
            width: 100%;
            padding: 6px 0 5px;
            box-sizing: border-box;
            color: #1f2f46;
            font-family: Arial, sans-serif;
        }


        /* MAIN HEADER ROW
           Logo columns are 100px - the marks are only ~82px and ~69px wide -
           so the university name gets the widest column it can. */

        .header-main {
            display: grid;
            grid-template-columns: 100px 1fr 100px;
            align-items: center;
            width: 100%;
        }


        /* LOGOS */

        .header-logo {
            display: flex;
            align-items: center;
        }

        .left-logo {
            justify-content: flex-start;
        }

        .right-logo {
            justify-content: flex-end;
        }

        .header-logo img {
            width: auto;
            height: 64px;
            max-width: 100px;
            object-fit: contain;
        }


        /* CENTER HEADER */

        .header-content {
            text-align: center;
            line-height: 1.1;
        }

        .republic {
            font-family: "Times New Roman", serif;
            font-size: 11px;
            font-weight: 400;
        }

        /* Sized so the full university name stays on one line inside the
           centre column (525px on a 215.9mm sheet; needs ~501px at 14px). */

        .university-name {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
            line-height: 1.15;
            margin-top: 3px;
        }

        .location {
            font-size: 12px;
            margin-top: 2px;
        }


        /* OFFICE INFORMATION */

        .office-section {
            text-align: center;
            margin-top: 4px;
        }

        .office-name {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .contact-info {
            font-size: 10px;
            line-height: 1.2;
            margin-top: 3px;
        }

        .contact-info .separator {
            margin: 0 8px;
        }


        /* BOTTOM LINE */

        .header-line {
            width: 100%;
            height: 2px;
            background: #244a73;
            margin-top: 5px;
        }


        /* =========================================
           FORM TITLE
        ========================================= */

        .form-title {
            margin: 0 0 6mm;
            text-align: center;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: 1px;
            color: #1f2937;
        }


        /* =========================================
           GENERAL ROW
        ========================================= */

        .row {
            display: flex;
            align-items: center;
            width: 100%;
            margin-bottom: 6px;
        }

        .label {
            font-size: 14px;
            white-space: nowrap;
        }

        .transaction-spacer {
            flex: 1;
        }

        .date-line {
            width: 190px;
            margin-left: 12px;
            padding: 2px 5px;
            text-align: center;
            font-size: 14px;
            border-bottom: 1px solid #374151;
        }


        /* =========================================
           SECTION SPACING
        ========================================= */

        .section-space {
            margin-top: 10px;
        }


        /* =========================================
           DOCUMENT REQUEST LIST
        ========================================= */

        .request-section {
            margin-top: 6px;
            margin-left: 55px;
        }

        .request-item {
            display: flex;
            align-items: center;
            min-height: 20px;
            margin: 2px 0;
            font-size: 14px;
        }

        .check-line {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 105px;
            min-height: 18px;
            margin-right: 10px;
            border-bottom: 1px solid #374151;
            font-size: 13px;
        }

        .medium-line {
            flex: 1;
            min-height: 20px;
            margin-left: 8px;
            padding: 0 4px;
            text-align: center;
            border-bottom: 1px solid #374151;
            font-size: 13px;
        }


        /* =========================================
           PURPOSE
        ========================================= */

        .purpose-row {
            display: flex;
            align-items: center;
            min-height: 21px;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .small-line {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 85px;
            min-height: 19px;
            margin: 0 10px;
            text-align: center;
            border-bottom: 1px solid #374151;
            font-size: 13px;
        }


        /* =========================================
           GRADUATION
        ========================================= */

        .graduation-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 5px;
            font-size: 14px;
        }


        /* =========================================
           STUDENT NAME
        ========================================= */

        .student-name-row {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            margin-top: 14px;
        }

        .student-name-label {
            padding-bottom: 14px;
            font-size: 14px;
            white-space: nowrap;
        }

        .name-field {
            flex: 1;
            text-align: center;
        }

        .name-line {
            min-height: 19px;
            border-bottom: 1px solid #374151;
            font-size: 13px;
        }

        .name-label {
            margin-top: 3px;
            font-size: 11px;
        }


        /* =========================================
           SIGNATURE
        ========================================= */

        .student-signature {
            width: 280px;
            margin-left: auto;
            margin-top: 14px;
        }

        .signature-line {
            height: 21px;
            border-bottom: 1px solid #374151;
            max-width: 200px;
            margin: 0 auto;
        }

        .signature-caption {
            margin-top: 4px;
            text-align: center;
            font-size: 12px;
        }


        /* =========================================
           MODE OF CLAIMING
        ========================================= */

        .claim-section {
            margin-top: 16px;
        }

        .claim-option {
            margin: 5px 0;
            font-size: 14px;
        }

        .checkbox-line {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            min-height: 18px;
            margin-right: 8px;
            border-bottom: 1px solid #374151;
            font-size: 12px;
        }

        .representative-line {
            display: inline-block;
            min-width: 160px;
            margin-left: 6px;
            text-align: center;
            border-bottom: 1px solid #374151;
            font-size: 13px;
        }


        /* =========================================
           ACTION TAKEN
        ========================================= */

        .action-section {
            margin-top: 14px;
            padding-top: 4px;
            border-top: 1px solid #374151;
        }

        .action-title {
            margin-bottom: 5px;
            font-size: 14px;
        }

        .action-row {
            display: flex;
            align-items: center;
            min-height: 21px;
            margin: 2px 0;
            font-size: 14px;
        }

        .action-name {
            width: 210px;
        }

        .action-line {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 95px;
            min-height: 18px;
            margin: 0 8px;
            text-align: center;
            border-bottom: 1px solid #374151;
            font-size: 13px;
        }


        /* =========================================
           CERTIFICATION
        ========================================= */

        .certification {
            margin-top: 8px;
            padding-top: 8px;
            text-align: center;
            border-top: 1px dashed #374151;
            font-size: 13px;
            line-height: 1.5;
        }


        /* =========================================
           SIGNATURES
        ========================================= */

        .signature-section {
            display: flex;
            justify-content: space-between;
            gap: 45px;
            margin-top: 16px;
            border-bottom: 1px solid #374151;
            padding-bottom: 6px;
        }

        .signature-box {
            flex: 1;
            text-align: center;
        }

        .signature-box p {
            margin: 5px 0;
            font-size: 13px;
        }

        .signature-name {
            font-weight: bold;
        }


        /* =========================================
           APPROVAL
        ========================================= */

        .approval-section {
            margin-top: 16px;
            text-align: center;
        }

        .approval-section p {
            margin: 4px 0;
            font-size: 14px;
        }

        .approval-name {
            margin-top: 14px !important;
            font-weight: bold;
        }


        /* =========================================
           FOOTER STRIP
        ========================================= */

        .doc-footer {
            margin-top: 16px;
            text-align: center;
        }

        .doc-footer-strip {
            display: block;
            width: 100%;
            max-width: 100%;
            height: 64px;
            object-fit: contain;
            margin: 6px auto 0;
        }


        /* =========================================
           PRINT
        ========================================= */

        @media print {

            @page {
                size: 8.5in 13in;
                margin: 10mm 12mm;
            }

            body {
                background: #ffffff;
            }

            /*
            | Long bond printable area = 191.9mm x 310.2mm (the 8.5in x 13in
            | sheet less the 10mm 12mm page margins). The form's natural height
            | is ~341mm, so it is scaled as one block until it lands on exactly
            | one sheet. 0.87 prints at ~297mm, leaving ~13mm of slack for rows
            | that wrap (long "Others" specs, transfer school, double names).
            | Raise it only if the natural height also comes down - above
            | ~0.91 the form spills onto a second page.
            */

            .document-page {
                width: auto;
                min-height: auto;
                margin: 0 auto;
                padding: 0;
                zoom: 0.87;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }

        }

    </style>
</head>


<body>

    @php

        /*
        |--------------------------------------------------------------------------
        | REQUESTED DOCUMENTS
        |--------------------------------------------------------------------------
        */

        $requestedNames = $request->documents
            ->pluck('name')
            ->map(
                fn ($name) => mb_strtolower(
                    (string) preg_replace('/[^A-Za-z0-9]/', '', $name)
                )
            )
            ->filter();


        $requested = function (string $line) use ($requestedNames) {

            $key = mb_strtolower(
                (string) preg_replace('/[^A-Za-z0-9]/', '', $line)
            );

            return $requestedNames->contains(
                fn ($name) =>
                    $name === $key ||
                    str_contains($name, $key) ||
                    str_contains($key, $name)
            );

        };


        /*
        |--------------------------------------------------------------------------
        | ENUMS
        |--------------------------------------------------------------------------
        */

        $purpose = \App\Enums\RequestPurposeType::tryFrom(
            $request->purpose_type
        );

        $educationalStatus = \App\Enums\EducationalStatus::tryFrom(
            $request->educational_status
        );

        $educationalLevel = \App\Enums\EducationalLevel::tryFrom(
            $request->educational_level
        );

        $claimMode = \App\Enums\ClaimMode::tryFrom(
            $request->claim_mode
        );


        /*
        |--------------------------------------------------------------------------
        | STUDENT NAME
        |--------------------------------------------------------------------------
        */

        $nameParts = preg_split(
            '/\s*,\s*|\s+/',
            trim($request->student_name ?? ''),
            3
        );

        $family = $nameParts[0] ?? '';
        $first = $nameParts[1] ?? '';
        $middle = $nameParts[2] ?? '';


        /*
        |--------------------------------------------------------------------------
        | DOCUMENT LIST
        |--------------------------------------------------------------------------
        */

        $documents = [

            [
                'label' => 'Transcript of Record',
                'spec' => ''
            ],

            [
                'label' => 'Certification/CAV/S',
                'spec' => ''
            ],

            [
                'label' => 'Diploma',
                'spec' => ''
            ],

            [
                'label' => 'Transfer Credentials',
                'spec' => ''
            ],

            [
                'label' => 'Permanent Record (F-137-A)',
                'spec' => ''
            ],

            [
                'label' => 'Good Moral Character',
                'spec' => ''
            ],

            [
                'label' => 'Others',
                'spec' => $request->others_specification
            ],

        ];


        /*
        |--------------------------------------------------------------------------
        | FEES
        |--------------------------------------------------------------------------
        */

        $feeLines = [

            [
                'key' => 'Transcript of Record',
                'fee' => $requested('Transcript of Record')
                    ? '₱' . number_format($request->totalFee(), 2)
                    : ''
            ],

            [
                'key' => 'Diploma',
                'fee' => $requested('Diploma')
                    ? '₱100.00'
                    : ''
            ],

            [
                'key' => 'Transfer Credentials',
                'fee' => $requested('Transfer Credentials')
                    ? '₱100.00'
                    : ''
            ],

            [
                'key' => 'Certification',
                'fee' => $requested('Certification')
                    ? '₱100.00'
                    : ''
            ],

        ];

    @endphp


    {{-- =========================================
        PRINT BUTTON
    ========================================= --}}

    <div
        class="no-print"
        style="
            text-align:center;
            padding:20px;
        "
    >

        <a
            href="{{ auth()->user()?->isStudent()
                ? route('student.documents.show', $request)
                : route('registrar.document-requests.show', $request) }}"
            style="
                display:inline-block;
                margin-right:10px;
                background:#f3f4f6;
                color:#111827;
                text-decoration:none;
                border:1px solid #d1d5db;
                padding:12px 25px;
                border-radius:8px;
                font-weight:bold;
                font-size:14px;
            "
        >

            ← Back to Request

        </a>


        <button
            onclick="window.print()"
            style="
                background:#1e3a8a;
                color:white;
                border:none;
                padding:12px 25px;
                border-radius:8px;
                cursor:pointer;
                font-weight:bold;
                font-size:14px;
            "
        >

            Print Requisition Form

        </button>


        <button
            type="button"
            id="download-pdf"
            style="
                background:#0f766e;
                color:white;
                border:none;
                margin-left:10px;
                padding:12px 25px;
                border-radius:8px;
                cursor:pointer;
                font-weight:bold;
                font-size:14px;
            "
        >

            Download PDF

        </button>


        {{--
            Chrome writes the date, page title and URL across the top and
            bottom of anything it prints itself, and window.print() has no
            switch for it. The PDF path sidesteps that entirely because the
            document is drawn here rather than printed by the browser, so it
            leads the advice. .no-print keeps both off the paper.
        --}}

        <p
            style="
                margin:16px 0 0;
                font-size:13px;
                line-height:1.5;
                color:#6b7280;
            "
        >

            <strong>Download PDF</strong> gives a clean sheet with no browser
            header. If you print straight from the browser instead, turn off
            <strong>Headers and footers</strong> in the print dialog first.

        </p>

    </div>


    {{-- =========================================
        A4 DOCUMENT
    ========================================= --}}

    <div class="document-page">


        {{-- =====================================
            DOCUMENT HEADER
        ====================================== --}}

        <div class="document-header">

            <div class="header-main">

                {{-- LEFT LOGO --}}
                <div class="header-logo left-logo">
                    <img
                        src="{{ asset('images/requisition-header-left-logo.png') }}"
                        alt="ISUFST Logo"
                    >
                </div>


                {{-- CENTER INFORMATION --}}
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


                {{-- RIGHT LOGO --}}
                <div class="header-logo right-logo">
                    <img
                        src="{{ asset('images/requisition-header-right-logo.png') }}"
                        alt="Bagong Pilipinas"
                    >
                </div>

            </div>


            {{-- OFFICE INFORMATION --}}
            <div class="office-section">

                <div class="office-name">
                    COLLEGE OF INFORMATION AND COMMUNICATIONS TECHNOLOGY
                </div>

                <div class="contact-info">

                    <span>Website: isufst.edu.ph</span>

                    <span class="separator">|</span>

                    <span>Contact No. +639634638274</span>

                    <span class="separator">|</span>

                    <span>
                        Email:
                        <a href="mailto:cict_dingle@isufst.edu.ph">
                            cict_dingle@isufst.edu.ph
                        </a>
                    </span>

                </div>

            </div>


            {{-- BOTTOM LINE --}}
            <div class="header-line"></div>

        </div>


        {{-- =====================================
            FORM TITLE
        ====================================== --}}

        <h1 class="form-title">

            REQUISITION FORM

        </h1>



        {{-- =====================================
            DOCUMENT REQUEST
        ====================================== --}}

        <div class="section-space">

            <p style="font-size:14px; margin-bottom:8px;">

                May I apply for:

            </p>

        </div>


        <div class="request-section">

            @foreach ($documents as $document)

                <div class="request-item">

                    <div class="check-line">

                        @if (
                            $requested($document['label']) ||
                            (
                                $document['label'] === 'Others' &&
                                !empty($document['spec'])
                            )
                        )

                            ✓

                        @endif

                    </div>


                    <span>

                        {{ $document['label'] }}

                    </span>


                    @if ($document['spec'])

                        <div
                            class="medium-line"
                            style="max-width:180px;"
                        >

                            {{ $document['spec'] }}

                        </div>

                    @endif

                </div>

            @endforeach

        </div>


        {{-- =====================================
            PURPOSE
        ====================================== --}}

        <div class="section-space">


            {{-- EMPLOYMENT --}}

            <div class="purpose-row">

                <strong>

                    Purpose:

                </strong>


                <div class="small-line">

                    @if (
                        $purpose ===
                        \App\Enums\RequestPurposeType::Employment
                    )

                        ✓

                    @endif

                </div>


                Employment

            </div>


            {{-- PRC --}}

            <div class="purpose-row">

                <div class="small-line">

                    @if (
                        $purpose ===
                        \App\Enums\RequestPurposeType::PRC
                    )

                        ✓

                    @endif

                </div>


                PRC

            </div>


            {{-- TRANSFER --}}

            <div class="purpose-row">

                <div class="small-line">

                    @if (
                        $purpose ===
                        \App\Enums\RequestPurposeType::Transfer
                    )

                        ✓

                    @endif

                </div>


                Transfer to


                <div class="medium-line">

                    {{ $request->transfer_to }}

                </div>

            </div>


            <p
                style="
                    margin-top:-3px;
                    text-align:center;
                    font-size:12px;
                "
            >

                (Name and Address of School/College/University)

            </p>


        </div>


        {{-- =====================================
            GRADUATION STATUS
        ====================================== --}}

        <div class="section-space">


            <div class="graduation-row">


                <div class="small-line">

                    @if (
                        $educationalStatus ===
                        \App\Enums\EducationalStatus::Graduated
                    )

                        ✓

                    @endif

                </div>

                Graduated


                <div
                    class="small-line"
                    style="margin-left:40px;"
                >

                    @if (
                        $educationalStatus ===
                        \App\Enums\EducationalStatus::Graduated &&
                        $educationalLevel ===
                        \App\Enums\EducationalLevel::HighSchool
                    )

                        ✓

                    @endif

                </div>

                High School


                <div
                    class="small-line"
                    style="margin-left:35px;"
                >

                    @if (
                        $educationalStatus ===
                        \App\Enums\EducationalStatus::Graduated &&
                        $educationalLevel ===
                        \App\Enums\EducationalLevel::College
                    )

                        ✓

                    @endif

                </div>

                College


            </div>


            <div class="graduation-row">


                <div class="small-line">

                    @if (
                        $educationalStatus ===
                        \App\Enums\EducationalStatus::NotGraduated
                    )

                        ✓

                    @endif

                </div>

                Not Graduated


            </div>


        </div>


        {{-- =====================================
            STUDENT NAME
        ====================================== --}}

        <div class="student-name-row">


            <span class="student-name-label">

                Student's Name:

            </span>


            {{-- FAMILY --}}

            <div class="name-field">

                <div class="name-line">

                    {{ $family }}

                </div>

                <div class="name-label">

                    (Family Name)

                </div>

            </div>


            {{-- FIRST --}}

            <div class="name-field">

                <div class="name-line">

                    {{ $first }}

                </div>

                <div class="name-label">

                    (First Name)

                </div>

            </div>


            {{-- MIDDLE --}}

            <div class="name-field">

                <div class="name-line">

                    {{ $middle }}

                </div>

                <div class="name-label">

                    (Middle Name)

                </div>

            </div>


        </div>


        {{-- =====================================
            ADDRESS
        ====================================== --}}

        <div class="row section-space">


            <span class="label">

                Address:

            </span>


            <div class="medium-line">

                {{ $request->student_address }}

            </div>


            <span
                class="label"
                style="margin-left:35px;"
            >

                Tel. No.:

            </span>


            <div class="small-line">

                {{ $request->student_contact }}

            </div>


        </div>


        {{-- =====================================
            COURSE AND YEAR
        ====================================== --}}

        <div class="row">


            <span class="label">

                Course &amp; Year:

            </span>


            <div
                class="medium-line"
                style="max-width:280px;"
            >

                {{ $request->student_course_year }}

            </div>


        </div>


        {{-- =====================================
            STUDENT SIGNATURE
        ====================================== --}}

        <div class="student-signature">

            <div class="signature-line"></div>

            <div class="signature-caption">

                (Signature Over Printed Name)

            </div>

        </div>


        {{-- =====================================
            MODE OF CLAIMING
        ====================================== --}}

        <div class="claim-section">


            <p class="label">

                Mode of Claiming:

            </p>


            {{-- PERSONAL --}}

            <div class="claim-option">

                <span class="checkbox-line">

                    @if (
                        $claimMode ===
                        \App\Enums\ClaimMode::Personal
                    )

                        ✓

                    @endif

                </span>


                I shall come back for my record personally.

            </div>


            {{-- REPRESENTATIVE --}}

            <div class="claim-option">

                <span class="checkbox-line">

                    @if (
                        $claimMode ===
                        \App\Enums\ClaimMode::Representative
                    )

                        ✓

                    @endif

                </span>


                I shall have my authorized representative to claim my request.


                @if (
                    $claimMode ===
                    \App\Enums\ClaimMode::Representative &&
                    $request->representative_name
                )

                    <span class="representative-line">

                        {{ $request->representative_name }}

                    </span>

                @endif


            </div>


        </div>


        {{-- =====================================
            ACTION TAKEN
        ====================================== --}}

        <div class="action-section">


            <div class="action-title">

                ACTION TAKEN:

            </div>


            @foreach ($feeLines as $feeLine)


                <div class="action-row">


                    <span class="action-name">

                        {{ $feeLine['key'] }}

                    </span>


                    <div class="action-line">

                        {{ $feeLine['fee'] }}

                    </div>


                    @if (
                        $feeLine['key'] ===
                        'Transcript of Record'
                    )

                        original copy (₱100.00/page)

                    @else

                        per request (₱100.00)

                    @endif


                </div>


            @endforeach


            {{-- SERVICE CHARGE --}}

            <div class="action-row">


                <span class="action-name">

                    Service Charge

                </span>


                <div class="action-line">

                    {{ $request->documents->isNotEmpty()
                        ? '₱100.00'
                        : ''
                    }}

                </div>


                per transaction (₱100.00)


            </div>


            {{-- TOTAL --}}

            <div
                class="action-row"
                style="font-weight:bold;"
            >


                <span class="action-name">

                    TOTAL ₱

                </span>


                <div class="action-line">

                    {{ $request->documents->isNotEmpty()
                        ? number_format($request->totalFee(), 2)
                        : ''
                    }}

                </div>


            </div>


        </div>


        {{-- =====================================
            CERTIFICATION
        ====================================== --}}

        <div class="certification">

            I certify that the above-named student has paid all his/her
            account with this college.

        </div>


        {{-- =====================================
            CASHIER AND DATE SIGNATURE
        ====================================== --}}

        <div class="signature-section">


            <div class="signature-box">

                <div class="signature-line"></div>

                <p>

                    Date

                </p>

            </div>


            <div class="signature-box">

                <div class="signature-line"></div>

                <p class="signature-name">

                    MAYFLOR C. PADIOS

                </p>

            </div>


        </div>


        {{-- =====================================
            APPROVAL
        ====================================== --}}

        <div class="approval-section">


            <p>

                Approved:

            </p>


            <p class="approval-name">

                AGNES MAE L. ACUESTA, PhD

            </p>


            <p>

                Acting Registrar

            </p>


        </div>


        {{--
            The strip already carries the rule and the motto beneath it, so
            repeating the line above the logos printed it twice - and put it
            ahead of the rule, where it does not belong. One strip, once.
        --}}

        <div class="doc-footer">

            <img
                src="{{ asset('images/requisition-footer-strip.jpeg') }}"
                alt="ISUFST logos and motto: Integrity, Social Justice, Discipline, Academic Excellence"
                class="doc-footer-strip"
            >

        </div>


    </div>


    {{--
        PDF EXPORT

        Chrome stamps a date, title and URL bar across anything it prints
        itself, and window.print() exposes no switch for it. So instead of
        printing, the sheet is drawn here and laid onto a single long bond
        page by jsPDF. The whole image is scaled to the printable box, which
        means the result is always exactly one sheet no matter how the rows
        reflow.
    --}}

    <script
        src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"
    ></script>

    <script
        src="https://cdn.jsdelivr.net/npm/jspdf@2.5.2/dist/jspdf.umd.min.js"
    ></script>

    <script>

        (function () {

            /* Long bond, with the same margins the printed page uses. */

            var SHEET_W = 215.9;
            var SHEET_H = 330.2;
            var MARGIN  = { top: 10, right: 12, bottom: 10, left: 12 };


            window.buildRequisitionPdf = async function () {

                var page = document.querySelector('.document-page');
                var rect = page.getBoundingClientRect();

                var previousScroll = window.scrollY;
                window.scrollTo(0, 0);

                try {

                    var canvas = await html2canvas(page, {
                        scale: 2,
                        backgroundColor: '#ffffff',
                        useCORS: true,
                        logging: false,
                        width: Math.ceil(rect.width),
                        height: Math.ceil(rect.height),
                        windowWidth: document.documentElement.scrollWidth,
                        windowHeight: document.documentElement.scrollHeight
                    });

                    /* CSS pixels to millimetres - jsPDF works in mm. */

                    var boxW = rect.width  * 25.4 / 96;
                    var boxH = rect.height * 25.4 / 96;

                    var maxW = SHEET_W - MARGIN.left - MARGIN.right;
                    var maxH = SHEET_H - MARGIN.top  - MARGIN.bottom;

                    var fit   = Math.min(maxW / boxW, maxH / boxH);
                    var drawW = boxW * fit;
                    var drawH = boxH * fit;

                    var pdf = new jspdf.jsPDF({
                        orientation: 'portrait',
                        unit: 'mm',
                        format: [SHEET_W, SHEET_H]
                    });

                    pdf.addImage(
                        canvas.toDataURL('image/jpeg', 0.95),
                        'JPEG',
                        MARGIN.left + (maxW - drawW) / 2,
                        MARGIN.top  + (maxH - drawH) / 2,
                        drawW,
                        drawH
                    );

                    return pdf;

                } finally {
                    window.scrollTo(0, previousScroll);
                }
            };


            var button = document.getElementById('download-pdf');

            if (!button) return;

            var idle = button.innerHTML;

            button.addEventListener('click', async function () {

                button.disabled = true;
                button.style.opacity = '0.7';
                button.textContent = 'Preparing PDF...';

                try {
                    var pdf = await window.buildRequisitionPdf();
                    pdf.save('Requisition-{{ $request->request_number }}.pdf');
                } catch (error) {
                    window.alert(
                        'The PDF could not be created here. ' +
                        'Use "Print Requisition Form" instead.'
                    );
                } finally {
                    button.disabled = false;
                    button.style.opacity = '';
                    button.innerHTML = idle;
                }
            });

        })();

    </script>


</body>
</html>