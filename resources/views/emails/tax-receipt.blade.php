<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            color: #3949ab;
            margin-bottom: 5px;
        }
        .content {
            margin-bottom: 30px;
        }
        .receipt-details {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .receipt-details p {
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .button {
            display: inline-block;
            background-color: #3949ab;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($organization->logo_path)
                <img src="{{ asset('storage/' . $organization->logo_path) }}" alt="{{ $organization->name }}" class="logo">
            @endif
            <h1>{{ $organization->name }}</h1>
            <p>{{ $organization->address }}</p>
            <p>{{ $organization->phone }} | {{ $organization->email }}</p>
        </div>

        <div class="content">
            <p>Dear {{ $member->first_name }} {{ $member->last_name }},</p>
            
            @if($receipt->is_annual)
                <p>Thank you for your generous donations to {{ $organization->name }} during the {{ $receipt->tax_year }} tax year. Your support helps us fulfill our mission and make a positive impact in our community.</p>
                
                <p>Attached to this email, you will find your official annual tax receipt for the {{ $receipt->tax_year }} tax year. This document summarizes all your tax-deductible donations made during this period.</p>
            @else
                <p>Thank you for your generous donation to {{ $organization->name }}. Your support helps us fulfill our mission and make a positive impact in our community.</p>
                
                <p>Attached to this email, you will find your official tax receipt for your recent donation. This document can be used for tax purposes.</p>
            @endif
            
            <div class="receipt-details">
                <p><strong>Receipt Number:</strong> {{ $receipt->receipt_number }}</p>
                <p><strong>Issue Date:</strong> {{ $receipt->issue_date->format('F j, Y') }}</p>
                <p><strong>Total Amount:</strong> ${{ number_format($receipt->amount, 2) }}</p>
                @if(!$receipt->is_annual)
                    <p><strong>Donation Date:</strong> {{ $receipt->donation_date->format('F j, Y') }}</p>
                @endif
                <p><strong>Tax Year:</strong> {{ $receipt->tax_year }}</p>
            </div>
            
            <p>Please keep this receipt for your tax records. If you have any questions or need further assistance, please don't hesitate to contact us at {{ $organization->email }} or {{ $organization->phone }}.</p>
            
            <p>Thank you again for your generosity and support.</p>
            
            <p>Sincerely,<br>
            {{ $organization->name }}</p>
        </div>

        <div class="footer">
            <p>{{ $organization->name }} | {{ $organization->address }}</p>
            <p>Tax ID: {{ $organization->tax_id }}</p>
            <p>This email was sent to {{ $member->email }}. If you received this email in error, please contact us.</p>
        </div>
    </div>
</body>
</html>
