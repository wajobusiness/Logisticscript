<?php
/**
 * Universal Union Logistics - Automated Shipment Creation & Verification Test
 * 
 * This script tests:
 * 1. Database connectivity ($con in db.php)
 * 2. Directory writability for uploads/ folder (attempts auto-chmod 0777)
 * 3. File upload / creation simulation in uploads/
 * 4. Insertion of a full test shipment into `addtracking` with 'UUL' tracking prefix
 * 5. Retrieval verification from the database
 * 
 * Works in both Web Browser and Terminal (CLI).
 */

$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    header('Content-Type: text/html; charset=utf-8');
}

function out($msg, $type = 'info') {
    global $is_cli;
    if ($is_cli) {
        $prefix = ($type === 'success') ? '[SUCCESS] ' : (($type === 'error') ? '[ERROR] ' : '[INFO] ');
        echo $prefix . strip_tags($msg) . "\n";
    }
}

$results = [];
$errors = [];

// 1. Connect to Database
$db_file = __DIR__ . '/db.php';
if (!file_exists($db_file)) {
    $errors[] = "db.php file not found at " . $db_file;
} else {
    include $db_file;
    if (!isset($con) || $con === false || mysqli_connect_errno()) {
        $errors[] = "Database connection failed: " . mysqli_connect_error();
    } else {
        $results['database'] = "Connected successfully to database '" . htmlspecialchars($db_name ?? 'cargo') . "'";
    }
}

// 2. Test uploads/ Directory
$uploads_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

if (!file_exists($uploads_dir)) {
    @mkdir($uploads_dir, 0777, true);
}
@chmod($uploads_dir, 0777);

if (!is_dir($uploads_dir)) {
    $errors[] = "Uploads directory could not be created at: " . $uploads_dir;
} elseif (!is_writable($uploads_dir)) {
    @chmod($uploads_dir, 0777);
    if (!is_writable($uploads_dir)) {
        $errors[] = "Uploads directory exists but is NOT writable by web server. Run command: chmod -R 777 " . $uploads_dir;
    } else {
        $results['uploads_dir'] = "Uploads folder (" . $uploads_dir . ") is WRITABLE (permissions updated to 0777).";
    }
} else {
    $results['uploads_dir'] = "Uploads folder (" . $uploads_dir . ") is WRITABLE.";
}

// 3. Generate Test Package Image
$test_image_name = '';
if (empty($errors)) {
    // 1x1 transparent PNG binary
    $png_pixel = base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==");
    $test_image_name = 'UUL_TEST_' . time() . '.png';
    $test_image_path = $uploads_dir . $test_image_name;

    $wrote = @file_put_contents($test_image_path, $png_pixel);
    if ($wrote === false) {
        $errors[] = "Failed to write test package image to " . $test_image_path . ". Check folder write permissions.";
    } else {
        @chmod($test_image_path, 0644);
        $results['image_write'] = "Test package image saved: " . $test_image_name . " (" . $wrote . " bytes)";
    }
}

// 4. Create Test Shipment Record
$new_tracking_id = '';
if (empty($errors)) {
    $digits = str_shuffle("12345678900987654321");
    $rand_part = substr($digits, 0, 8);
    $new_tracking_id = "UUL" . date('y') . $rand_part;

    $sender_name = "Global Logistics Hub Ltd";
    $sender_contact = "+447594694996";
    $sender_email = "contact@universalunionlogistics.com";
    $sender_address = "71-75 Shelton Street, Covent Garden, London, WC2H 9JQ, UK";
    $status = "In Transit";
    $dispatch_location = "Heathrow Worldwide Distribution Centre, London";
    $carrier = "Universal Union Fleet";
    $carrier_refrence_number = "CRN-" . rand(100000, 999999);
    $weight = "8.5 kg";
    $payment_mode = "Prepaid";
    $packageImage = $test_image_name;
    $receiver_name = "Alexander Wright";
    $receiver_contact = "+1 (555) 349-8821";
    $receiver_email = "alex.wright@example.com";
    $receiver_address = "450 Lexington Avenue, 14th Floor, New York, NY 10017, USA";
    $destination = "New York, USA";
    $package_discription = "Automated System Test Package - High Priority Industrial Documents & Components";
    $dispach_date = date('Y-m-d');
    $estimated_delivery_date = date('Y-m-d', strtotime('+4 days'));
    $shipment_mode = "Express Air Freight";
    $quantity = "1";
    $delivery_time = "14:30";
    $date_added = date('Y-m-d H:i:s');

    $stmt = $con->prepare("INSERT INTO addtracking (tracking_id, sender_name, sender_contact, sender_email, sender_address, status, dispatch_location, carrier, carrier_refrence_number, weight, payment_mode, image, receiver_name, receiver_contact, receiver_email, receiver_address, destination, package_discription, dispach_date, estimated_delivery_date, shipment_mode, quantity, delivery_time, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        $errors[] = "Prepare statement failed: " . $con->error;
    } else {
        $stmt->bind_param("ssssssssssssssssssssssss", 
            $new_tracking_id, $sender_name, $sender_contact, $sender_email, $sender_address, 
            $status, $dispatch_location, $carrier, $carrier_refrence_number, $weight, 
            $payment_mode, $packageImage, $receiver_name, $receiver_contact, $receiver_email, 
            $receiver_address, $destination, $package_discription, $dispach_date, 
            $estimated_delivery_date, $shipment_mode, $quantity, $delivery_time, $date_added
        );

        if ($stmt->execute()) {
            $results['shipment_created'] = "Shipment created successfully! Tracking ID: " . $new_tracking_id;
            
            // Also insert an initial tracking checkpoint into track_update
            $checkpoint_stmt = $con->prepare("INSERT INTO track_update (track_num, status, date, time, note, current_location, invoice_sub_total, discount, tax, invoice_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($checkpoint_stmt) {
                $sub_total = 120.00;
                $disc = 0.00;
                $tax_amt = 15.00;
                $total_amt = 135.00;
                $note = "Package processed through automated test intake and sorted for transit.";
                $checkpoint_stmt->bind_param("ssssssddds", $new_tracking_id, $status, $dispach_date, $delivery_time, $note, $dispatch_location, $sub_total, $disc, $tax_amt, $total_amt);
                $checkpoint_stmt->execute();
                $checkpoint_stmt->close();
            }
        } else {
            $errors[] = "Shipment insertion failed: " . $stmt->error;
        }
        $stmt->close();
    }
}

// 5. Verification Query
$verified_shipment = null;
if (empty($errors) && !empty($new_tracking_id)) {
    $verify_sql = mysqli_query($con, "SELECT * FROM addtracking WHERE tracking_id = '" . mysqli_real_escape_string($con, $new_tracking_id) . "'");
    if ($verify_sql && mysqli_num_rows($verify_sql) > 0) {
        $verified_shipment = mysqli_fetch_assoc($verify_sql);
        $results['verification'] = "Database record verified: Found tracking record for " . $new_tracking_id;
    } else {
        $errors[] = "Shipment verification query returned no rows for tracking ID: " . $new_tracking_id;
    }
}

if ($is_cli): ?>
===================================================================
UNIVERSAL UNION LOGISTICS - SHIPMENT CREATION TEST
===================================================================
<?php if (!empty($errors)): ?>
ERRORS DETECTED:
<?php foreach ($errors as $err): ?>
  [X] <?php echo $err . "\n"; ?>
<?php endforeach; ?>
<?php else: ?>
ALL TESTS PASSED SUCCESSFULLY!
- <?php echo $results['database'] . "\n"; ?>
- <?php echo $results['uploads_dir'] . "\n"; ?>
- <?php echo $results['image_write'] . "\n"; ?>
- <?php echo $results['shipment_created'] . "\n"; ?>
- <?php echo $results['verification'] . "\n"; ?>

Tracking Number: <?php echo $new_tracking_id . "\n"; ?>
Sender:          <?php echo $verified_shipment['sender_name'] . "\n"; ?>
Receiver:        <?php echo $verified_shipment['receiver_name'] . "\n"; ?>
Status:          <?php echo $verified_shipment['status'] . "\n"; ?>
Image File:      <?php echo $verified_shipment['image'] . "\n"; ?>
Dispatch Date:   <?php echo $verified_shipment['dispach_date'] . "\n"; ?>
Est. Delivery:   <?php echo $verified_shipment['estimated_delivery_date'] . "\n"; ?>
===================================================================
<?php endif; ?>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Creation Test - Universal Union Logistics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700&family=Yantramanav:wght@400;500;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Yantramanav', sans-serif; } h1, h2, h3, .font-heading { font-family: 'Lexend', sans-serif; }</style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen p-4 sm:p-8 flex items-center justify-center">
    <div class="max-w-2xl w-full bg-gray-900 border border-gray-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
        <div class="flex items-center gap-3 pb-6 border-b border-gray-800">
            <div class="w-10 h-10 rounded-xl bg-sky-600/20 text-sky-400 flex items-center justify-center font-bold text-xl border border-sky-500/30">
                UUL
            </div>
            <div>
                <h1 class="text-xl font-bold text-white font-heading">Shipment Creation & Permission Test</h1>
                <p class="text-xs text-gray-400">Automated diagnostic and verification test</p>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="mt-6 p-4 bg-red-950/40 border border-red-700/60 rounded-xl text-red-200">
                <h3 class="font-bold text-red-400 font-heading mb-2">Test Failed:</h3>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-4 pt-3 border-t border-red-800/40 text-xs text-red-300">
                    <strong>Recommended Fix:</strong> Run <code class="bg-black/50 px-2 py-1 rounded text-white font-mono">chmod -R 777 uploads</code> in your cPanel terminal.
                </div>
            </div>
        <?php else: ?>
            <div class="mt-6 p-4 bg-emerald-950/40 border border-emerald-600/50 rounded-xl text-emerald-200">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h3 class="font-bold text-emerald-300 font-heading text-lg">Test Passed: Shipment Created Successfully!</h3>
                </div>
                <p class="text-sm text-emerald-200/90">
                    A real test shipment has been generated, package image created in <code class="bg-black/40 px-1.5 py-0.5 rounded font-mono text-emerald-300">uploads/</code>, and verified in the database.
                </p>
            </div>

            <div class="mt-6 bg-gray-950/80 border border-gray-800 rounded-xl p-5 space-y-3">
                <div class="flex justify-between items-center border-b border-gray-800/80 pb-2.5">
                    <span class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Tracking Number</span>
                    <span class="font-mono text-lg font-bold text-sky-400 bg-sky-950/60 px-3 py-1 rounded-md border border-sky-800/50">
                        <?php echo htmlspecialchars($new_tracking_id); ?>
                    </span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-400">Sender:</span>
                    <span class="text-gray-200 font-medium"><?php echo htmlspecialchars($verified_shipment['sender_name']); ?></span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-400">Receiver:</span>
                    <span class="text-gray-200 font-medium"><?php echo htmlspecialchars($verified_shipment['receiver_name']); ?></span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-400">Status:</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-600/20 text-sky-300 border border-sky-500/30">
                        <?php echo htmlspecialchars($verified_shipment['status']); ?>
                    </span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-400">Package Image:</span>
                    <span class="font-mono text-xs text-emerald-400"><?php echo htmlspecialchars($verified_shipment['image']); ?> (Saved in uploads/)</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-400">Dispatch Location:</span>
                    <span class="text-gray-300 text-xs"><?php echo htmlspecialchars($verified_shipment['dispatch_location']); ?></span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-400">Destination:</span>
                    <span class="text-gray-300 text-xs"><?php echo htmlspecialchars($verified_shipment['destination']); ?></span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="track/tracking.php?tracking_id=<?php echo urlencode($new_tracking_id); ?>" target="_blank" class="block text-center py-2.5 px-3 rounded-lg bg-sky-600 hover:bg-sky-500 text-white font-semibold text-xs sm:text-sm transition-all shadow-lg hover:shadow-sky-500/30">
                    Live Tracking Page
                </a>
                <a href="track/print.php?num=<?php echo urlencode($new_tracking_id); ?>" target="_blank" class="block text-center py-2.5 px-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 font-semibold text-xs sm:text-sm border border-gray-700 transition-all">
                    View & Print Invoice
                </a>
                <a href="admin/view-details.php?detail=<?php echo urlencode($new_tracking_id); ?>" target="_blank" class="block text-center py-2.5 px-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 font-semibold text-xs sm:text-sm border border-gray-700 transition-all">
                    Admin Panel
                </a>
            </div>
        <?php endif; ?>

        <div class="mt-6 pt-4 border-t border-gray-800/80 flex justify-between items-center text-xs text-gray-500">
            <span>Universal Union Logistics</span>
            <span>Security & Diagnostic Script</span>
        </div>
    </div>
</body>
</html>
