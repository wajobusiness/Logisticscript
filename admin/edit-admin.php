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
$msg = "";
$err = "";

// Fetch current admin details
$sql = mysqli_query($con, "SELECT * FROM admin LIMIT 1");
if ($sql && mysqli_num_rows($sql) > 0) {
    $admin_data = mysqli_fetch_assoc($sql);
} else {
    $err = "Could not retrieve admin profile to edit.";
}

// Handle form submission
if (isset($_POST['save'])) {
    $adminEmail = trim($_POST['email']);
    $adminPassword = $_POST['password']; // Don't trim password in case of leading/trailing spaces are intentional

    if (!empty($adminEmail) && !empty($adminPassword)) {
        // It's highly recommended to hash passwords before storing them.
        // For this example, we stick to the original logic.
        // A better implementation would be: $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $stmt = $con->prepare("UPDATE admin SET email = ?, password = ?");
        // For hashed password, use: $stmt->bind_param("ss", $adminEmail, $hashedPassword);
        $stmt->bind_param("ss", $adminEmail, $adminPassword);
        
        if ($stmt->execute()) {
            $msg = "Profile updated successfully!";
            // To see the change, we refetch the data
            $sql = mysqli_query($con, "SELECT * FROM admin LIMIT 1");
            $admin_data = mysqli_fetch_assoc($sql);
        } else {
            $err = "Failed to update profile.";
        }
        $stmt->close();
    } else {
        $err = "Email and password cannot be empty.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Universal Union Logistics Admin</title>
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
            <h1 class="text-2xl font-bold text-white">Edit Admin Profile</h1>
             <span class="text-white hidden sm:block">Welcome, <?php echo htmlspecialchars($_SESSION['ADMIN_LOGIN']); ?></span>
        </header>
        <main class="flex-1 p-4 sm:p-6">
            <div class="max-w-4xl mx-auto">
                
                <?php if ($msg != ""): ?>
                    <div class="bg-green-500/20 text-green-300 p-4 rounded-lg mb-6 text-center"><?php echo $msg; ?></div>
                <?php endif; ?>
                <?php if ($err != ""): ?>
                    <div class="bg-red-500/20 text-red-300 p-4 rounded-lg mb-6 text-center"><?php echo $err; ?></div>
                <?php endif; ?>

                <div class="bg-gray-800 rounded-2xl shadow-lg">
                    <div class="p-6 border-b border-gray-700">
                        <div role="tablist" class="flex space-x-4">
                            <a href="admin-profile.php" role="tab" class="px-4 py-2 text-sm font-semibold text-gray-300 hover:bg-gray-700 rounded-md">Overview</a>
                            <a href="edit-admin.php" role="tab" class="px-4 py-2 text-sm font-semibold text-white bg-sky-600 rounded-md">Edit Profile</a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <form method="POST" action="edit-admin.php">
                            <div class="space-y-6">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-text-secondary mb-2">Email Address</label>
                                    <input type="email" name="email" id="email" class="form-input w-full rounded-md py-3 px-4" value="<?php echo htmlspecialchars($admin_data['email'] ?? ''); ?>" required>
                                </div>
                                <div>
                                    <label for="password" class="block text-sm font-medium text-text-secondary mb-2">New Password</label>
                                    <input type="password" name="password" id="password" class="form-input w-full rounded-md py-3 px-4" value="<?php echo htmlspecialchars($admin_data['password'] ?? ''); ?>" required>
                                    <p class="text-xs text-gray-500 mt-1">Enter the new password. For security, we recommend changing it regularly.</p>
                                </div>
                            </div>
                            <div class="pt-6 mt-6 border-t border-gray-700 flex justify-end">
                                <button name="save" type="submit" class="bg-sky-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 focus:ring-offset-gray-800 transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
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
