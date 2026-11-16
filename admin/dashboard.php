<?php
session_start();
include '../db.php'; 
include '../functions.php';

if (!isset($_SESSION['ADMIN_LOGIN'])) {
    header('location:login.php');
    die();
}

$total_shipments_result = mysqli_query($con, "SELECT COUNT(*) as count FROM addtracking");
$total_shipments = mysqli_fetch_assoc($total_shipments_result)['count'];

$in_transit_result = mysqli_query($con, "SELECT COUNT(*) as count FROM addtracking WHERE status = 'In Transit'");
$in_transit_count = mysqli_fetch_assoc($in_transit_result)['count'];

$delivered_result = mysqli_query($con, "SELECT COUNT(*) as count FROM addtracking WHERE status = 'Delivered'");
$delivered_count = mysqli_fetch_assoc($delivered_result)['count'];

$on_hold_result = mysqli_query($con, "SELECT COUNT(*) as count FROM addtracking WHERE status = 'On Hold'");
$on_hold_count = mysqli_fetch_assoc($on_hold_result)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Universal Union Logistics</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700;800&family=Yantramanav:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/feather-icons"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sky: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                        }
                    },
                    fontFamily: {
                        heading: ['Lexend', 'sans-serif'],
                        body: ['Yantramanav', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --bg-dark-primary: #0f172a;
            --bg-dark-secondary: #1e293b;
            --accent-primary: #0284c7;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
        }
        body {
            font-family: 'Yantramanav', sans-serif;
            background-color: var(--bg-dark-primary);
            color: var(--text-primary);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Lexend', sans-serif;
        }
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 20;
        }
    </style>
</head>
<body class="flex bg-slate-900 text-slate-100 min-h-screen">

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="md:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-slate-800 shadow-xl border-r border-slate-700 min-h-screen flex flex-col fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition-transform duration-300 ease-in-out z-30">
        <div class="p-4 flex items-center justify-center border-b border-slate-700">
            <div class="bg-white p-2 rounded-xl shadow-md inline-flex items-center justify-center w-full">
                <img src="../assets/img/logo.png" alt="Universal Union Logistics" class="h-10 w-auto object-contain">
            </div>
        </div>
        <nav class="flex-1 px-4 py-6 space-y-2">
            <a href="dashboard.php" class="flex items-center px-4 py-3 bg-sky-600 text-white rounded-xl shadow-md shadow-sky-600/25 font-heading text-sm font-semibold transition-all">
                <i data-feather="home" class="h-4 w-4 mr-3"></i> Dashboard
            </a>
            <a href="add-tracking.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white rounded-xl font-heading text-sm font-medium transition-all">
                <i data-feather="plus-circle" class="h-4 w-4 mr-3 text-sky-400"></i> Add Shipment
            </a>
            <a href="edit-admin.php" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white rounded-xl font-heading text-sm font-medium transition-all">
                <i data-feather="settings" class="h-4 w-4 mr-3 text-sky-400"></i> Settings
            </a>
            <a href="../index.html" target="_blank" class="flex items-center px-4 py-3 text-slate-300 hover:bg-slate-700 hover:text-white rounded-xl font-heading text-sm font-medium transition-all">
                <i data-feather="external-link" class="h-4 w-4 mr-3 text-sky-400"></i> Public Website
            </a>
        </nav>
        <div class="p-4 border-t border-slate-700">
            <a href="logout.php" class="flex items-center px-4 py-3 text-red-400 hover:bg-red-500/10 hover:text-red-300 rounded-xl font-heading text-sm font-semibold transition-all">
                <i data-feather="log-out" class="h-4 w-4 mr-3"></i> Log Out
            </a>
        </div>
    </aside>

    <!-- Main Content Panel -->
    <div class="flex-1 flex flex-col overflow-x-hidden">
        <header class="bg-slate-800 border-b border-slate-700 shadow-sm p-4 sm:px-8 flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <button id="menu-btn" class="md:hidden p-2 rounded-lg hover:bg-slate-700 text-white">
                    <i data-feather="menu" class="h-5 w-5"></i>
                </button>
                <h1 class="text-xl sm:text-2xl font-bold text-white font-heading">Operations Dashboard</h1>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-sky-600/30 text-sky-400 flex items-center justify-center font-bold text-xs border border-sky-500/40">
                    AD
                </div>
                <span class="text-slate-300 text-sm hidden sm:block font-medium"><?php echo htmlspecialchars($_SESSION['ADMIN_LOGIN']); ?></span>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-8">
            <?php
            if (isset($_GET['delete'])) {
                $id = mysqli_real_escape_string($con, $_GET['delete']);
                $sql = mysqli_query($con, "DELETE FROM addtracking WHERE tracking_id = '$id' ");
                if ($sql) {
                    echo '<div class="bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 p-4 rounded-xl mb-6 flex items-center gap-2"><i data-feather="check-circle" class="h-5 w-5"></i><span>Shipment #'.htmlspecialchars($id).' deleted successfully.</span></div>';
                } else {
                    echo '<div class="bg-red-500/20 border border-red-500/30 text-red-300 p-4 rounded-xl mb-6 flex items-center gap-2"><i data-feather="alert-triangle" class="h-5 w-5"></i><span>Error deleting shipment.</span></div>';
                }
            }
            ?>

            <!-- Stats Metric Cards (Sky Blue Theme) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Shipments -->
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Total Shipments</p>
                        <p class="text-3xl font-extrabold text-white font-heading"><?php echo $total_shipments; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center border border-sky-500/30">
                        <i data-feather="package" class="h-6 w-6"></i>
                    </div>
                </div>

                <!-- In Transit -->
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">In Transit</p>
                        <p class="text-3xl font-extrabold text-sky-400 font-heading"><?php echo $in_transit_count; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-sky-600/20 text-sky-400 flex items-center justify-center border border-sky-500/30">
                        <i data-feather="truck" class="h-6 w-6"></i>
                    </div>
                </div>

                <!-- Delivered -->
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">Delivered</p>
                        <p class="text-3xl font-extrabold text-emerald-400 font-heading"><?php echo $delivered_count; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center border border-emerald-500/30">
                        <i data-feather="check-circle" class="h-6 w-6"></i>
                    </div>
                </div>

                <!-- On Hold -->
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700 shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1">On Hold</p>
                        <p class="text-3xl font-extrabold text-amber-400 font-heading"><?php echo $on_hold_count; ?></p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center border border-amber-500/30">
                        <i data-feather="pause-circle" class="h-6 w-6"></i>
                    </div>
                </div>
            </div>

            <!-- Shipments Ledger Table -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
                <div class="p-6 border-b border-slate-700 flex justify-between items-center flex-wrap gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-white font-heading">Consignment Ledger</h2>
                        <p class="text-slate-400 text-xs mt-0.5">Real-time database records of registered logistics consignments</p>
                    </div>
                    <a href="add-tracking.php" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-bold font-heading shadow-md shadow-sky-600/30 transition-all">
                        <i data-feather="plus" class="h-4 w-4"></i>
                        <span>Register New Shipment</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-300">
                        <thead class="text-xs text-slate-400 uppercase bg-slate-900/60 font-heading tracking-wider">
                            <tr>
                                <th scope="col" class="px-6 py-4">Tracking Code</th>
                                <th scope="col" class="px-6 py-4">Package Specifications</th>
                                <th scope="col" class="px-6 py-4">Current Status</th>
                                <th scope="col" class="px-6 py-4">Dispatch Date</th>
                                <th scope="col" class="px-6 py-4 text-right">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <?php
                            $select = mysqli_query($con, "SELECT * FROM addtracking ORDER BY id DESC");
                            if (mysqli_num_rows($select) > 0) {
                                while ($row = mysqli_fetch_assoc($select)) {
                            ?>
                            <tr class="hover:bg-slate-700/40 transition-colors">
                                <td class="px-6 py-4 font-medium text-white">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sky-400 font-bold"><?php echo htmlspecialchars($row['tracking_id']); ?></span>
                                        <button onclick="copyToClipboard('<?php echo htmlspecialchars($row['tracking_id']); ?>')" class="text-slate-400 hover:text-white p-1" title="Copy tracking ID">
                                            <i data-feather="copy" class="h-3.5 w-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-white"><?php echo htmlspecialchars($row['package_discription']); ?></p>
                                    <p class="text-slate-400 text-xs">Weight: <?php echo htmlspecialchars($row['weight']); ?> | Carrier: <?php echo htmlspecialchars($row['carrier']); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full inline-block
                                    <?php 
                                        switch(strtolower(trim($row['status']))) {
                                            case 'in transit': echo 'bg-sky-500/20 text-sky-300 border border-sky-500/30'; break;
                                            case 'delivered': echo 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'; break;
                                            case 'on hold': echo 'bg-amber-500/20 text-amber-300 border border-amber-500/30'; break;
                                            default: echo 'bg-slate-700 text-slate-300 border border-slate-600';
                                        }
                                    ?>
                                    ">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-400 text-xs">
                                    <?php echo htmlspecialchars($row['dispach_date'] ?: $row['date_added']); ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <a href="view-details.php?post=<?php echo urlencode($row['tracking_id']); ?>" class="p-2 text-slate-300 hover:bg-slate-700 hover:text-sky-400 rounded-lg transition-colors" title="View Full Details">
                                            <i data-feather="eye" class="h-4 w-4"></i>
                                        </a>
                                        <a href="view-update.php?edit=<?php echo urlencode($row['tracking_id']); ?>" class="p-2 text-slate-300 hover:bg-slate-700 hover:text-sky-400 rounded-lg transition-colors" title="Timeline Milestones">
                                            <i data-feather="activity" class="h-4 w-4"></i>
                                        </a>
                                        <a href="edit.php?edit=<?php echo urlencode($row['tracking_id']); ?>" class="p-2 text-slate-300 hover:bg-slate-700 hover:text-sky-400 rounded-lg transition-colors" title="Edit Shipment">
                                            <i data-feather="edit-2" class="h-4 w-4"></i>
                                        </a>
                                        <a href="../track/print.php?num=<?php echo urlencode($row['tracking_id']); ?>" target="_blank" class="p-2 text-slate-300 hover:bg-slate-700 hover:text-sky-400 rounded-lg transition-colors" title="Print Waybill">
                                            <i data-feather="printer" class="h-4 w-4"></i>
                                        </a>
                                        <a href="dashboard.php?delete=<?php echo urlencode($row['tracking_id']); ?>" onclick="return confirm('Confirm permanent deletion of shipment <?php echo htmlspecialchars($row['tracking_id']); ?>?')" class="p-2 text-red-400 hover:bg-red-500/20 rounded-lg transition-colors" title="Delete Record">
                                            <i data-feather="trash-2" class="h-4 w-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="5" class="text-center p-8 text-slate-400">No shipments found in database.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        feather.replace();

        // Mobile drawer toggle
        const menuBtn = document.getElementById('menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        if (menuBtn && sidebar && overlay) {
            menuBtn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.style.display = sidebar.classList.contains('-translate-x-full') ? 'none' : 'block';
            });
            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.style.display = 'none';
            });
        }

        // Copy Tracking ID
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied tracking ID: ' + text);
            });
        }
    </script>
</body>
</html>
