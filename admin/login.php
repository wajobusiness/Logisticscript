<?php
session_start();
// Ensure the path to db.php is correct relative to the admin folder
include '../db.php';
$error = "";

$email = $password = "";

if (isset($_POST['login'])) {
    if (empty($_POST['email'])) {
        $error = "Admin email is required.";
    } else {
        $email = trim($_POST['email']);
    }

    if (empty($_POST['password'])) {
        $error = "Admin password is required.";
    } else {
        $password = trim($_POST['password']);
    }

    if (empty($error)) {
        $stmt = $con->prepare("SELECT email, password FROM admin WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($password == $row['password']) {
                $_SESSION['ADMIN_LOGIN'] = $row['email'];
                echo "<script>window.location.href = 'dashboard.php'</script>";
                exit();
            } else {
                $error = "Incorrect admin password.";
            }
        } else {
            $error = "Incorrect email address.";
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
    <title>Admin Login - Universal Union Logistics</title>
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
        }
        body {
            font-family: 'Yantramanav', sans-serif;
            background-color: var(--bg-dark-primary);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Lexend', sans-serif;
        }
        .form-input {
            background-color: #334155;
            border: 1px solid #475569;
        }
        .form-input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.4);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-md p-8 space-y-8 bg-slate-800 rounded-2xl shadow-2xl border border-slate-700">
        <div>
            <div class="flex justify-center items-center mb-6">
                <div class="bg-white p-3 rounded-2xl shadow-xl inline-flex items-center justify-center">
                    <img src="../assets/img/logo.png" alt="Universal Union Logistics" class="h-14 w-auto object-contain">
                </div>
            </div>
            <h2 class="mt-4 text-center text-2xl font-bold text-white font-heading">
                Admin Operations Portal
            </h2>
            <p class="text-center text-slate-400 text-sm mt-1">Sign in to manage global consignments and shipments</p>
        </div>

        <?php if ($error != ""): ?>
            <div class="bg-red-500/20 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg text-center text-sm" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        
        <form class="mt-8 space-y-5" action="login.php" method="POST">
            <div>
                <label for="email-address" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Admin Email</label>
                <div class="relative">
                    <i data-feather="mail" class="absolute left-3.5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                    <input id="email-address" name="email" type="email" autocomplete="email" required class="form-input w-full pl-11 pr-4 py-3 rounded-xl text-white placeholder-slate-400 text-sm focus:outline-none transition-all" placeholder="admin@mail.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <i data-feather="lock" class="absolute left-3.5 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400"></i>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="form-input w-full pl-11 pr-4 py-3 rounded-xl text-white placeholder-slate-400 text-sm focus:outline-none transition-all" placeholder="••••••••">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" name="login" class="w-full flex items-center justify-center gap-2 py-3.5 px-4 rounded-xl text-white font-semibold text-sm bg-sky-600 hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 shadow-lg shadow-sky-600/30 transition-all font-heading">
                    <i data-feather="log-in" class="h-4 w-4"></i>
                    <span>Sign In to Dashboard</span>
                </button>
            </div>
        </form>

        <div class="text-center pt-4 border-t border-slate-700">
            <a href="../index.html" class="text-xs text-sky-400 hover:text-sky-300 flex items-center justify-center gap-1">
                <i data-feather="arrow-left" class="h-3.5 w-3.5"></i>
                <span>Return to Public Website</span>
            </a>
        </div>
    </div>

    <script>
        feather.replace();
    </script>
</body>
</html>
