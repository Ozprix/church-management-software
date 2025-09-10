<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Financial Report</title>
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
        .project-info {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .project-title {
            font-size: 16px;
            font-weight: bold;
            color: #3949ab;
            margin-bottom: 10px;
        }
        .project-description {
            margin-bottom: 15px;
            font-style: italic;
        }
        .project-meta {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        .project-meta-item {
            width: 50%;
            margin-bottom: 5px;
        }
        .project-meta-label {
            font-weight: bold;
        }
        .progress-container {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .progress-bar {
            background-color: #eee;
            height: 20px;
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 5px;
        }
        .progress-fill {
            background-color: #3949ab;
            height: 100%;
        }
        .progress-text {
            text-align: center;
            font-weight: bold;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Project Financial Report</h1>
        <p>Period: {{ $period['start'] }} to {{ $period['end'] }}</p>
        <p>Generated on: {{ $generated_at }}</p>
    </div>

    <div class="project-info">
        <div class="project-title">{{ $project->name }}</div>
        <div class="project-description">{{ $project->description }}</div>
        
        <div class="project-meta">
            <div class="project-meta-item">
                <span class="project-meta-label">Start Date:</span> {{ $project->start_date->format('Y-m-d') }}
            </div>
            <div class="project-meta-item">
                <span class="project-meta-label">End Date:</span> {{ $project->end_date ? $project->end_date->format('Y-m-d') : 'Ongoing' }}
            </div>
            <div class="project-meta-item">
                <span class="project-meta-label">Status:</span> {{ ucfirst($project->status) }}
            </div>
            <div class="project-meta-item">
                <span class="project-meta-label">Goal Amount:</span> ${{ number_format($project->goal_amount, 2) }}
            </div>
        </div>
        
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $percent_complete }}%"></div>
            </div>
            <div class="progress-text">
                ${{ number_format($project->current_amount, 2) }} of ${{ number_format($project->goal_amount, 2) }} ({{ $percent_complete }}% Complete)
            </div>
            <div class="progress-text" style="color: {{ $remaining_amount > 0 ? 'red' : 'green' }}">
                {{ $remaining_amount > 0 ? 'Remaining: $' . number_format($remaining_amount, 2) : 'Goal Reached!' }}
            </div>
        </div>
    </div>

    <div class="section-title">
        Donation History
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Receipt #</th>
                <th>Donor</th>
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
                <td>{{ $donation->payment_method }}</td>
                <td class="amount-column">${{ number_format($donation->amount, 2) }}</td>
            </tr>
            @endforeach
            @if(count($donations) == 0)
            <tr>
                <td colspan="5" style="text-align: center;">No donations found for this project in the selected period</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4">Total</th>
                <th class="amount-column">${{ number_format($total_donations, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This report is automatically generated by the Church Management System.</p>
        <p>&copy; {{ date('Y') }} Church Management System. All rights reserved.</p>
    </div>
</body>
</html>
