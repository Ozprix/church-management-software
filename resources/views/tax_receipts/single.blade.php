<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            margin-bottom: 5px;
            color: #3949ab;
        }
        .header p {
            margin: 0;
            color: #666;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .receipt-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            color: #3949ab;
        }
        .receipt-number {
            text-align: right;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .receipt-date {
            text-align: right;
            margin-bottom: 20px;
        }
        .address-block {
            margin-bottom: 20px;
        }
        .donor-info {
            margin-bottom: 20px;
        }
        .donation-details {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .donation-details p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .amount-column {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
        }
        .signature {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .signature-line {
            display: inline-block;
            width: 200px;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .tax-id {
            font-weight: bold;
        }
        .watermark {
            position: absolute;
            top: 40%;
            left: 20%;
            transform: rotate(-45deg);
            font-size: 100px;
            color: rgba(200, 200, 200, 0.2);
            z-index: -1;
        }
    </style>
</head>
<body>
    @if($receipt->status === 'voided')
    <div class="watermark">VOID</div>
    @endif

    <div class="container">
        <div class="header">
            @if($organization->logo_path)
                <img src="{{ storage_path('app/public/' . $organization->logo_path) }}" alt="{{ $organization->name }}" class="logo">
            @endif
            <h1>{{ $organization->name }}</h1>
            <p>{{ $organization->address }}</p>
            <p>{{ $organization->phone }} | {{ $organization->email }}</p>
            <p class="tax-id">Tax ID: {{ $organization->tax_id }}</p>
        </div>

        <div class="receipt-title">
            OFFICIAL RECEIPT FOR INCOME TAX PURPOSES
        </div>

        <div class="receipt-number">
            Receipt #: {{ $receipt->receipt_number }}
        </div>

        <div class="receipt-date">
            Issue Date: {{ $receipt->issue_date->format('F j, Y') }}
        </div>

        <div class="donor-info">
            <strong>Donor Information:</strong><br>
            {{ $member->first_name }} {{ $member->last_name }}<br>
            {{ $member->address }}<br>
            {{ $member->city }}, {{ $member->state }} {{ $member->zip }}<br>
            {{ $member->email }}
        </div>

        <div class="donation-details">
            <p><strong>Donation Date:</strong> {{ $donation->donation_date->format('F j, Y') }}</p>
            <p><strong>Donation Amount:</strong> ${{ number_format($donation->amount, 2) }}</p>
            <p><strong>Payment Method:</strong> {{ ucfirst($donation->payment_method) }}</p>
            @if($donation->category)
                <p><strong>Donation Category:</strong> {{ $donation->category->name }}</p>
            @endif
            @if($donation->project)
                <p><strong>Project:</strong> {{ $donation->project->name }}</p>
            @endif
            @if($donation->campaign)
                <p><strong>Campaign:</strong> {{ $donation->campaign->name }}</p>
            @endif
        </div>

        <p>
            This is to acknowledge that {{ $organization->name }} has received the above donation from {{ $member->first_name }} {{ $member->last_name }} during the {{ $receipt->tax_year }} tax year. No goods or services were provided in exchange for this donation.
        </p>

        <div class="signature">
            <div class="signature-line"></div><br>
            Authorized Signature<br>
            {{ $organization->name }}
        </div>

        <div class="footer">
            <p>This receipt is an important document. Please keep it for your tax records.</p>
            <p>{{ $organization->name }} is a registered charitable organization.</p>
            <p>Tax ID: {{ $organization->tax_id }}</p>
        </div>
    </div>
</body>
</html>
