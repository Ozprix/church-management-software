<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Donations PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #f3f3f3;
        }
    </style>
</head>

<body>
    <h2>Donations Report</h2>
    <table>
        <thead>
            <tr>
                <th>Member</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($donations as $donation)
            <tr>
                <td>{{ $donation->member->name ?? 'Anonymous' }}</td>
                <td>₦{{ number_format($donation->amount) }}</td>
                <td>{{ $donation->created_at->toDateString() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>