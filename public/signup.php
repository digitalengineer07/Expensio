<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Middleware\Session;

if (Session::isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Expensio</title>
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

        /* Glass styles for select element */
        select.auth-input option {
            background: #ffffff;
            color: #1F2024;
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

    <!-- Signup Card -->
    <div class="auth-card w-full max-w-[400px] p-6 rounded-[28px] z-10 mx-6 animate-[fadeIn_0.5s_ease-out]">
        
        <!-- Header Section -->
        <div class="text-center mb-4 relative z-10">
            <div class="w-10 h-10 auth-button rounded-xl flex items-center justify-center mx-auto mb-3 shadow-sm text-[#7C3AED]">
                <i class='bx bx-user-plus text-xl'></i>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-1 tracking-tight">Create your account</h1>
        </div>

        <!-- Signup Form -->
        <form action="../app/Controllers/AuthController.php?action=signup" method="POST" class="space-y-3">
            <!-- Full Name / Username Field -->
            <div class="relative group">
                <i class='bx bx-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-[#7C3AED] transition-colors z-10'></i>
                <input type="text" name="username" placeholder="Username" required autocomplete="username"
                       class="w-full pl-11 pr-4 py-3 border border-gray-200 focus:bg-white rounded-xl outline-none focus:border-[#7C3AED] transition-all text-sm text-gray-900 placeholder:text-gray-400">
            </div>

            <!-- Email Field -->
            <div class="relative group">
                <i class='bx bx-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-[#7C3AED] transition-colors z-10'></i>
                <input type="email" name="email" placeholder="Email" required autocomplete="email"
                       class="w-full pl-11 pr-4 py-3 border border-gray-200 focus:bg-white rounded-xl outline-none focus:border-[#7C3AED] transition-all text-sm text-gray-900 placeholder:text-gray-400">
            </div>

            <!-- Role Selection -->
            <div class="relative group">
                <i class='bx bx-id-card absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-[#7C3AED] transition-colors z-10'></i>
                <select name="role_id" required
                        class="w-full pl-11 pr-10 py-3 border border-gray-200 focus:bg-white rounded-xl outline-none focus:border-[#7C3AED] transition-all text-sm text-gray-900 appearance-none cursor-pointer">
                    <option value="" disabled selected>Select Role</option>
                    <option value="2">Student</option>
                    <option value="3">Civil Engineer</option>
                    <option value="1">Other / Admin (Limited)</option>
                </select>
                <i class='bx bx-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none z-10'></i>
            </div>

            <!-- Password Field -->
            <div class="relative group">
                <i class='bx bx-lock-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-lg group-focus-within:text-[#7C3AED] transition-colors z-10'></i>
                <input type="password" id="password" name="password" placeholder="Password" required autocomplete="new-password"
                       class="w-full pl-11 pr-11 py-3 border border-gray-200 focus:bg-white rounded-xl outline-none focus:border-[#7C3AED] transition-all text-sm text-gray-900 placeholder:text-gray-400">
                <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors z-10">
                    <i class='bx bx-hide text-lg' id="toggleIcon"></i>
                </button>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="w-full bg-[#7C3AED] text-white py-3.5 rounded-xl font-bold hover:opacity-90 transition-all shadow-[0_4px_12px_rgba(124,58,237,0.3)] active:scale-[0.98] mt-2">
                Register Now
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200/50"></div>
            </div>
            <div class="relative flex justify-center text-[11px]">
                <span class="bg-white/0 px-3 text-gray-400 italic">Or sign up with</span>
            </div>
        </div>

        <!-- Social Signup -->
        <div class="grid grid-cols-3 gap-3">
            <a href="google-callback.php" class="flex items-center justify-center p-2 rounded-xl auth-button hover:bg-gray-50 transition-all shadow-sm group">
                <i class='bx bxl-google text-2xl text-red-500 group-hover:scale-110 transition-transform'></i>
            </a>
            <button type="button" class="flex items-center justify-center p-2 rounded-xl auth-button hover:bg-gray-50 transition-all shadow-sm group">
                <i class='bx bxl-facebook-circle text-2xl text-blue-600 group-hover:scale-110 transition-transform'></i>
            </button>
            <button type="button" class="flex items-center justify-center p-2 rounded-xl auth-button hover:bg-gray-50 transition-all shadow-sm group">
                <i class='bx bxl-apple text-2xl text-gray-900 group-hover:scale-110 transition-transform'></i>
            </button>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-[12px] text-gray-500">
                Already have an account? <a href="login.php" class="font-bold text-expensio-dark hover:underline">Sign in</a>
            </p>
        </div>
    </div>

    <!-- Toggle Password Visibility Script -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('bx-hide', 'bx-show');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('bx-show', 'bx-hide');
            }
        }
    </script>
</body>
</html>
