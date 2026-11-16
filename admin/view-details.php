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

$tracking_data = [];
$err = "";

if (isset($_GET['post'])) {
    $post_id = mysqli_real_escape_string($con, $_GET['post']);
    $sql = mysqli_query($con, "SELECT * FROM addtracking WHERE tracking_id = '$post_id'");
    if (mysqli_num_rows($sql) > 0) {
        $tracking_data = mysqli_fetch_assoc($sql);
    } else {
        $err = "Shipment with this Tracking ID not found.";
    }
} else {
    header('location:dashboard.php');
    die();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Details - Universal Union Logistics Admin</title>
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
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #374151;
        }
        .detail-item:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        .detail-value {
            font-weight: 500;
            text-align: right;
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
            <h1 class="text-2xl font-bold text-white">Shipment Details</h1>
            <a href="dashboard.php" class="text-sky-400 hover:text-sky-300 text-sm font-semibold">&larr; Back to Dashboard</a>
        </header>
        <main class="flex-1 p-4 sm:p-6">
            <?php if ($err): ?>
                <div class="bg-red-500/20 text-red-300 p-4 rounded-lg text-center"><?php echo $err; ?></div>
            <?php else: ?>
            <div class="bg-gray-800 p-6 rounded-2xl shadow-lg mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Tracking Number</h3>
                        <p class="text-2xl mt-1 text-sky-400 font-mono"><?php echo htmlspecialchars($tracking_data['tracking_id']); ?></p>
                    </div>
                    <div class="mt-4 sm:mt-0 sm:text-right">
                        <h3 class="text-lg font-semibold text-white">Current Status</h3>
                         <span class="mt-1 px-3 py-1 text-sm font-medium rounded-full 
                        <?php 
                            switch(strtolower($tracking_data['status'])) {
                                case 'in transit': echo 'bg-blue-600/30 text-blue-300'; break;
                                case 'delivered': echo 'bg-green-600/30 text-green-300'; break;
                                case 'on hold': echo 'bg-yellow-600/30 text-yellow-300'; break;
                                default: echo 'bg-gray-600/30 text-gray-300';
                            }
                        ?>
                        ">
                            <?php echo htmlspecialchars($tracking_data['status']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Sender and Receiver -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                        <h4 class="text-lg font-bold text-white border-b border-gray-700 pb-2 mb-4">Sender Information</h4>
                        <ul class="text-sm space-y-3">
                           <li><strong class="text-gray-400 w-24 inline-block">Name:</strong> <?php echo htmlspecialchars($tracking_data['sender_name']); ?></li>
                           <li><strong class="text-gray-400 w-24 inline-block">Contact:</strong> <?php echo htmlspecialchars($tracking_data['sender_contact']); ?></li>
                           <li><strong class="text-gray-400 w-24 inline-block">Email:</strong> <?php echo htmlspecialchars($tracking_data['sender_email']); ?></li>
                           <li><strong class="text-gray-400 w-24 inline-block align-top">Address:</strong> <span class="inline-block w-2/3"><?php echo htmlspecialchars($tracking_data['sender_address']); ?></span></li>
                        </ul>
                    </div>
                     <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                        <h4 class="text-lg font-bold text-white border-b border-gray-700 pb-2 mb-4">Receiver Information</h4>
                        <ul class="text-sm space-y-3">
                           <li><strong class="text-gray-400 w-24 inline-block">Name:</strong> <?php echo htmlspecialchars($tracking_data['receiver_name']); ?></li>
                           <li><strong class="text-gray-400 w-24 inline-block">Contact:</strong> <?php echo htmlspecialchars($tracking_data['receiver_contact']); ?></li>
                           <li><strong class="text-gray-400 w-24 inline-block">Email:</strong> <?php echo htmlspecialchars($tracking_data['receiver_email']); ?></li>
                           <li><strong class="text-gray-400 w-24 inline-block align-top">Address:</strong> <span class="inline-block w-2/3"><?php echo htmlspecialchars($tracking_data['receiver_address']); ?></span></li>
                        </ul>
                    </div>
                </div>

                <!-- Right Column: Shipment Details and Image -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                        <h4 class="text-lg font-bold text-white border-b border-gray-700 pb-2 mb-4">Shipment Details</h4>
                        <div class="space-y-3 text-sm">
                            <div class="detail-item"><span class="detail-label">Dispatch Location</span><span class="detail-value"><?php echo htmlspecialchars($tracking_data['dispatch_location']); ?></span></div>
                             <div class="detail-item"><span class="detail-label">Final Destination</span><span class="detail-value"><?php echo htmlspecialchars($tracking_data['destination']); ?></span></div>
                             <div class="detail-item"><span class="detail-label">Dispatch Date</span><span class="detail-value"><?php echo date("M d, Y", strtotime($tracking_data['dispach_date'])); ?></span></div>
                             <div class="detail-item"><span class="detail-label">Est. Delivery</span><span class="detail-value"><?php echo date("M d, Y", strtotime($tracking_data['estimated_delivery_date'])); ?></span></div>
                             <div class="detail-item"><span class="detail-label">Carrier</span><span class="detail-value"><?php echo htmlspecialchars($tracking_data['carrier']); ?></span></div>
                             <div class="detail-item"><span class="detail-label">Carrier Ref #</span><span class="detail-value"><?php echo htmlspecialchars($tracking_data['carrier_refrence_number']); ?></span></div>
                             <div class="detail-item"><span class="detail-label">Shipment Mode</span><span class="detail-value"><?php echo htmlspecialchars($tracking_data['shipment_mode']); ?></span></div>
                             <div class="detail-item"><span class="detail-label">Payment Mode</span><span class="detail-value"><?php echo htmlspecialchars($tracking_data['payment_mode']); ?></span></div>
                             <div class="detail-item"><span class="detail-label">Weight</span><span class="detail-value"><?php echo htmlspecialchars($tracking_data['weight']); ?></span></div>
                             <div class="detail-item"><span class="detail-label">Quantity</span><span class="detail-value"><?php echo htmlspecialchars($tracking_data['quantity']); ?></span></div>
                        </div>
                    </div>
                    <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                        <h4 class="text-lg font-bold text-white mb-4">Package</h4>
                        <p class="text-sm text-gray-300 mb-4"><?php echo htmlspecialchars($tracking_data['package_discription']); ?></p>
                        <?php if (!empty($tracking_data['image'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($tracking_data['image']); ?>" alt="Package Image" class="w-full h-auto object-cover rounded-lg">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
