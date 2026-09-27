<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }} export</title>
    <style>
        @page {
            margin: 24px 18px;
        }

        body {
            margin: 0;
            color: #0F172A;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 16px;
        }

        .meta {
            margin-bottom: 14px;
            color: #64748B;
            font-size: 9px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #CBD5E1;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #F1F5F9;
            font-size: 9px;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) {
            background: #F8FAFC;
        }

        .empty {
            padding: 18px;
            border: 1px dashed #CBD5E1;
            color: #64748B;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">
        Generated {{ $generatedAt }} &middot;
        {{ $scope === 'all' ? 'All records' : 'Current filters' }} &middot;
        {{ count($rows) }} row{{ count($rows) === 1 ? '' : 's' }}
    </p>

    @if (count($rows) === 0)
        <p class="empty">No records to export.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
