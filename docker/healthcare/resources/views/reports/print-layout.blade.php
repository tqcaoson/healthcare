<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Clinical Report</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            color: #111827;
        }

        .section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #d1d5db;
            padding: 10px;
        }
    </style>
</head>

<body>

    <div class="section">

        <div class="section-title">
            Patient Information
        </div>

        <table class="table">

            <tr>
                <th>Name</th>
                <td>{{ $patient->name }}</td>
            </tr>

            <tr>
                <th>MRN</th>
                <td>{{ $patient->mrn }}</td>
            </tr>

        </table>
    </div>

    <div class="section">

        <div class="section-title">
            Clinical Summary
        </div>

        <p>
            {{ $summary }}
        </p>
    </div>

</body>
</html>