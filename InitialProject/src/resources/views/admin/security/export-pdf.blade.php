<!DOCTYPE html>
<html>
<head>
    <title>Security Events Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .threat-level {
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
        }
        .high {
            background-color: #ffebee;
            color: #c62828;
        }
        .medium {
            background-color: #fff3e0;
            color: #ef6c00;
        }
        .low {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Security Events Report</h2>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Event Type</th>
                <th>User/IP</th>
                <th>Details</th>
                <th>Threat Level</th>
            </tr>
        </thead>
        <tbody>
            @foreach($events as $event)
            <tr>
                <td>{{ $event->created_at->format('Y-m-d H:i:s') }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $event->event_type)) }}</td>
                <td>
                    @if($event->user_id)
                        {{ $event->user->fname_en ?? 'Unknown' }} {{ $event->user->lname_en ?? '' }}<br>
                        <small>{{ $event->ip_address }}</small>
                    @else
                        {{ $event->ip_address }}
                    @endif
                </td>
                <td>{{ $event->details }}</td>
                <td>
                    <span class="threat-level {{ $event->threat_level }}">
                        {{ ucfirst($event->threat_level) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report contains {{ $events->count() }} security events.</p>
    </div>
</body>
</html> 