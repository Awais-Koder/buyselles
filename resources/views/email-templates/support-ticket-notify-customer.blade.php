<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ translate('Support_Ticket_Update') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 650px;
            margin: 40px auto;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
        }

        .header {
            background-color: #3c3c3c;
            padding: 24px 32px;
        }

        .header img {
            height: 40px;
        }

        .body {
            padding: 32px;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            background: #fff3cd;
            color: #856404;
        }

        h2 {
            color: #222;
            font-size: 20px;
            margin: 16px 0 8px;
        }

        p {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
            margin: 6px 0;
        }

        .info-row {
            display: flex;
            gap: 8px;
            margin: 4px 0;
        }

        .label {
            font-weight: 600;
            color: #333;
            min-width: 90px;
        }

        .btn {
            display: inline-block;
            margin-top: 24px;
            padding: 12px 28px;
            background-color: #1a73e8;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
        }

        .footer {
            background: #f4f4f4;
            padding: 16px 32px;
            text-align: center;
            font-size: 12px;
            color: #999;
        }

        .divider {
            border: none;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }

        .reply-box {
            background: #f9f9f9;
            border-left: 4px solid #1a73e8;
            padding: 12px 16px;
            border-radius: 0 4px 4px 0;
            margin-top: 12px;
            font-size: 14px;
            color: #333;
        }
    </style>
</head>

<body>
    <?php
    use App\Models\SupportTicket;
    use App\Models\SupportTicketConv;
    
    $ticket = SupportTicket::with('customer')->find($ticketId);
    $latestReply = SupportTicketConv::where('support_ticket_id', $ticketId)->whereNotNull('admin_message')->latest()->first();
    
    $companyName = getWebConfig(name: 'company_name');
    $companyEmail = getWebConfig(name: 'company_email');
    $companyLogo = getWebConfig(name: 'company_web_logo');
    $ticketUrl = url('support-ticket/' . $ticketId);
    
    $customerName = $ticket?->customer?->name ?? translate('Customer');
    ?>

    <div class="wrapper">
        <div class="header">
            <img src="{{ getStorageImages(path: $companyLogo, type: 'backend-logo') }}" alt="{{ $companyName }}">
        </div>
        <div class="body">
            <span class="badge">{{ translate('Ticket_Update') }}</span>
            <h2>{{ translate('Your_support_ticket_has_received_a_reply') }}</h2>
            <p>{{ translate('Hi') }}, {{ $customerName }}.</p>
            <p>{{ translate('The_support_team_has_replied_to_your_ticket') }}</p>
            <hr class="divider">
            <div class="info-row"><span class="label">{{ translate('ticket_id') }}:</span>
                <span>#{{ $ticket->id }}</span></div>
            <div class="info-row"><span class="label">{{ translate('subject') }}:</span>
                <span>{{ $ticket->subject }}</span></div>
            @if ($latestReply?->admin_message)
                <hr class="divider">
                <p><span class="label">{{ translate('reply') }}:</span></p>
                <div class="reply-box">{{ $latestReply->admin_message }}</div>
            @endif
            <a href="{{ $ticketUrl }}" class="btn">{{ translate('View_Ticket') }}</a>
        </div>
        <div class="footer">
            {{ $companyName }} &mdash; {{ $companyEmail }}
        </div>
    </div>
</body>

</html>
