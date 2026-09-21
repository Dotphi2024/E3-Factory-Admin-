<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - INV-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-light: #e0e7ff;
            --text-dark: #1f2937;
            --text-muted: #4b5563;
            --bg-light: #f9fafb;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --success-light: #d1fae5;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: var(--text-dark);
            background-color: #f3f4f6;
            line-height: 1.5;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .invoice-container {
            background-color: #ffffff;
            width: 100%;
            max-width: 800px;
            padding: 50px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            position: relative;
            border: 1px solid var(--border-color);
        }

        .no-print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
            width: 100%;
            margin: 0 auto 20px auto;
            background: #ffffff;
            padding: 15px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #4338ca;
        }

        .btn-secondary {
            background-color: var(--bg-light);
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: #f3f4f6;
        }

        /* Invoice Header */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 30px;
            margin-bottom: 30px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-circle {
            width: 48px;
            height: 48px;
            background-color: var(--primary-color);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .company-tagline {
            font-size: 12px;
            color: var(--text-muted);
        }

        .invoice-title-section {
            text-align: right;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary-color);
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }

        .invoice-meta {
            margin-top: 5px;
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 45px;
        }

        .info-block-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 12px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 6px;
        }

        .info-content {
            font-size: 14px;
        }

        .info-content p {
            margin-bottom: 4px;
        }

        .info-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        /* Invoice Details Table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .invoice-table th {
            background-color: var(--bg-light);
            font-weight: 600;
            text-align: left;
            padding: 12px 16px;
            font-size: 12px;
            text-transform: uppercase;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border-color);
        }

        .invoice-table td {
            padding: 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--border-color);
        }

        .invoice-table tr:last-child td {
            border-bottom: 2px solid var(--border-color);
        }

        .item-description {
            font-weight: 500;
            color: var(--text-dark);
        }

        .item-subtext {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .text-right {
            text-align: right !important;
        }

        /* Invoice Summary */
        .invoice-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stamp-container {
            display: flex;
            align-items: center;
        }

        .paid-stamp {
            border: 3px double var(--success-color);
            color: var(--success-color);
            background-color: var(--success-light);
            font-size: 20px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 6px 20px;
            border-radius: 8px;
            transform: rotate(-10deg);
            display: inline-block;
            letter-spacing: 2px;
            opacity: 0.85;
            box-shadow: 0 0 0 3px var(--success-light);
        }

        .totals-table {
            width: 300px;
            font-size: 14px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed var(--border-color);
        }

        .totals-row:last-child {
            border-bottom: none;
        }

        .totals-row.grand-total {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
            border-top: 2px solid var(--primary-color);
            border-bottom: none;
            padding: 12px 0;
            margin-top: 4px;
        }

        /* Footer */
        .invoice-footer {
            margin-top: 60px;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Print styling */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .no-print, .no-print-bar {
                display: none !important;
            }

            .invoice-container {
                box-shadow: none;
                border: none;
                padding: 0;
                margin: 0;
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
        <!-- Print / Back Toolbar -->
        <div class="no-print-bar">
           
            <button onclick="window.print();" class="btn btn-primary">
                Print / Save PDF
            </button>
        </div>

        <!-- Invoice -->
        <div class="invoice-container">
            <!-- Header -->
            <div class="invoice-header">
                <div class="logo-section">
                    <div class="logo-circle">E3</div>
                    <div>
                        <div class="company-name">E3 Factory</div>
                        <div class="company-tagline">Empowerment • Education • Excellence</div>
                    </div>
                </div>
                <div class="invoice-title-section">
                    <h1 class="invoice-title">Receipt</h1>
                    <div class="invoice-meta">
                        <p><strong>Receipt #:</strong> REC-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</p>
                        <p><strong>Date:</strong> {{ $payment->created_at->format('d-M-Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Billing Grid -->
            <div class="info-grid">
                <div>
                    <h2 class="info-block-title">Received From (Participant)</h2>
                    <div class="info-content">
                        <div class="info-name">{{ $payment->participant->first_name }} {{ $payment->participant->last_name }}</div>
                        @if($payment->participant->email)
                            <p><strong>Email:</strong> {{ $payment->participant->email }}</p>
                        @endif
                        <p><strong>Mobile:</strong> {{ $payment->participant->mobile }}</p>
                        @if($payment->participant->address || $payment->participant->city)
                            <p><strong>Address:</strong> {{ $payment->participant->address }} {{ $payment->participant->city }} {{ $payment->participant->state }}</p>
                        @endif
                    </div>
                </div>
                <div>
                    <h2 class="info-block-title">Payment Information</h2>
                    <div class="info-content">
                        <p><strong>Payment Mode:</strong> {{ $payment->payment_mode }}</p>
                        <p><strong>Transaction ID:</strong> {{ $payment->transaction_id }}</p>
                        @if($payment->batch)
                            <p><strong>Batch:</strong> {{ $payment->batch->name }}</p>
                            @if($payment->batch->course)
                                <p><strong>Program:</strong> {{ $payment->batch->course->name }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Table -->
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">No</th>
                        <th>Item & Description</th>
                        <th class="text-right" style="width: 150px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <span class="item-description">
                                @if($payment->payment_for == 'registration')
                                    Registration Fee
                                @else
                                    Session Fee (Session #{{ $payment->session_number }})
                                @endif
                            </span>
                            @if($payment->batch)
                                <div class="item-subtext">Batch: {{ $payment->batch->name }}</div>
                            @endif
                        </td>
                        <td class="text-right font-weight-bold">
                            ₹{{ number_format($payment->amount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Summary -->
            <div class="invoice-summary">
                <div class="stamp-container">
                    <div class="paid-stamp">Paid</div>
                </div>
                <div class="totals-table">
                    <div class="totals-row">
                        <span>Subtotal:</span>
                        <span>₹{{ number_format($payment->amount, 2) }}</span>
                    </div>
                    <div class="totals-row">
                        <span>Tax / GST (0%):</span>
                        <span>₹0.00</span>
                    </div>
                    <div class="totals-row grand-total">
                        <span>Total Paid:</span>
                        <span>₹{{ number_format($payment->amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="invoice-footer">
                <p>This is a computer-generated payment receipt and does not require a physical signature.</p>
                <p style="margin-top: 5px;">Thank you for your learning journey with E3 Academy!</p>
            </div>
        </div>
    </div>

    <!-- Auto-print script -->
    <script>
        window.onload = function() {
            // Wait slightly for fonts and styles to render before triggering print dialog
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
