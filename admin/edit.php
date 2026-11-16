<?php
// =================================================================
// Reusable Header
// =================================================================
session_start();
include '../db.php'; 
include '../functions.php';

if (!isset($_SESSION['ADMIN_LOGIN'])) {
    header('location:login.php');
    die();
}
// =================================================================

$msg = ""; 
$err = "";
$tracking_data = [];

if (isset($_GET['edit'])) {
  $edit_id = mysqli_real_escape_string($con, $_GET['edit']);

  $sql = mysqli_query($con, "SELECT * FROM addtracking WHERE tracking_id = '$edit_id'");
  if (mysqli_num_rows($sql) > 0) {
    $tracking_data = mysqli_fetch_assoc($sql);
  } else {
    $err = "Shipment with this Tracking ID not found.";
  }
} else {
    // Redirect if no edit ID is provided, or handle appropriately
    header('location: dashboard.php');
    die();
}

if (isset($_POST['update'])) {
  // Sanitize and validate inputs
  $current_location = text_input($_POST['current_location'] ?? '');
  $date = text_input($_POST['date'] ?? '');
  $time = text_input($_POST['time'] ?? '');
  $status = text_input($_POST['status'] ?? '');
  $invoice_sub_total = filter_var($_POST['invoice_sub_total'] ?? 0, FILTER_VALIDATE_FLOAT);
  $discount = filter_var($_POST['discount'] ?? 0, FILTER_VALIDATE_FLOAT);
  $tax = filter_var($_POST['tax'] ?? 0, FILTER_VALIDATE_FLOAT);
  $invoice_total = $invoice_sub_total - $discount + $tax;
  $note = text_input($_POST['message'] ?? '');
  
  $receiver_name = $tracking_data['receiver_name'];
  $receiver_email = $tracking_data['receiver_email'];
  $tracking_id = $tracking_data['tracking_id'];

  if (empty($current_location) || empty($date) || empty($time) || empty($status) || empty($note)) {
    $err = "Please fill all required fields.";
  } else {
    // Use prepared statements to prevent SQL injection
    $stmt_insert = $con->prepare("INSERT INTO track_update (track_num, status, date, time, note, current_location, invoice_sub_total, discount, tax, invoice_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("ssssssddds", $edit_id, $status, $date, $time, $note, $current_location, $invoice_sub_total, $discount, $tax, $invoice_total);
    
    if ($stmt_insert->execute()) {
        $stmt_update = $con->prepare("UPDATE addtracking SET current_location = ?, status = ? WHERE tracking_id = ?");
        $stmt_update->bind_param("sss", $current_location, $status, $edit_id);
        $stmt_update->execute();
        $stmt_update->close();
        
        // Send email notification
        $settings_sql = mysqli_query($con, "SELECT site_url, sitename FROM setting LIMIT 1");
        $settings = mysqli_fetch_assoc($settings_sql);
        $site_url = $settings['site_url'] ?? 'http://yourwebsite.com';
        $sitename = $settings['sitename'] ?? 'Universal Union Logistics';
        
        $subject = "Shipment Update - " . $sitename;
        $body = "<p>Dear ".htmlspecialchars($receiver_name).",</p>
                 <p>An update on your shipment with tracking number <strong>".htmlspecialchars($tracking_id)."</strong>:</p>
                 <p><strong>New Status:</strong> ".htmlspecialchars($status)."</p>
                 <p><strong>Current Location:</strong> ".htmlspecialchars($current_location)."</p>
                 <p><strong>Note:</strong> ".nl2br(htmlspecialchars($note))."</p>
                 <p>For more details, please visit our <a href='".$site_url."/track.html'>Tracking Page</a>.</p>";
        sendMail($receiver_email, $subject, $body);

        $msg = "Shipment updated successfully!";
    } else {
        $err = "Database error: Could not update shipment.";
    }
    $stmt_insert->close();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Shipment - Universal Union Logistics Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800&family=Yantramanav:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        :root {
            --bg-dark-primary: #111827;
            --bg-dark-secondary: #1F2937;
            --accent-primary: #0284c7;
            --text-primary: #F3F4F6;
            --text-secondary: #9CA3AF;
        }
        body {
            font-family: 'Yantramanav', sans-serif;
        h1, h2, h3, h4, h5, h6 { font-family: 'Lexend', sans-serif; }
            background-color: var(--bg-dark-primary);
            color: var(--text-primary);
        }
        .form-input {
            background-color: #374151;
            border-color: #4B5563;
        }
        .form-input:focus {
            --tw-ring-color: var(--accent-primary);
            border-color: var(--accent-primary);
        }
        /* For mobile sidebar overlay */
        #sidebar-overlay { display: none; position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); z-index: 20; }
    </style>
</head>
<body class="flex bg-gray-900 text-gray-100">

    <div id="sidebar-overlay" class="md:hidden"></div>
    <aside id="sidebar" class="w-64 bg-gray-800 shadow-md min-h-screen flex-col fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out z-30">
        <div class="p-6 flex items-center space-x-2 border-b border-gray-700">
             <svg class="h-10 w-10 text-sky-400 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5-10-5-10 5z"/></svg>
            <span class="text-xl font-extrabold text-white">Universal Union Logistics</span>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-2">
            <a href="dashboard.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-sky-600 hover:text-white rounded-md transition-colors duration-200">
                <i data-feather="home" class="h-5 w-5 mr-3"></i> Dashboard
            </a>
            <a href="add-tracking.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-sky-600 hover:text-white rounded-md transition-colors duration-200">
                <i data-feather="plus-circle" class="h-5 w-5 mr-3"></i> Add Shipment
            </a>
             <a href="#" class="flex items-center px-4 py-2 text-gray-300 hover:bg-sky-600 hover:text-white rounded-md transition-colors duration-200">
                <i data-feather="settings" class="h-5 w-5 mr-3"></i> Settings
            </a>
        </nav>
        <div class="p-4 border-t border-gray-700">
             <a href="logout.php" class="flex items-center px-4 py-2 text-gray-300 hover:bg-red-600/50 hover:text-white rounded-md transition-colors duration-200">
                <i data-feather="log-out" class="h-5 w-5 mr-3"></i> Logout
            </a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col">
        <header class="bg-gray-800 shadow-md p-4 flex justify-between items-center">
            <button id="menu-btn" class="md:hidden p-2 rounded-md hover:bg-gray-700 text-white">
                <i data-feather="menu" class="h-6 w-6"></i>
            </button>
            <h1 class="text-2xl font-bold text-white">Update Shipment Status</h1>
            <div class="flex items-center space-x-4">
                 <span class="text-white hidden sm:block">Welcome, <?php echo htmlspecialchars($_SESSION['ADMIN_LOGIN']); ?></span>
            </div>
        </header>
        <main class="flex-1 p-4 sm:p-6">
            <div class="bg-gray-800 p-6 rounded-2xl shadow-lg mb-6">
                <h3 class="text-lg font-semibold text-white">Updating Tracking Number</h3>
                <p class="text-2xl mt-1 text-sky-400 font-mono"><?php echo htmlspecialchars($tracking_data['tracking_id'] ?? 'N/A'); ?></p>
            </div>
            
            <?php if ($msg != ""): ?>
                <div class="bg-green-500/20 text-green-300 p-4 rounded-lg mb-6 text-center"><?php echo $msg; ?></div>
            <?php endif; ?>
            <?php if ($err != ""): ?>
                <div class="bg-red-500/20 text-red-300 p-4 rounded-lg mb-6 text-center"><?php echo $err; ?></div>
            <?php endif; ?>

            <?php if(!empty($tracking_data)): ?>
            <form action="edit.php?edit=<?php echo htmlspecialchars($edit_id); ?>" method="POST">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Update Form -->
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg space-y-4">
                        <h3 class="text-xl font-bold text-white border-b border-gray-700 pb-2">New Update Details</h3>
                        <div>
                            <label class="text-sm text-gray-400">Current Location</label>
                            <input type="text" name="current_location" class="w-full mt-1 p-2 form-input rounded-md" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-400">Date</label>
                                <input type="date" name="date" class="w-full mt-1 p-2 form-input rounded-md" required>
                            </div>
                            <div>
                                <label class="text-sm text-gray-400">Time</label>
                                <input type="time" name="time" class="w-full mt-1 p-2 form-input rounded-md" required>
                            </div>
                        </div>
                         <div>
                            <label class="text-sm text-gray-400">Shipment Status</label>
                            <select name="status" class="w-full mt-1 p-2 form-input rounded-md">
                                <option <?php if($tracking_data['status'] == 'Pending') echo 'selected'; ?>>Pending</option>
                                <option <?php if($tracking_data['status'] == 'Active') echo 'selected'; ?>>Active</option>
                                <option <?php if($tracking_data['status'] == 'In Transit') echo 'selected'; ?>>In Transit</option>
                                <option <?php if($tracking_data['status'] == 'On Hold') echo 'selected'; ?>>On Hold</option>
                                <option <?php if($tracking_data['status'] == 'Awaiting Delivery') echo 'selected'; ?>>Awaiting Delivery</option>
                                <option <?php if($tracking_data['status'] == 'Delivered') echo 'selected'; ?>>Delivered</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-gray-400">Note / Update Message</label>
                            <textarea name="message" class="w-full mt-1 p-2 form-input rounded-md" rows="4" required></textarea>
                        </div>
                    </div>

                    <!-- Financial Details -->
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg space-y-4">
                        <h3 class="text-xl font-bold text-white border-b border-gray-700 pb-2">Financials (for Invoice)</h3>
                         <div>
                            <label class="text-sm text-gray-400">Invoice Subtotal</label>
                            <input type="text" name="invoice_sub_total" placeholder="0.00" class="w-full mt-1 p-2 form-input rounded-md">
                        </div>
                        <div>
                            <label class="text-sm text-gray-400">Discount</label>
                            <input type="text" name="discount" placeholder="0.00" class="w-full mt-1 p-2 form-input rounded-md">
                        </div>
                         <div>
                            <label class="text-sm text-gray-400">Tax</label>
                            <input type="text" name="tax" placeholder="0.00" class="w-full mt-1 p-2 form-input rounded-md">
                        </div>
                         <div class="pt-4 mt-4 border-t border-gray-700">
                             <a href="view-update.php?edit=<?php echo urlencode($edit_id); ?>" class="w-full text-center bg-gray-700 text-white font-semibold py-3 px-8 rounded-lg hover:bg-gray-600 transition-all duration-300">
                                View Previous Updates
                            </a>
                         </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" name="update" class="bg-sky-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-all duration-300 shadow-lg hover:shadow-sky-500/50">
                        Add Update
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </main>
    </div>
</div>
<script>
    feather.replace();
    // Mobile menu toggle
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.style.display = sidebar.classList.contains('-translate-x-full') ? 'none' : 'block';
    });
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.style.display = 'none';
    });
</script>
</body>
</html>
