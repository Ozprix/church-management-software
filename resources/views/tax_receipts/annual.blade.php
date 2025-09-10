<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Annual Tax Receipt</title>
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
        .summary-box {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
        }
        .summary-box p {
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
        .page-break {
            page-break-after: always;
        }
        .category-header {
            background-color: #3949ab;
            color: white;
            padding: 5px 10px;
            margin-top: 20px;
            margin-bottom: 10px;
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
            ANNUAL RECEIPT FOR INCOME TAX PURPOSES<br>
            {{ $year }} TAX YEAR
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

        <div class="summary-box">
            <p><strong>Tax Year:</strong> {{ $year }}</p>
            <p><strong>Total Donations:</strong> ${{ number_format($total_amount, 2) }}</p>
            <p><strong>Number of Donations:</strong> {{ $donations->count() }}</p>
            <p><strong>Date Range:</strong> {{ $donations->min('donation_date')->format('M j, Y') }} to {{ $donations->max('donation_date')->format('M j, Y') }}</p>
        </div>

        <p>
            This is to acknowledge that {{ $organization->name }} has received the following donations from {{ $member->first_name }} {{ $member->last_name }} during the {{ $year }} tax year. No goods or services were provided in exchange for these donations.
        </p>

        <h3>Donation Summary by Category</h3>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="amount-column">Amount</th>
                    <th class="amount-column">% of Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categorized_donations as $category)
                <tr>
                    <td>{{ $category['name'] }}</td>
                    <td class="amount-column">${{ number_format($category['total'], 2) }}</td>
                    <td class="amount-column">{{ number_format(($category['total'] / $total_amount) * 100, 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td>Total</td>
                    <td class="amount-column">${{ number_format($total_amount, 2) }}</td>
                    <td class="amount-column">100%</td>
                </tr>
            </tfoot>
        </table>

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

    <div class="page-break"></div>

    <div class="container">
        <div class="header">
            <h1>Donation Details</h1>
            <p>Receipt #: {{ $receipt->receipt_number }} | {{ $member->first_name }} {{ $member->last_name }} | {{ $year }} Tax Year</p>
        </div>

        @foreach($categorized_donations as $category)
        <div class="category-header">
            {{ $category['name'] }} - ${{ number_format($category['total'], 2) }}
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Receipt #</th>
                    <th>Payment Method</th>
                    <th class="amount-column">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($category['donations'] as $donation)
                <tr>
                    <td>{{ $donation->donation_date->format('M j, Y') }}</td>
                    <td>{{ $donation->receipt_number }}</td>
                    <td>{{ ucfirst($donation->payment_method) }}</td>
                    <td class="amount-column">${{ number_format($donation->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3">Subtotal</td>
                    <td class="amount-column">${{ number_format($category['total'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
        @endforeach

        <table>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3">Total Donations for {{ $year }}</td>
                    <td class="amount-column">${{ number_format($total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <p>Page 2 of 2 | Receipt #: {{ $receipt->receipt_number }}</p>
            <p>{{ $organization->name }} | Tax ID: {{ $organization->tax_id }}</p>
        </div>
    </div>
</body>
</html>
