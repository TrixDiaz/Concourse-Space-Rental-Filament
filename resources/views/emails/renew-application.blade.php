<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Renewal Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Lease Renewal Application</h2>
    </div>

    <div class="content">
        <p>Dear Admin,</p>

        <p>A new lease renewal application has been submitted with the following details:</p>

        <h3>Tenant Information:</h3>
        <ul>
            <li><strong>Tenant Name:</strong> {{ $tenant->name }}</li>
            <li><strong>Email:</strong> {{ $tenant->email }}</li>
            <li><strong>Phone:</strong> {{ $tenant->phone_number ?? 'Not provided' }}</li>
        </ul>

        <h3>Space Details:</h3>
        <ul>
            <li><strong>Concourse:</strong> {{ $concourse->name }}</li>
            <li><strong>Space Number:</strong> {{ $space->name }}</li>
            <li><strong>Current Lease End Date:</strong> {{ $space->lease_end ? $space->lease_end->format('F j, Y') : 'Not set' }}</li>
        </ul>

        <p>Please review this application in your admin dashboard to process the renewal request.</p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply directly to this email.</p>
        <p>© {{ date('Y') }} Your Company Name. All rights reserved.</p>
    </div>
</body>
</html>