<?php
include '../db.php'; 
include '../functions.php';

$tracking_data = [];
$invoice_data = [];
$settings_data = [];
$error_message = '';

if (isset($_GET['num']) && $_GET['num'] != "") {
    $post_id = mysqli_real_escape_string($con, $_GET['num']);

    // Fetch main tracking details
    $sql_tracking = mysqli_query($con, "SELECT * FROM addtracking WHERE tracking_id = '$post_id'");
    if (mysqli_num_rows($sql_tracking) > 0) {
        $tracking_data = mysqli_fetch_assoc($sql_tracking);
    } else {
        $error_message = "Tracking information not found.";
    }

    // Fetch financial details from track_update table
    $sql_invoice = mysqli_query($con, "SELECT * FROM track_update WHERE track_num = '$post_id' ORDER BY id DESC LIMIT 1");
    if (mysqli_num_rows($sql_invoice) > 0) {
        $invoice_data = mysqli_fetch_assoc($sql_invoice);
    }

    // Fetch general site settings
    $sql_settings = mysqli_query($con, "SELECT * FROM setting ORDER BY id DESC LIMIT 1");
    if (mysqli_num_rows($sql_settings) > 0) {
        $settings_data = mysqli_fetch_assoc($sql_settings);
    }
} else {
    $error_message = "No tracking number provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Waybill - <?php echo htmlspecialchars($tracking_data['tracking_id'] ?? 'Consignment'); ?> - Universal Union Logistics</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800&family=Yantramanav:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0284c7;
            --secondary: #0f172a;
            --text-main: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --bg-light: #f8fafc;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Yantramanav', sans-serif;
            background-color: #f1f5f9;
            color: var(--text-body);
            padding: 2rem 1rem;
            line-height: 1.5;
        }
        .waybill-sheet {
            max-width: 8.5in;
            min-height: 11in;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 3rem;
            border-radius: 8px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
            position: relative;
        }
        .header-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        .brand-title {
            font-family: 'Lexend', sans-serif;
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--secondary);
        }
        .brand-title span { color: var(--primary); }
        .meta-title {
            font-family: 'Lexend', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .info-card {
            background-color: var(--bg-light);
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 1.25rem;
        }
        .info-card-title {
            font-family: 'Lexend', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--primary);
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.5rem;
            margin-bottom: 0.75rem;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        .data-table th, .data-table td {
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            text-align: left;
            font-size: 0.95rem;
        }
        .data-table th {
            background-color: var(--bg-light);
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            color: var(--secondary);
        }
        .stamp-badge {
            display: inline-block;
            border: 2px dashed #0284c7;
            border-radius: 50%;
            width: 110px;
            height: 110px;
            text-align: center;
            padding: 1.5rem 0.5rem;
            color: #0284c7;
            font-family: 'Lexend', sans-serif;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            transform: rotate(-12deg);
        }
        .no-print-bar {
            text-align: center;
            margin-top: 1.5rem;
        }
        .btn-print {
            padding: 0.75rem 2rem;
            background-color: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            font-family: 'Lexend', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }
        .btn-print:hover { background-color: #0369a1; }
        @media print {
            body { background-color: #ffffff; padding: 0; }
            .waybill-sheet { box-shadow: none; border-radius: 0; padding: 2rem; }
            .no-print-bar { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="waybill-sheet">
        <?php if ($error_message): ?>
            <div style="text-align: center; padding: 4rem 1rem; color: #ef4444; font-family: 'Lexend', sans-serif;">
                <h2>Error</h2>
                <p><?php echo htmlspecialchars($error_message); ?></p>
            </div>
        <?php else: ?>
            <!-- Header -->
            <div class="header-grid">
                <div>
                    <div style="margin-bottom: 0.5rem;">
                        <img src="../assets/img/logo.png" alt="Universal Union Logistics" style="height: 54px; width: auto; display: block;">
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">
                        24-26 Minories, London EC3N 1BQ, United Kingdom<br>
                        Tel/WhatsApp: +44 7594 694996 | Telegram: @universal_union545 | Email: <?php echo htmlspecialchars($settings_data['email_address'] ?? 'contact@universalunionlogistics.com'); ?><br>
                        Web: <?php echo htmlspecialchars($settings_data['site_url'] ?? 'https://universalunionlogistics.com'); ?>
                    </p>
                </div>
                <div>
                    <div class="meta-title">Official Waybill</div>
                    <p style="text-align: right; font-size: 0.95rem; color: var(--text-muted);">
                        Tracking #: <strong style="color: var(--secondary);"><?php echo htmlspecialchars($tracking_data['tracking_id'] ?? ''); ?></strong><br>
                        Issue Date: <?php echo date("F d, Y"); ?>
                    </p>
                </div>
            </div>

            <!-- Sender & Receiver Grid -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-card-title">Shipper / Sender Information</div>
                    <div style="font-family: 'Lexend', sans-serif; font-weight: 700; color: var(--secondary); margin-bottom: 0.35rem;">
                        <?php echo htmlspecialchars($tracking_data['sender_name'] ?? ''); ?>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--text-body);">
                        Address: <?php echo htmlspecialchars($tracking_data['sender_address'] ?? ''); ?><br>
                        Email: <?php echo htmlspecialchars($tracking_data['sender_email'] ?? 'N/A'); ?><br>
                        Contact: <?php echo htmlspecialchars($tracking_data['sender_contact'] ?? ''); ?>
                    </p>
                </div>

                <div class="info-card">
                    <div class="info-card-title">Consignee / Receiver Information</div>
                    <div style="font-family: 'Lexend', sans-serif; font-weight: 700; color: var(--secondary); margin-bottom: 0.35rem;">
                        <?php echo htmlspecialchars($tracking_data['receiver_name'] ?? ''); ?>
                    </div>
                    <p style="font-size: 0.9rem; color: var(--text-body);">
                        Address: <?php echo htmlspecialchars($tracking_data['receiver_address'] ?? ''); ?><br>
                        Destination: <?php echo htmlspecialchars($tracking_data['destination'] ?? ''); ?><br>
                        Email: <?php echo htmlspecialchars($tracking_data['receiver_email'] ?? 'N/A'); ?><br>
                        Contact: <?php echo htmlspecialchars($tracking_data['receiver_contact'] ?? ''); ?>
                    </p>
                </div>
            </div>

            <!-- Shipment Specifications Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Shipment Mode</th>
                        <th>Assigned Carrier</th>
                        <th>Gross Weight</th>
                        <th>Quantity</th>
                        <th>Dispatch Date</th>
                        <th>Est. Delivery</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-transform: capitalize; font-weight: 600;"><?php echo htmlspecialchars($tracking_data['shipment_mode'] ?? 'Express'); ?></td>
                        <td><?php echo htmlspecialchars($tracking_data['carrier'] ?? 'DHL'); ?></td>
                        <td><?php echo htmlspecialchars($tracking_data['weight'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($tracking_data['quantity'] ?? '1'); ?></td>
                        <td><?php echo htmlspecialchars($tracking_data['dispach_date'] ?? 'N/A'); ?></td>
                        <td style="color: var(--primary); font-weight: 700;"><?php echo htmlspecialchars($tracking_data['estimated_delivery_date'] ?? 'N/A'); ?></td>
                    </tr>
                    <tr>
                        <td colspan="6" style="background-color: var(--bg-light); padding: 1.25rem;">
                            <div style="display: flex; gap: 1.5rem; align-items: center;">
                                <?php if (!empty($tracking_data['image'])): ?>
                                    <div style="width: 100px; height: 100px; border-radius: 6px; overflow: hidden; border: 1px solid var(--border); flex-shrink: 0; background-color: #ffffff;">
                                        <img src="../uploads/<?php echo htmlspecialchars($tracking_data['image']); ?>" alt="Package Item" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                <?php endif; ?>
                                <div style="flex: 1;">
                                    <div style="font-size: 1rem; font-weight: 700; color: var(--secondary); margin-bottom: 0.35rem;">
                                        Cargo: <?php echo htmlspecialchars($tracking_data['package_discription'] ?? 'Commercial freight parcel'); ?>
                                    </div>
                                    <div style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.5;">
                                        Payment Mode: <strong><?php echo htmlspecialchars($tracking_data['payment_mode'] ?? 'Prepaid'); ?></strong> &bull; 
                                        Carrier Ref: <strong><?php echo htmlspecialchars($tracking_data['carrier_refrence_number'] ?? 'N/A'); ?></strong><br>
                                        Origin Location: <strong><?php echo htmlspecialchars($tracking_data['dispatch_location'] ?? 'N/A'); ?></strong> &bull; 
                                        Final Destination: <strong><?php echo htmlspecialchars($tracking_data['destination'] ?? 'N/A'); ?></strong>
                                    </div>
                                    <?php if (!empty($tracking_data['delivery_message']) || !empty($tracking_data['message'])): ?>
                                        <div style="margin-top: 0.35rem; font-size: 0.82rem; color: var(--primary);">
                                            <strong>Special Handling:</strong> <?php echo htmlspecialchars($tracking_data['delivery_message'] ?: $tracking_data['message']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Financial Charges Table -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tariff Description</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Freight Handling Subtotal</td>
                        <td style="text-align: right;"><?php echo htmlspecialchars($invoice_data['invoice_sub_total'] ?? '$0.00'); ?></td>
                    </tr>
                    <tr>
                        <td>Discount Applied</td>
                        <td style="text-align: right;"><?php echo htmlspecialchars($invoice_data['discount'] ?? '$0.00'); ?></td>
                    </tr>
                    <tr>
                        <td>Customs & Port Duty / Tax</td>
                        <td style="text-align: right;"><?php echo htmlspecialchars($invoice_data['tax'] ?? '$0.00'); ?></td>
                    </tr>
                    <tr style="background-color: var(--bg-light); font-weight: 800; font-family: 'Lexend', sans-serif;">
                        <td style="font-size: 1.1rem; color: var(--secondary);">Total Invoice Due</td>
                        <td style="text-align: right; font-size: 1.15rem; color: var(--primary);"><?php echo htmlspecialchars($invoice_data['invoice_total'] ?? '$0.00'); ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Signature & Validation Stamp -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 3.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                <div>
                    <div style="font-family: 'Brush Script MT', cursive, sans-serif; font-size: 2.2rem; color: var(--secondary);">
                        Universal Union Logistics Dispatch
                    </div>
                    <div style="border-top: 1px solid var(--text-muted); width: 200px; margin-top: 0.25rem; padding-top: 0.35rem; font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">
                        Authorized Cargo Officer
                    </div>
                </div>

                <div class="stamp-badge">
                    Official Carrier<br>Verified<br>Security Seal
                </div>
            </div>

            <!-- Footer -->
            <div style="margin-top: 3rem; text-align: center; font-size: 0.82rem; color: var(--text-muted); border-top: 1px solid var(--border); padding-top: 1rem;">
                Thank you for choosing <?php echo htmlspecialchars($settings_data['sitename'] ?? 'Universal Union Logistics'); ?>. This document serves as legal proof of dispatch and transport under international carriage agreements.
            </div>
        <?php endif; ?>
    </div>

    <div class="no-print-bar">
        <button onclick="window.print()" class="btn-print">
            Print Waybill / Invoice
        </button>
    </div>
</body>
</html>
