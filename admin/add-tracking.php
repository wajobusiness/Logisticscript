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

$error = "";
$msg = "";

// Generate a unique tracking number
$tnumbs = "12345678900987654321";
$tnumbs = str_shuffle($tnumbs);
$track_prefix = "WC"; // Using WC for Universal Union Logistics
$tnumbs = substr($tnumbs, 0, 8);
$tnumbs = $track_prefix . date('y') . $tnumbs;

$date_added = date('Y-m-d H:i:s');

if (isset($_POST['add'])) {
    // Sanitize and validate all inputs
    $sender_name = text_input($_POST['sendername'] ?? '');
    $sender_contact = text_input($_POST['sendercontact'] ?? '');
    $sender_email = text_input($_POST['senderemail'] ?? '');
    $sender_address = text_input($_POST['senderaddress'] ?? '');
    $status = text_input($_POST['status'] ?? 'Pending');
    $dispatch_location = text_input($_POST['dispatchlocation'] ?? '');
    $carrier = text_input($_POST['carrier'] ?? '');
    $carrier_refrence_number = text_input($_POST['carrierreferencenumber'] ?? '');
    $weight = text_input($_POST['weight'] ?? '');
    $payment_mode = text_input($_POST['paymentmode'] ?? '');
    $receiver_name = text_input($_POST['receivername'] ?? '');
    $receiver_email = text_input($_POST['recevieremail'] ?? '');
    $receiver_contact = text_input($_POST['receviercontact'] ?? '');
    $receiver_address = text_input($_POST['recevieraddress'] ?? '');
    $destination = text_input($_POST['destination'] ?? '');
    $package_discription = text_input($_POST['packagedescription'] ?? '');
    $dispach_date = text_input($_POST['dipatchdate'] ?? '');
    $estimated_delivery_date = text_input($_POST['estimateddeliverydate'] ?? '');
    $shipment_mode = text_input($_POST['shipmentmethod'] ?? '');
    $quantity = text_input($_POST['quantity'] ?? '');
    $delivery_time = text_input($_POST['deliverytime'] ?? '');

    // Basic validation
    if (empty($sender_name) || empty($receiver_name) || empty($package_discription)) {
        $error = "Please fill in all required fields (Sender Name, Receiver Name, Description).";
    }

    // Image Upload
    $packageImage = '';
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $extensions = array("jpeg", "jpg", "png");
        $location = "../uploads/";
        $filename = $_FILES["image"]["name"];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $extensions)) {
            $packageImage = $tnumbs . '.' . $file_ext;
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $location . $packageImage)) {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Please choose a JPEG, JPG, or PNG file.";
        }
    } else {
        $error = "Package image is required.";
    }

    if (empty($error)) {
        // Use prepared statements to prevent SQL injection
        $stmt = $con->prepare("INSERT INTO addtracking (tracking_id, sender_name, sender_contact, sender_email, sender_address, status, dispatch_location, carrier, carrier_refrence_number, weight, payment_mode, image, receiver_name, receiver_contact, receiver_email, receiver_address, destination, package_discription, dispach_date, estimated_delivery_date, shipment_mode, quantity, delivery_time, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssssssssssssss", $tnumbs, $sender_name, $sender_contact, $sender_email, $sender_address, $status, $dispatch_location, $carrier, $carrier_refrence_number, $weight, $payment_mode, $packageImage, $receiver_name, $receiver_contact, $receiver_email, $receiver_address, $destination, $package_discription, $dispach_date, $estimated_delivery_date, $shipment_mode, $quantity, $delivery_time, $date_added);
        
        if ($stmt->execute()) {
            // Send mail notification
            // Ensure you have a working sendMail function and site settings in the DB
            $settings_sql = mysqli_query($con, "SELECT site_url, sitename FROM setting LIMIT 1");
            $settings = mysqli_fetch_assoc($settings_sql);
            $site_url = $settings['site_url'] ?? 'http://yourwebsite.com';
            $sitename = $settings['sitename'] ?? 'Universal Union Logistics';
            
            $subject = "Shipment Registered - " . $sitename;
            $body = "<p>Dear ".htmlspecialchars($receiver_name).",</p> 
                     <p>We are pleased to inform you that your shipment has been registered with us.</p>
                     <p><strong>Tracking Number:</strong> " . htmlspecialchars($tnumbs) . "</p>
                     <p><strong>Status:</strong> " . htmlspecialchars($status) . "</p>
                     <p>For more information, please visit our <a href='" . $site_url . "/track.html'>Tracking Page</a>.</p>";
            sendMail($receiver_email, $subject, $body);

            $msg = "Shipment created successfully! Tracking Number: " . $tnumbs;
            // Clear variables for a new entry
            $_POST = array(); 
            
        } else {
            $error = "Database error: Failed to create shipment.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Shipment - Universal Union Logistics Admin</title>
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
            <a href="add-tracking.php" class="flex items-center px-4 py-2 bg-sky-600 text-white rounded-md transition-colors duration-200">
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
            <h1 class="text-2xl font-bold text-white">Add New Shipment</h1>
            <div class="flex items-center space-x-4">
                 <span class="text-white hidden sm:block">Welcome, <?php echo htmlspecialchars($_SESSION['ADMIN_LOGIN']); ?></span>
            </div>
        </header>
        <main class="flex-1 p-4 sm:p-6">
            <form action="add-tracking.php" method="POST" enctype="multipart/form-data">
                <div class="bg-gray-800 p-6 rounded-2xl shadow-lg mb-6">
                    <h3 class="text-lg font-semibold text-white">Tracking Number (Auto-Generated)</h3>
                    <input type="text" readonly value="<?php echo $tnumbs; ?>" class="w-full mt-2 p-3 bg-gray-900 text-sky-400 font-mono rounded-md border border-gray-700">
                </div>
                
                <?php if ($msg != ""): ?>
                    <div class="bg-green-500/20 text-green-300 p-4 rounded-lg mb-6 text-center"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if ($error != ""): ?>
                    <div class="bg-red-500/20 text-red-300 p-4 rounded-lg mb-6 text-center"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column: Sender and Shipment Info -->
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg space-y-4">
                        <h3 class="text-xl font-bold text-white border-b border-gray-700 pb-2">Sender & Shipment Info</h3>
                        <div><label class="text-sm text-gray-400">Sender Name</label><input type="text" name="sendername" class="w-full mt-1 p-2 form-input rounded-md" required></div>
                        <div><label class="text-sm text-gray-400">Sender Contact</label><input type="text" name="sendercontact" class="w-full mt-1 p-2 form-input rounded-md" required></div>
                        <div><label class="text-sm text-gray-400">Sender Email</label><input type="email" name="senderemail" class="w-full mt-1 p-2 form-input rounded-md" required></div>
                        <div><label class="text-sm text-gray-400">Sender Address</label><textarea name="senderaddress" class="w-full mt-1 p-2 form-input rounded-md" rows="2"></textarea></div>
                        
                        <div class="border-t border-gray-700 my-4"></div>
                        
                        <div><label class="text-sm text-gray-400">Dispatch Location</label><input type="text" name="dispatchlocation" class="w-full mt-1 p-2 form-input rounded-md"></div>
                        <div><label class="text-sm text-gray-400">Dispatch Date</label><input type="date" name="dipatchdate" class="w-full mt-1 p-2 form-input rounded-md"></div>
                        <div><label class="text-sm text-gray-400">Carrier</label><input type="text" name="carrier" class="w-full mt-1 p-2 form-input rounded-md"></div>
                        <div><label class="text-sm text-gray-400">Carrier Reference No.</label><input type="text" name="carrierreferencenumber" class="w-full mt-1 p-2 form-input rounded-md"></div>
                    </div>

                    <!-- Right Column: Receiver and Package Info -->
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg space-y-4">
                        <h3 class="text-xl font-bold text-white border-b border-gray-700 pb-2">Receiver & Package Info</h3>
                        <div><label class="text-sm text-gray-400">Receiver Name</label><input type="text" name="receivername" class="w-full mt-1 p-2 form-input rounded-md" required></div>
                        <div><label class="text-sm text-gray-400">Receiver Contact</label><input type="text" name="receviercontact" class="w-full mt-1 p-2 form-input rounded-md" required></div>
                        <div><label class="text-sm text-gray-400">Receiver Email</label><input type="email" name="recevieremail" class="w-full mt-1 p-2 form-input rounded-md" required></div>
                        <div><label class="text-sm text-gray-400">Receiver Address</label><textarea name="recevieraddress" class="w-full mt-1 p-2 form-input rounded-md" rows="2"></textarea></div>
                        <div><label class="text-sm text-gray-400">Final Destination</label><input type="text" name="destination" class="w-full mt-1 p-2 form-input rounded-md"></div>
                        
                        <div class="border-t border-gray-700 my-4"></div>

                        <div><label class="text-sm text-gray-400">Package Description</label><input type="text" name="packagedescription" class="w-full mt-1 p-2 form-input rounded-md" required></div>
                        <div><label class="text-sm text-gray-400">Package Image</label><input type="file" name="image" class="w-full mt-1 text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-sky-600 file:text-white hover:file:bg-sky-700" required></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="text-sm text-gray-400">Weight (e.g., 5kg)</label><input type="text" name="weight" class="w-full mt-1 p-2 form-input rounded-md"></div>
                            <div><label class="text-sm text-gray-400">Quantity</label><input type="number" name="quantity" value="1" class="w-full mt-1 p-2 form-input rounded-md"></div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row: Status and Other Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                        <label class="text-sm text-gray-400">Shipment Status</label>
                        <select name="status" class="w-full mt-1 p-2 form-input rounded-md">
                            <option>Pending</option><option>Active</option><option>In Transit</option><option>On Hold</option><option>Awaiting Delivery</option><option>Delivered</option>
                        </select>
                    </div>
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                        <label class="text-sm text-gray-400">Shipment Mode</label>
                        <input type="text" name="shipmentmethod" class="w-full mt-1 p-2 form-input rounded-md">
                    </div>
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                        <label class="text-sm text-gray-400">Payment Mode</label>
                        <input type="text" name="paymentmode" class="w-full mt-1 p-2 form-input rounded-md">
                    </div>
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                        <label class="text-sm text-gray-400">Estimated Delivery Date</label>
                        <div class="grid grid-cols-2 gap-2">
                             <input type="date" name="estimateddeliverydate" class="w-full mt-1 p-2 form-input rounded-md">
                             <input type="time" name="deliverytime" class="w-full mt-1 p-2 form-input rounded-md">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" name="add" class="bg-sky-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-all duration-300 shadow-lg hover:shadow-sky-500/50">
                        Create Shipment
                    </button>
                </div>
            </form>
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
