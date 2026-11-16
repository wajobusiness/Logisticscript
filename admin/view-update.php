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

$tracking_updates = [];
$err = "";
$edit_id = ''; // Initialize to prevent errors

if (isset($_GET['edit'])) {
    $edit_id = mysqli_real_escape_string($con, $_GET['edit']);

    // Handle delete action first
    if (isset($_GET['delete'])) {
        $delete_id = mysqli_real_escape_string($con, $_GET['delete']);
        $delete_sql = "DELETE FROM track_update WHERE id = '$delete_id' AND track_num = '$edit_id'";
        if (mysqli_query($con, $delete_sql)) {
            // Redirect back to the same page without the delete parameter to avoid re-deleting on refresh
            header("Location: view-update.php?edit=" . urlencode($edit_id) . "&status=deleted");
            exit();
        } else {
            $err = "Error deleting record: " . mysqli_error($con);
        }
    }
    
    // Fetch all updates for the given tracking number
    $sql = mysqli_query($con, "SELECT * FROM track_update WHERE track_num = '$edit_id' ORDER BY date DESC, time DESC");
    if (mysqli_num_rows($sql) > 0) {
        while ($row = mysqli_fetch_assoc($sql)) {
            $tracking_updates[] = $row;
        }
    } else {
        $err = "No updates found for this tracking number.";
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
    <title>Shipment History - Universal Union Logistics Admin</title>
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
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -21px; /* (2rem / 2) - (dot_width / 2) = 1rem - 0.25rem = 12px... but needs adjustment */
            top: 0.5rem;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #4B5563; /* gray-600 */
            border: 2px solid var(--bg-dark-secondary);
        }
        .timeline-item:first-child::before {
             background-color: var(--accent-primary);
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
            <h1 class="text-2xl font-bold text-white">Shipment History</h1>
            <a href="dashboard.php" class="text-sky-400 hover:text-sky-300 text-sm font-semibold">&larr; Back to Dashboard</a>
        </header>
        <main class="flex-1 p-4 sm:p-6">
            <div class="bg-gray-800 p-6 rounded-2xl shadow-lg mb-6">
                <h3 class="text-lg font-semibold text-white">History for Tracking Number</h3>
                <p class="text-2xl mt-1 text-sky-400 font-mono"><?php echo htmlspecialchars($edit_id); ?></p>
            </div>
            
             <?php if (isset($_GET['status']) && $_GET['status'] == 'deleted'): ?>
                <div class="bg-green-500/20 text-green-300 p-4 rounded-lg mb-6 text-center">Update record deleted successfully.</div>
            <?php endif; ?>
            <?php if ($err): ?>
                <div class="bg-red-500/20 text-red-300 p-4 rounded-lg text-center"><?php echo $err; ?></div>
            <?php else: ?>
                <div class="bg-gray-800 p-6 rounded-2xl shadow-lg">
                    <div class="relative border-l-2 border-gray-700 ml-4 pl-8 space-y-8">
                        <?php foreach($tracking_updates as $update): ?>
                        <div class="timeline-item relative">
                            <div class="flex justify-between items-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                <?php 
                                    switch(strtolower($update['status'])) {
                                        case 'in transit': echo 'bg-blue-600/30 text-blue-300'; break;
                                        case 'delivered': echo 'bg-green-600/30 text-green-300'; break;
                                        case 'on hold': echo 'bg-yellow-600/30 text-yellow-300'; break;
                                        default: echo 'bg-gray-600/30 text-gray-300';
                                    }
                                ?>">
                                    <?php echo htmlspecialchars($update['status']); ?>
                                </span>
                                <a href="?edit=<?php echo urlencode($edit_id); ?>&delete=<?php echo $update['id']; ?>" onclick="return confirm('Are you sure you want to delete this update?')" class="text-red-500 hover:text-red-400" title="Delete Update">
                                    <i data-feather="trash-2" class="h-4 w-4"></i>
                                </a>
                            </div>
                            <p class="text-sm text-gray-400 mt-2"><?php echo date('F j, Y, g:i a', strtotime($update['date'] . ' ' . $update['time'])); ?></p>
                            <p class="mt-1 text-gray-200"><?php echo nl2br(htmlspecialchars($update['note'])); ?></p>
                            <p class="text-xs text-gray-500 mt-1">Location: <?php echo htmlspecialchars($update['current_location']); ?></p>
                        </div>
                        <?php endforeach; ?>
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
