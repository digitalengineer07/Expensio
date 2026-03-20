<?php
require_once __DIR__ . '/../config/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Expensio</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Poppins & Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        'expensio-dark': '#1F2024',
                        'expensio-blue-light': '#E8F1FF',
                    },
                }
            }
        }
    </script>
    <style>
        .auth-card {
            background: #ffffff;
            border: 1px solid #F3F4F6;
            box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.05);
        }

        .auth-input {
            background: #F8F9FD !important;
            border: 1px solid #F3F4F6 !important;
        }

        .auth-button {
            background: #ffffff !important;
            border: 1px solid #F3F4F6 !important;
        }
        
        .bg-expensio {
            background-color: #F8F9FD;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-expensio min-h-screen flex items-center justify-center py-10 relative overflow-x-hidden font-sans">

    <!-- Global Brand Logo -->
    <div class="fixed top-6 left-6 md:top-8 md:left-8 flex items-center gap-2 z-50">
        <div class="w-8 h-8 bg-[#7C3AED] rounded-lg flex items-center justify-center transform rotate-12 shadow-sm">
            <i class='bx bxs-diamond text-white text-lg'></i>
        </div>
        <span class="text-xl font-bold tracking-tight text-gray-900 font-display hidden sm:inline">Expensio</span>
    </div>

    <!-- Forgot Password Card -->
    <div class="auth-card w-full max-w-[400px] p-8 rounded-[28px] z-10 mx-6 animate-[fadeIn_0.5s_ease-out]">
        
        <!-- Header Section -->
        <div class="text-center mb-6 relative z-10">
            <div class="w-12 h-12 auth-button rounded-xl flex items-center justify-center mx-auto mb-4 shadow-sm text-[#7C3AED]">
                <i class='bx bx-key text-2xl'></i>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-2 tracking-tight">Forgot Password?</h1>
            <p class="text-[13px] text-gray-500 leading-relaxed px-4">
                Enter your email and we'll send you instructions to reset your password.
            </p>
        </div>

        <!-- Form -->
        <form action="#" method="POST" class="space-y-4">
            <div class="relative group">
                <i class='bx bx-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-[#7C3AED] transition-colors'></i>
                <input type="email" name="email" placeholder="Email" required
                       class="w-full pl-11 pr-4 py-3 border border-gray-200 focus:bg-white rounded-xl outline-none focus:border-[#7C3AED] transition-all text-sm text-gray-900 placeholder:text-gray-400">
            </div>

            <button type="submit" 
                    class="w-full bg-[#7C3AED] text-white py-3.5 rounded-xl font-bold hover:opacity-90 transition-all shadow-[0_4px_12px_rgba(124,58,237,0.3)] active:scale-[0.98]">
                Reset Password
            </button>
        </form>

        <!-- Footer -->
        <div class="text-center mt-8">
            <a href="login.php" class="text-sm font-bold text-gray-500 hover:text-[#7C3AED] transition-colors flex items-center justify-center gap-2">
                <i class='bx bx-arrow-back'></i> Back to Login
            </a>
        </div>
    </div>
</body>
</html>
