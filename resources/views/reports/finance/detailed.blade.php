<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detailed Financial Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin-bottom: 5px;
            color: #3949ab;
        }
        .header p {
            margin: 0;
            color: #666;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-label {
            font-weight: bold;
            width: 40%;
        }
        .summary-value {
            text-align: right;
            width: 60%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th, td {
            padding: 6px;
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
        .section-title {
            background-color: #3949ab;
            color: white;
            padding: 5px 10px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Detailed Financial Report</h1>
        <p>Period: {{ $period['start'] }} to {{ $period['end'] }}</p>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    <div class="summary-box">
        <h3>Financial Overview</h3>
        <div class="summary-row">
            <span class="summary-label">Total Donations:</span>
            <span class="summary-value">${{ number_format($total_donations, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Expenses:</span>
            <span class="summary-value">${{ number_format($total_expenses, 2) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Net Income:</span>
            <span class="summary-value" style="color: {{ $net_income >= 0 ? 'green' : 'red' }}">
                ${{ number_format($net_income, 2) }}
            </span>
        </div>
    </div>

    <div class="section-title">
        Donations Detail
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Receipt #</th>
                <th>Donor</th>
                <th>Category</th>
                <th>Project</th>
                <th>Payment Method</th>
                <th class="amount-column">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donations as $donation)
            <tr>
                <td>{{ $donation->donation_date->format('Y-m-d') }}</td>
                <td>{{ $donation->receipt_number }}</td>
                <td>
                    @if($donation->is_anonymous)
                        Anonymous
                    @else
                        {{ $donation->member ? $donation->member->full_name : 'N/A' }}
                    @endif
                </td>
                <td>{{ $donation->category ? $donation->category->name : 'N/A' }}</td>
                <td>{{ $donation->project ? $donation->project->name : 'N/A' }}</td>
                <td>{{ $donation->payment_method }}</td>
                <td class="amount-column">${{ number_format($donation->amount, 2) }}</td>
            </tr>
            @endforeach
            @if(count($donations) == 0)
            <tr>
                <td colspan="7" style="text-align: center;">No donations found for this period</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6">Total</th>
                <th class="amount-column">${{ number_format($total_donations, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="page-break"></div>

    <div class="section-title">
        Expenses Detail
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Approved By</th>
                <th class="amount-column">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date->format('Y-m-d') }}</td>
                <td>{{ $expense->category ? $expense->category->name : 'N/A' }}</td>
                <td>{{ $expense->description }}</td>
                <td>{{ $expense->approver ? $expense->approver->name : 'N/A' }}</td>
                <td class="amount-column">${{ number_format($expense->amount, 2) }}</td>
            </tr>
            @endforeach
            @if(count($expenses) == 0)
            <tr>
                <td colspan="5" style="text-align: center;">No expenses found for this period</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th class="amount-column">${{ number_format($total_expenses, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report is automatically generated by the Church Management System.</p>
        <p>&copy; {{ date('Y') }} Church Management System. All rights reserved.</p>
    </div>
</body>
</html>
