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

$admin_data = [];
$err = "";

// Fetch admin details
$sql = mysqli_query($con, "SELECT * FROM admin LIMIT 1"); // Assuming there's only one admin
if ($sql && mysqli_num_rows($sql) > 0) {
    $admin_data = mysqli_fetch_assoc($sql);
} else {
    $err = "Could not retrieve admin profile.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Universal Union Logistics Admin</title>
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
            padding: 1rem 0;
            border-bottom: 1px solid #374151;
        }
        .detail-label {
            color: var(--text-secondary);
            width: 8rem; /* 128px */
            flex-shrink: 0;
        }
        .detail-value {
            font-weight: 500;
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
             <a href="admin-profile.php" class="flex items-center px-4 py-2 bg-sky-600 text-white rounded-md transition-colors duration-200">
                <i data-feather="user" class="h-5 w-5 mr-3"></i> Admin Profile
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
            <h1 class="text-2xl font-bold text-white">Admin Profile</h1>
             <span class="text-white hidden sm:block">Welcome, <?php echo htmlspecialchars($_SESSION['ADMIN_LOGIN']); ?></span>
        </header>
        <main class="flex-1 p-4 sm:p-6">
            <div class="max-w-4xl mx-auto">

                <?php if ($err): ?>
                    <div class="bg-red-500/20 text-red-300 p-4 rounded-lg text-center"><?php echo $err; ?></div>
                <?php else: ?>
                <div class="bg-gray-800 rounded-2xl shadow-lg">
                    <div class="p-6 border-b border-gray-700">
                        <div role="tablist" class="flex space-x-4">
                            <a href="admin-profile.php" role="tab" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-md">Overview</a>
                            <a href="edit-admin.php" role="tab" class="px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-gray-700 rounded-md">Edit Profile</a>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-white">About</h3>
                            <p class="mt-1 text-sm text-text-secondary">This section displays the current administrator credentials. The admin has full rights to manage all aspects of the site.</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-bold text-white">Profile Details</h3>
                            <div class="mt-4">
                                <div class="detail-item">
                                    <span class="detail-label">Email Address</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($admin_data['email'] ?? 'N/A'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Password</span>
                                    <span class="detail-value font-mono">************</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-700 flex justify-end">
                            <a href="logout.php" class="bg-red-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-colors">
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
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
