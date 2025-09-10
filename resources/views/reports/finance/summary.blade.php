<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Financial Summary Report</title>
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
        .category-table th:last-child,
        .category-table td:last-child {
            text-align: right;
        }
        .project-table .progress-bar {
            background-color: #eee;
            height: 10px;
            width: 100%;
            border-radius: 5px;
            overflow: hidden;
        }
        .project-table .progress-fill {
            background-color: #3949ab;
            height: 100%;
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
        <h1>Financial Summary Report</h1>
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

    <h3>Donations by Category</h3>
    <table class="category-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donations_by_category as $category)
            <tr>
                <td>{{ $category->name }}</td>
                <td>${{ number_format($category->total, 2) }}</td>
                <td>{{ number_format(($category->total / $total_donations) * 100, 1) }}%</td>
            </tr>
            @endforeach
            @if(count($donations_by_category) == 0)
            <tr>
                <td colspan="3" style="text-align: center;">No donations found for this period</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th>${{ number_format($total_donations, 2) }}</th>
                <th>100%</th>
            </tr>
        </tfoot>
    </table>

    <h3>Active Projects Funding Status</h3>
    <table class="project-table">
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Goal</th>
                <th>Current Amount</th>
                <th>Progress</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{{ $project['name'] }}</td>
                <td>${{ number_format($project['goal_amount'], 2) }}</td>
                <td>${{ number_format($project['current_amount'], 2) }}</td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $project['percent_complete'] }}%"></div>
                    </div>
                    {{ $project['percent_complete'] }}% Complete
                </td>
            </tr>
            @endforeach
            @if(count($projects) == 0)
            <tr>
                <td colspan="4" style="text-align: center;">No active projects found</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>This report is automatically generated by the Church Management System.</p>
        <p>&copy; {{ date('Y') }} Church Management System. All rights reserved.</p>
    </div>
</body>
</html>
