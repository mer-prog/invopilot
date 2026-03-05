<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            line-height: 1.5;
        }
        .container {
            padding: 40px;
        }
        .header {
            display: flex;
            margin-bottom: 30px;
        }
        .org-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 50%;
            text-align: right;
        }
        .org-name {
            font-size: 20px;
            font-weight: bold;
            color: #111;
            margin-bottom: 6px;
        }
        .org-details {
            color: #666;
            font-size: 10px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #111;
            margin-bottom: 8px;
        }
        .invoice-meta {
            font-size: 10px;
            color: #666;
        }
        .invoice-meta td {
            padding: 2px 0;
        }
        .invoice-meta .label {
            text-align: right;
            padding-right: 10px;
            color: #888;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .bill-to {
            margin: 30px 0 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .bill-to-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 6px;
        }
        .bill-to-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .bill-to-details {
            color: #666;
            font-size: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background: #f3f4f6;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #666;
            border-bottom: 2px solid #e5e7eb;
        }
        .items-table th.right {
            text-align: right;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 11px;
        }
        .items-table td.right {
            text-align: right;
        }
        .totals {
            float: right;
            width: 250px;
            margin-top: 10px;
        }
        .totals table {
            width: 100%;
        }
        .totals td {
            padding: 5px 0;
            font-size: 11px;
        }
        .totals .label {
            color: #666;
        }
        .totals .value {
            text-align: right;
        }
        .totals .total-row td {
            border-top: 2px solid #111;
            padding-top: 8px;
            font-size: 14px;
            font-weight: bold;
        }
        .notes {
            clear: both;
            margin-top: 40px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        .notes-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 6px;
        }
        .notes-content {
            font-size: 10px;
            color: #666;
            white-space: pre-line;
        }
        .footer-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #888;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-overdue { background: #fee2e2; color: #b91c1c; }
        .status-cancelled { background: #f3f4f6; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <div class="org-info">
                <div class="org-name">{{ $organization->name }}</div>
                <div class="org-details">
                    @if($organization->address)
                        {{ $organization->address }}<br>
                    @endif
                    @if($organization->phone)
                        {{ $organization->phone }}<br>
                    @endif
                    @if($organization->tax_id)
                        @lang('clients.tax_id'): {{ $organization->tax_id }}
                    @endif
                </div>
            </div>
            <div class="invoice-info">
                <div class="invoice-title">@lang('invoices.singular')</div>
                <table class="invoice-meta" style="margin-left: auto;">
                    <tr>
                        <td class="label">@lang('invoices.invoice_number'):</td>
                        <td>{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">@lang('invoices.issue_date'):</td>
                        <td>{{ $invoice->issue_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td class="label">@lang('invoices.due_date'):</td>
                        <td>{{ $invoice->due_date->format('Y-m-d') }}</td>
                    </tr>
                    <tr>
                        <td class="label">@lang('common.status'):</td>
                        <td>
                            <span class="status-badge status-{{ $invoice->status->value }}">
                                @lang('invoices.status_' . $invoice->status->value)
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($client)
            <div class="bill-to">
                <div class="bill-to-label">@lang('invoices.bill_to')</div>
                <div class="bill-to-name">{{ $client->name }}</div>
                <div class="bill-to-details">
                    @if($client->company)
                        {{ $client->company }}<br>
                    @endif
                    @if($client->email)
                        {{ $client->email }}<br>
                    @endif
                    @if($client->address)
                        {{ $client->address }}<br>
                    @endif
                    @if($client->tax_id)
                        @lang('clients.tax_id'): {{ $client->tax_id }}
                    @endif
                </div>
            </div>
        @endif

        <table class="items-table">
            <thead>
                <tr>
                    <th>@lang('common.description')</th>
                    <th class="right">@lang('common.quantity')</th>
                    <th class="right">@lang('common.unit_price')</th>
                    <th class="right">@lang('common.tax_rate')</th>
                    <th class="right">@lang('common.amount')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                        <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="right">{{ $item->tax_rate }}%</td>
                        <td class="right">{{ number_format((float) $item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td class="label">@lang('common.subtotal')</td>
                    <td class="value">{{ number_format((float) $invoice->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">@lang('common.tax')</td>
                    <td class="value">{{ number_format((float) $invoice->tax_amount, 2) }}</td>
                </tr>
                @if((float) $invoice->discount_amount > 0)
                    <tr>
                        <td class="label">@lang('common.discount')</td>
                        <td class="value">-{{ number_format((float) $invoice->discount_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>@lang('common.total') ({{ $invoice->currency->value }})</td>
                    <td class="value">{{ number_format((float) $invoice->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        @if($invoice->notes)
            <div class="notes">
                <div class="notes-label">@lang('common.notes')</div>
                <div class="notes-content">{{ $invoice->notes }}</div>
            </div>
        @endif

        @if($invoice->footer)
            <div class="footer-section">
                {{ $invoice->footer }}
            </div>
        @endif
    </div>
</body>
</html>
