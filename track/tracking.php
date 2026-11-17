<?php
session_start();
include '../db.php';
include '../functions.php';

$tracking_data = null;
$tracking_history = [];
$error_message = "";

$tracking_pr = '';
if (isset($_POST['search_P']) && trim($_POST['search_P']) !== '') {
    $tracking_pr = trim($_POST['search_P']);
} elseif (isset($_GET['tracking_id']) && trim($_GET['tracking_id']) !== '') {
    $tracking_pr = trim($_GET['tracking_id']);
} elseif (isset($_GET['num']) && trim($_GET['num']) !== '') {
    $tracking_pr = trim($_GET['num']);
} elseif (isset($_GET['search_P']) && trim($_GET['search_P']) !== '') {
    $tracking_pr = trim($_GET['search_P']);
} elseif (isset($_SESSION['search_P']) && trim($_SESSION['search_P']) !== '') {
    $tracking_pr = trim($_SESSION['search_P']);
}

if (!empty($tracking_pr)) {
    $safe_tracking_pr = mysqli_real_escape_string($con, $tracking_pr);
    $sql = mysqli_query($con, "SELECT * FROM addtracking WHERE tracking_id = '$safe_tracking_pr' ");
    if ($sql && mysqli_num_rows($sql) > 0) {
        $tracking_data = mysqli_fetch_assoc($sql);
        $_SESSION['search_P'] = $tracking_data['tracking_id'];

        // Fetch tracking history
        $history_sql = mysqli_query($con, "SELECT * FROM track_update WHERE track_num = '$safe_tracking_pr' ORDER BY date DESC, time DESC");
        while ($row = mysqli_fetch_assoc($history_sql)) {
            $tracking_history[] = $row;
        }
    } else {
        $error_message = "Tracking number '$tracking_pr' not found in our global database. Please check the number and try again.";
        unset($_SESSION['search_P']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Results - Universal Union Logistics</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Yantramanav:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Sky Logistics Master Design System -->
    <link rel="stylesheet" href="../assets/css/sky-logistics.css">
    <link rel="shortcut icon" href="../assets/img/favicon.png" type="image/x-icon">

    <style>
        .stepper-progress-bar {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 2.5rem 0 1.5rem 0;
        }
        .stepper-progress-bar::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 24px;
            right: 24px;
            height: 4px;
            background-color: var(--border-color);
            z-index: 1;
        }
        .stepper-step {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 120px;
        }
        .stepper-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: #ffffff;
            border: 3px solid var(--border-color);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            transition: var(--transition);
        }
        .stepper-step.completed .stepper-circle {
            background-color: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 0 0 4px var(--primary-light);
        }
        .stepper-step.active .stepper-circle {
            background-color: #0284c7;
            border-color: #0284c7;
            color: #ffffff;
            box-shadow: 0 0 0 6px var(--primary-glow);
            animation: pulseGlow 2s infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 4px var(--primary-glow); }
            50% { box-shadow: 0 0 0 8px rgba(2, 132, 199, 0.4); }
        }
        .stepper-label {
            font-family: var(--font-heading);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-body);
        }
        .stepper-step.completed .stepper-label,
        .stepper-step.active .stepper-label {
            color: var(--secondary);
            font-weight: 700;
        }

        /* Timeline History */
        .timeline-wrap {
            position: relative;
            padding-left: 2rem;
            border-left: 2px dashed var(--primary);
            margin-left: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1.75rem;
        }
        .timeline-node {
            position: relative;
        }
        .timeline-node::before {
            content: '';
            position: absolute;
            left: -2.45rem;
            top: 4px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #ffffff;
            border: 3px solid var(--primary);
            box-shadow: 0 0 0 3px var(--primary-light);
        }
        .timeline-node.latest::before {
            background-color: var(--primary);
            border-color: #ffffff;
            box-shadow: 0 0 0 4px var(--primary);
        }

        /* Consignment Item & Cargo Showcase */
        .consignment-item-card {
            background-color: #ffffff;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        .consignment-item-header {
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(to right, #f8fafc, #ffffff);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .consignment-item-body {
            padding: 2rem;
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 2.25rem;
            align-items: start;
        }
        .package-image-frame {
            position: relative;
            border-radius: var(--radius-md);
            border: 2px solid var(--border-color);
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 260px;
        }
        .package-image-preview {
            width: 100%;
            height: 260px;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .package-image-preview:hover {
            transform: scale(1.03);
        }
        .package-badge-tag {
            position: absolute;
            top: 12px;
            left: 12px;
            background-color: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(4px);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.3rem 0.75rem;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        }
        .package-image-caption {
            width: 100%;
            background-color: #ffffff;
            border-top: 1px solid var(--border-color);
            padding: 0.5rem 0.75rem;
            text-align: center;
            font-size: 0.78rem;
            color: var(--text-muted);
        }
        .package-specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
        }
        .spec-box {
            background-color: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 0.85rem 1.15rem;
            transition: var(--transition);
        }
        .spec-box:hover {
            border-color: #bae6fd;
            background-color: #f0f9ff;
        }
        .spec-label {
            font-size: 0.75rem;
            font-family: var(--font-heading);
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .spec-value {
            font-family: var(--font-heading);
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--secondary);
            word-break: break-word;
        }
        .stats-grid-responsive {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }
        .tracking-details-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.75fr;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 991px) {
            .consignment-item-body {
                grid-template-columns: 1fr;
                gap: 1.75rem;
            }
            .package-image-frame {
                height: 300px;
            }
            .package-image-preview {
                height: 300px;
            }
            .tracking-details-grid {
                grid-template-columns: 1fr;
            }
            .stats-grid-responsive {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 640px) {
            .package-specs-grid {
                grid-template-columns: 1fr;
                gap: 0.85rem;
            }
            .stats-grid-responsive {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- =========================================================================
         SITE HEADER & NAVIGATION
         ========================================================================= -->
    <header class="site-header">
        <div class="container">
            <div class="header-inner">
                <a href="../index.html" class="brand-logo" aria-label="Universal Union Logistics">
                    <img src="../assets/img/logo.png" alt="Universal Union Logistics" class="site-logo">
                </a>

                <ul class="desktop-nav-menu">
                    <li><a href="../index.html" class="nav-link-item">Home</a></li>
                    <li><a href="../about.html" class="nav-link-item">About Us</a></li>
                    <li><a href="../services.html" class="nav-link-item">Services</a></li>
                    <li>
                        <a href="javascript:void(0)" class="nav-link-item">
                            Company
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </a>
                        <ul class="nav-dropdown">
                            <li><a href="../faq.html">FAQs</a></li>
                            <li><a href="../privacy.html">Privacy & Policy</a></li>
                            <li><a href="../terms.html">Terms of Service</a></li>
                        </ul>
                    </li>
                    <li><a href="../contact.html" class="nav-link-item">Contact</a></li>
                    <li><a href="../track.html" class="nav-link-item active">Tracking</a></li>
                </ul>

                <div class="header-actions">
                    <a href="../track.html" class="btn-sky header-cta-btn" aria-label="New Search">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                        <span class="cta-text">New Search</span>
                    </a>
                    <button class="mobile-nav-toggle" aria-label="Toggle navigation">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Drawer -->
    <div class="mobile-drawer-overlay"></div>
    <div class="mobile-drawer">
        <button class="mobile-drawer-close" aria-label="Close menu">&times;</button>
        <div class="mobile-drawer-brand" style="margin-bottom: 1.5rem;">
            <a href="../index.html" class="brand-logo" aria-label="Universal Union Logistics">
                <img src="../assets/img/logo.png" alt="Universal Union Logistics" class="site-logo-drawer">
            </a>
        </div>
        <ul class="mobile-nav-links">
            <li><a href="../index.html">Home</a></li>
            <li><a href="../about.html">About Us</a></li>
            <li><a href="../services.html">Services</a></li>
            <li><a href="../faq.html">FAQs</a></li>
            <li><a href="../privacy.html">Privacy & Policy</a></li>
            <li><a href="../terms.html">Terms of Service</a></li>
            <li><a href="../contact.html">Contact Us</a></li>
            <li><a href="../track.html">Track Shipment</a></li>
        </ul>
    </div>

    <!-- BREADCRUMB BANNER -->
    <section class="page-breadcrumb-banner">
        <div class="container">
            <h1>Shipment Telemetry</h1>
            <div class="breadcrumb-trail">
                <a href="../index.html">Home</a>
                <span>/</span>
                <a href="../track.html">Tracking</a>
                <span>/</span>
                <span>Live Status</span>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         TRACKING RESULTS & PROGRESS
         ========================================================================= -->
    <main class="section-spacing bg-subtle-light">
        <div class="container">
            
            <!-- Quick Search Bar at Top -->
            <div style="background-color: #ffffff; padding: 1.5rem 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); margin-bottom: 2.5rem;">
                <form action="tracking.php" method="POST" style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <input type="text" name="search_P" value="<?php echo htmlspecialchars($tracking_data['tracking_id'] ?? ''); ?>" placeholder="Enter tracking code" class="form-control-sky" style="flex: 1; min-width: 240px;" required>
                    <button type="submit" name="search" class="btn-sky">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        Update Query
                    </button>
                </form>
            </div>

            <?php if ($tracking_data): ?>
                <?php
                    $status = strtolower(trim($tracking_data['status']));
                    // Determine milestone step: 1 = Active/Booked, 2 = In Transit, 3 = Out for Delivery / Awaiting, 4 = Delivered
                    $step = 1;
                    if (strpos($status, 'transit') !== false) {
                        $step = 2;
                    } elseif (strpos($status, 'awaiting') !== false || strpos($status, 'out') !== false) {
                        $step = 3;
                    } elseif (strpos($status, 'delivered') !== false) {
                        $step = 4;
                    }
                ?>

                <!-- Main Status & Stepper Card -->
                <div style="background-color: #ffffff; padding: 2.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                    <!-- Top Status Header -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
                        <div>
                            <span style="font-size: 0.88rem; font-family: var(--font-heading); color: var(--text-muted); text-transform: uppercase;">Tracking Number</span>
                            <h2 style="font-size: 2rem; color: var(--secondary); margin: 0.15rem 0;">
                                <?php echo htmlspecialchars($tracking_data['tracking_id']); ?>
                            </h2>
                            <p style="margin: 0; color: var(--text-body); font-size: 0.95rem;">
                                Shipment Mode: <strong style="color: var(--secondary); text-transform: capitalize;"><?php echo htmlspecialchars($tracking_data['shipment_mode'] ?: 'Air Express'); ?></strong> | Package: <strong><?php echo htmlspecialchars($tracking_data['package_discription']); ?></strong>
                            </p>
                        </div>

                        <div style="text-align: right;">
                            <span class="kicker-badge" style="margin-bottom: 0; font-size: 0.95rem; padding: 0.5rem 1.25rem; <?php echo ($step === 4) ? 'background-color: #ecfdf5; color: #059669; border-color: #a7f3d0;' : ''; ?>">
                                <?php echo htmlspecialchars($tracking_data['status']); ?>
                            </span>
                        </div>
                    </div>

                    <!-- 4-Stage Stepper -->
                    <div class="stepper-progress-bar">
                        <div class="stepper-step <?php echo ($step >= 1) ? (($step == 1) ? 'active' : 'completed') : ''; ?>">
                            <div class="stepper-circle">
                                <?php echo ($step > 1) ? '<i class="fa-solid fa-check"></i>' : '1'; ?>
                            </div>
                            <div class="stepper-label">Dispatched</div>
                        </div>

                        <div class="stepper-step <?php echo ($step >= 2) ? (($step == 2) ? 'active' : 'completed') : ''; ?>">
                            <div class="stepper-circle">
                                <?php echo ($step > 2) ? '<i class="fa-solid fa-check"></i>' : '2'; ?>
                            </div>
                            <div class="stepper-label">In Transit</div>
                        </div>

                        <div class="stepper-step <?php echo ($step >= 3) ? (($step == 3) ? 'active' : 'completed') : ''; ?>">
                            <div class="stepper-circle">
                                <?php echo ($step > 3) ? '<i class="fa-solid fa-check"></i>' : '3'; ?>
                            </div>
                            <div class="stepper-label">Out for Delivery</div>
                        </div>

                        <div class="stepper-step <?php echo ($step >= 4) ? 'completed' : ''; ?>">
                            <div class="stepper-circle">
                                <?php echo ($step >= 4) ? '<i class="fa-solid fa-check"></i>' : '4'; ?>
                            </div>
                            <div class="stepper-label">Delivered</div>
                        </div>
                    </div>

                    <!-- 4 Metric Cards -->
                    <div class="stats-grid-responsive">
                        <div class="stat-box">
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Assigned Carrier</div>
                            <div style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--secondary);">
                                <?php echo htmlspecialchars($tracking_data['carrier'] ?: 'Global Freight'); ?>
                            </div>
                        </div>

                        <div class="stat-box">
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Gross Weight</div>
                            <div style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--secondary);">
                                <?php echo htmlspecialchars($tracking_data['weight']); ?>
                            </div>
                        </div>

                        <div class="stat-box">
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Dispatch Date</div>
                            <div style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--secondary);">
                                <?php echo htmlspecialchars($tracking_data['dispach_date']); ?>
                            </div>
                        </div>

                        <div class="stat-box">
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">Est. Arrival</div>
                            <div style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--primary);">
                                <?php echo htmlspecialchars($tracking_data['estimated_delivery_date']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Consignment Item & Cargo Details Card (Image & Complete Specifications) -->
                <div class="consignment-item-card">
                    <div class="consignment-item-header">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 38px; height: 38px; border-radius: 8px; background-color: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.15rem;">
                                <i class="fa-solid fa-boxes-packing"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--secondary); margin: 0;">
                                    Consignment Item & Cargo Details
                                </h3>
                                <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                                    Physical item snapshot, freight characteristics & dispatch manifest
                                </p>
                            </div>
                        </div>
                        <span class="kicker-badge" style="font-size: 0.75rem; padding: 0.25rem 0.85rem; margin-bottom: 0;">
                            <i class="fa-solid fa-shield-halved" style="margin-right: 0.35rem;"></i> Verified Cargo Record
                        </span>
                    </div>

                    <div class="consignment-item-body">
                        <!-- Left: Item Photo Showcase -->
                        <div class="package-image-frame">
                            <?php if (!empty($tracking_data['image'])): ?>
                                <div class="package-badge-tag">
                                    <i class="fa-solid fa-camera"></i> Package Item Photo
                                </div>
                                <a href="../uploads/<?php echo htmlspecialchars($tracking_data['image']); ?>" target="_blank" title="Click to view full high-resolution image" style="width: 100%; display: block;">
                                    <img src="../uploads/<?php echo htmlspecialchars($tracking_data['image']); ?>" alt="Package Item Photo" class="package-image-preview" onerror="this.onerror=null; this.src='../assets/img/cargo-plane.png';">
                                </a>
                                <div class="package-image-caption">
                                    <i class="fa-solid fa-magnifying-glass-plus" style="color: var(--primary); margin-right: 0.25rem;"></i>
                                    Click photo to inspect full resolution
                                </div>
                            <?php else: ?>
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding: 2.5rem 1.5rem; text-align: center;">
                                    <div style="width: 64px; height: 64px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin-bottom: 0.75rem;">
                                        <i class="fa-solid fa-boxes-stacked"></i>
                                    </div>
                                    <strong style="color: var(--secondary); font-size: 1rem;">Standard Consignment Freight</strong>
                                    <span style="font-size: 0.82rem; color: var(--text-muted); margin-top: 0.35rem;">
                                        No item snapshot uploaded
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Right: Detailed Item & Manifest Specifications -->
                        <div>
                            <div class="package-specs-grid">
                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-solid fa-box" style="color: var(--primary);"></i> Cargo Description</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($tracking_data['package_discription'] ?: 'Standard Logistics Cargo'); ?></div>
                                </div>

                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-solid fa-weight-hanging" style="color: var(--primary);"></i> Total Gross Weight</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($tracking_data['weight'] ?: 'Not recorded'); ?></div>
                                </div>

                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-solid fa-cubes" style="color: var(--primary);"></i> Quantity / Pieces</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($tracking_data['quantity'] ?: '1 Package / Item'); ?></div>
                                </div>

                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-solid fa-plane-departure" style="color: var(--primary);"></i> Shipment Mode</div>
                                    <div class="spec-value" style="text-transform: capitalize;"><?php echo htmlspecialchars($tracking_data['shipment_mode'] ?: 'Express Air Freight'); ?></div>
                                </div>

                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-solid fa-credit-card" style="color: var(--primary);"></i> Payment Terms / Mode</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($tracking_data['payment_mode'] ?: 'Prepaid'); ?></div>
                                </div>

                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-solid fa-barcode" style="color: var(--primary);"></i> Carrier Ref Number</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($tracking_data['carrier_refrence_number'] ?: 'N/A'); ?></div>
                                </div>

                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-solid fa-warehouse" style="color: var(--primary);"></i> Origin Dispatch Hub</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($tracking_data['dispatch_location'] ?: 'Origin Terminal'); ?></div>
                                </div>

                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-solid fa-location-crosshairs" style="color: var(--primary);"></i> Final Destination</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($tracking_data['destination'] ?: 'Destination Hub'); ?></div>
                                </div>

                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-regular fa-calendar-check" style="color: var(--primary);"></i> Dispatch Date</div>
                                    <div class="spec-value"><?php echo htmlspecialchars($tracking_data['dispach_date'] ?: 'N/A'); ?></div>
                                </div>

                                <div class="spec-box">
                                    <div class="spec-label"><i class="fa-regular fa-clock" style="color: var(--primary);"></i> Expected Delivery</div>
                                    <div class="spec-value" style="color: var(--primary);">
                                        <?php echo htmlspecialchars($tracking_data['estimated_delivery_date'] ?: 'In Transit'); ?>
                                        <?php if (!empty($tracking_data['delivery_time'])): ?>
                                            <span style="font-size: 0.85rem; font-weight: normal; color: var(--text-muted);">(<?php echo htmlspecialchars($tracking_data['delivery_time']); ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (!empty($tracking_data['delivery_message']) || !empty($tracking_data['message'])): ?>
                                <div style="margin-top: 1.25rem; padding: 1rem 1.25rem; background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: var(--radius-md); font-size: 0.9rem; color: #0369a1;">
                                    <strong style="font-family: var(--font-heading); display: block; margin-bottom: 0.35rem;">
                                        <i class="fa-solid fa-circle-info" style="margin-right: 0.35rem;"></i> Special Cargo & Delivery Remarks:
                                    </strong>
                                    <?php echo nl2br(htmlspecialchars($tracking_data['delivery_message'] ?: $tracking_data['message'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 2-Column Details: Route & History -->
                <div class="tracking-details-grid">
                    <!-- Left: Map & Addresses -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <!-- Live Map Embed -->
                        <div style="background-color: #ffffff; border-radius: var(--radius-lg); border: 1px solid var(--border-color); overflow: hidden; box-shadow: var(--shadow-sm);">
                            <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color); font-family: var(--font-heading); font-weight: 700; color: var(--secondary); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-map-location-dot" style="color: var(--primary);"></i>
                                Current Geographic Transit Coordinate: <?php echo htmlspecialchars($tracking_data['current_location'] ?: $tracking_data['destination']); ?>
                            </div>
                            <div style="height: 320px; width: 100%;">
                                <iframe src="https://maps.google.com/maps?q=<?php echo urlencode($tracking_data['current_location'] ?: $tracking_data['destination']); ?>&t=&z=10&ie=UTF8&iwloc=&output=embed" class="w-full h-full" frameborder="0" style="width: 100%; height: 100%; border:0;" allowfullscreen=""></iframe>
                            </div>
                        </div>

                        <!-- Sender & Receiver Profiles -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <!-- Sender -->
                            <div style="background-color: #ffffff; padding: 1.75rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                                <div class="kicker-badge" style="font-size: 0.75rem; padding: 0.2rem 0.65rem;">Origin Dispatcher</div>
                                <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: var(--secondary);">
                                    <?php echo htmlspecialchars($tracking_data['sender_name']); ?>
                                </h4>
                                <p style="font-size: 0.9rem; color: var(--text-body); margin-bottom: 0.35rem;">
                                    <i class="fa-solid fa-location-dot" style="color: var(--primary); margin-right: 0.35rem;"></i>
                                    <?php echo htmlspecialchars($tracking_data['sender_address']); ?>
                                </p>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                    <i class="fa-regular fa-envelope" style="color: var(--primary); margin-right: 0.35rem;"></i>
                                    Email: <?php echo htmlspecialchars($tracking_data['sender_email'] ?: 'N/A'); ?>
                                </p>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                                    <i class="fa-solid fa-phone" style="color: var(--primary); margin-right: 0.35rem;"></i>
                                    Contact: <?php echo htmlspecialchars($tracking_data['sender_contact']); ?>
                                </p>
                            </div>

                            <!-- Receiver -->
                            <div style="background-color: #ffffff; padding: 1.75rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                                <div class="kicker-badge" style="font-size: 0.75rem; padding: 0.2rem 0.65rem; background-color: #ecfdf5; color: #059669; border-color: #a7f3d0;">Destination Consignee</div>
                                <h4 style="font-size: 1.15rem; margin-bottom: 0.5rem; color: var(--secondary);">
                                    <?php echo htmlspecialchars($tracking_data['receiver_name']); ?>
                                </h4>
                                <p style="font-size: 0.9rem; color: var(--text-body); margin-bottom: 0.35rem;">
                                    <i class="fa-solid fa-location-dot" style="color: #059669; margin-right: 0.35rem;"></i>
                                    <?php echo htmlspecialchars($tracking_data['receiver_address']); ?>
                                </p>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                    <i class="fa-regular fa-envelope" style="color: #059669; margin-right: 0.35rem;"></i>
                                    Email: <?php echo htmlspecialchars($tracking_data['receiver_email'] ?: 'N/A'); ?>
                                </p>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                    <i class="fa-solid fa-phone" style="color: #059669; margin-right: 0.35rem;"></i>
                                    Contact: <?php echo htmlspecialchars($tracking_data['receiver_contact']); ?>
                                </p>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">
                                    <i class="fa-solid fa-flag-checkered" style="color: #059669; margin-right: 0.35rem;"></i>
                                    Destination: <strong><?php echo htmlspecialchars($tracking_data['destination']); ?></strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Milestones & Print Button -->
                    <div style="background-color: #ffffff; padding: 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm);">
                        <h4 style="font-size: 1.25rem; margin-bottom: 1.5rem; color: var(--secondary); display: flex; align-items: center; justify-content: space-between;">
                            <span>Shipment History</span>
                            <span style="font-size: 0.8rem; font-weight: 500; color: var(--text-muted);">Real-time feed</span>
                        </h4>

                        <div class="timeline-wrap">
                            <?php if (!empty($tracking_history)): ?>
                                <?php foreach($tracking_history as $index => $history_item): ?>
                                    <div class="timeline-node <?php echo ($index === 0) ? 'latest' : ''; ?>">
                                        <div style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 0.2rem;">
                                            <?php echo date('M d, Y - H:i', strtotime($history_item['date'] . ' ' . $history_item['time'])); ?>
                                        </div>
                                        <h5 style="font-size: 1.05rem; margin-bottom: 0.25rem; color: var(--secondary);">
                                            <?php echo htmlspecialchars($history_item['status']); ?>
                                        </h5>
                                        <p style="font-size: 0.88rem; color: var(--text-body); margin-bottom: 0.25rem;">
                                            <?php echo htmlspecialchars($history_item['note']); ?>
                                        </p>
                                        <div style="font-size: 0.8rem; color: var(--primary); font-weight: 600;">
                                            <i class="fa-solid fa-map-pin"></i> <?php echo htmlspecialchars($history_item['current_location']); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: var(--text-muted); font-size: 0.95rem;">Initial dispatch registered. Real-time checkpoints will populate as cargo advances.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Print Invoice Action (Preserves print.php?num=...) -->
                        <div style="margin-top: 2.5rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                            <a href="print.php?num=<?php echo urlencode($tracking_data['tracking_id']); ?>" target="_blank" class="btn-sky-outline" style="width: 100%;">
                                <i class="fa-solid fa-print"></i>
                                Print Official Waybill
                            </a>
                        </div>
                    </div>
                </div>

            <?php elseif ($error_message): ?>
                <!-- Error Notice Card -->
                <div style="background-color: #ffffff; padding: 4rem 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); text-align: center; max-width: 640px; margin: 0 auto; box-shadow: var(--shadow-sm);">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background-color: #fef2f2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1.5rem auto;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 style="font-size: 1.65rem; color: var(--secondary); margin-bottom: 0.75rem;">Shipment Not Found</h3>
                    <p style="color: var(--text-body); font-size: 1rem; line-height: 1.7; margin-bottom: 2rem;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </p>
                    <a href="../track.html" class="btn-sky">
                        Try Another Tracking Number
                    </a>
                </div>
            <?php else: ?>
                <!-- Default State: Search Prompt -->
                <div style="background-color: #ffffff; padding: 4rem 2rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); text-align: center; max-width: 640px; margin: 0 auto; box-shadow: var(--shadow-sm);">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.75rem; margin: 0 auto 1.5rem auto;">
                        <i class="fa-solid fa-compass"></i>
                    </div>
                    <h3 style="font-size: 1.65rem; color: var(--secondary); margin-bottom: 0.75rem;">Awaiting Tracking Code</h3>
                    <p style="color: var(--text-body); font-size: 1rem; line-height: 1.7; margin-bottom: 2rem;">
                        Please provide a valid cargo tracking number above to inspect live transport telemetry.
                    </p>
                    <a href="../index.html" class="btn-sky-outline">Return to Homepage</a>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <a href="../index.html" class="footer-brand-logo" aria-label="Universal Union Logistics">
                        <div class="footer-logo-card">
                            <img src="../assets/img/logo.png" alt="Universal Union Logistics" class="footer-logo-img">
                        </div>
                    </a>
                    <p style="line-height: 1.7; margin-bottom: 1.5rem; color: #94a3b8;">Your trusted global partner in supply chain logistics.</p>
                    <ul class="footer-contact-list">
                        <li class="footer-contact-item">
                            <i class="fa-regular fa-envelope"></i>
                            <a href="mailto:contact@universalunionlogistics.com">contact@universalunionlogistics.com</a>
                        </li>
                        <li class="footer-contact-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <span>24-26 Minories, London EC3N 1BQ, United Kingdom</span>
                        </li>
                        <li class="footer-contact-item">
                            <i class="fa-brands fa-whatsapp"></i>
                            <a href="https://wa.me/447594694996" target="_blank">+44 7594 694996</a>
                        </li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Our Services</h4>
                    <ul class="footer-links">
                        <li><a href="../services.html">Air Freight Services</a></li>
                        <li><a href="../services.html">Ocean Freight Shipping</a></li>
                        <li><a href="../services.html">Overland Road Transport</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="../about.html">About Us</a></li>
                        <li><a href="../services.html">Services</a></li>
                        <li><a href="../track.html">Track Shipment</a></li>
                        <li><a href="../faq.html">FAQs</a></li>
                        <li><a href="../contact.html">Contact Support</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Newsletter</h4>
                    <p style="color: #94a3b8; line-height: 1.6; margin-bottom: 1rem;">Subscribe to receive regular freight advisories and updates.</p>
                    <form class="footer-newsletter-form" onsubmit="event.preventDefault(); alert('Subscribed!');">
                        <input type="email" placeholder="Your email address" required>
                        <button type="submit"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom"><p>&copy; 2026 Universal Union Logistics. All Rights Reserved.</p></div>
        </div>
    </footer>

    <button class="scroll-to-top" aria-label="Scroll to top"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"></polyline></svg></button>
    <a href="https://wa.me/447594694996" target="_blank" class="whatsapp-float" aria-label="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp" style="font-size: 1.5rem;"></i>
    </a>
    <a href="https://t.me/universal_union545" target="_blank" class="telegram-float" aria-label="Chat on Telegram">
        <i class="fa-brands fa-telegram" style="font-size: 1.5rem;"></i>
    </a>
    <script src="../assets/js/sky-logistics.js"></script>
</body>
</html>
