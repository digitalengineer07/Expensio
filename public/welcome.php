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
    <title>Expensio - Master Your Money</title>
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        purple: {
                            light: '#FAF5FF',
                            DEFAULT: '#7C3AED',
                            dark: '#5B21B6',
                        },
                        pink: {
                            light: '#FFF5F7',
                            DEFAULT: '#EC4899',
                        }
                    },
                    backgroundImage: {
                        'hero-gradient': 'linear-gradient(135deg, #FFF5F7 0%, #FFFFFF 50%, #F5F3FF 100%)',
                    }
                }
            }
        }
    </script>
    <style>
        .step-line::after {
            content: '';
            position: absolute;
            left: 20px;
            top: 40px;
            bottom: -20px;
            width: 2px;
            background: #E5E7EB;
            z-index: 0;
        }
        .step-item:last-child .step-line::after {
            display: none;
        }
    </style>
</head>
<body class="font-sans text-gray-900 bg-white overflow-x-hidden">

    <!-- Navbar -->
    <nav class="flex items-center justify-between px-6 py-6 max-w-7xl mx-auto w-full">
        <div class="flex items-center gap-2">
            <span class="text-2xl font-bold tracking-tight text-gray-900">Expensio</span>
        </div>
        
        <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
            <a href="#features" class="hover:text-purple transition-colors">Features</a>
            <a href="#how-it-works" class="hover:text-purple transition-colors">How it Works</a>
            <a href="#" class="hover:text-purple transition-colors">Pricing</a>
            <a href="#" class="hover:text-purple transition-colors">Blog</a>
        </div>
        
        <div class="flex items-center gap-4">
            <a href="login.php" class="text-sm font-semibold text-gray-900 hover:text-purple px-4 py-2">Sign In</a>
            <a href="signup.php" class="bg-purple text-white px-6 py-2.5 rounded-full text-sm font-semibold shadow-lg shadow-purple/20 hover:bg-purple-dark transition-all transform hover:-translate-y-0.5">Sign Up</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-20 pb-24 px-6 overflow-hidden">
        <!-- Premium Background Pattern -->
        <div class="absolute inset-0 opacity-[0.03] -z-10 pointer-events-none" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
        
        <!-- Background Blobs -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-[600px] h-[600px] bg-pink-100/40 rounded-full blur-[120px] -z-10"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-[400px] h-[400px] bg-purple-100/40 rounded-full blur-[100px] -z-10"></div>
        
        <div class="max-w-7xl mx-auto grid md:grid-cols-2 items-center gap-12 relative">
            <div class="space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-purple/5 border border-purple/10 text-purple text-xs font-bold tracking-wide uppercase">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-purple"></span>
                    </span>
                    New: Smart AI Receipts scanning
                </div>
                <h1 class="text-5xl md:text-7xl font-bold leading-[1.1] text-gray-900 tracking-tight">
                    Master Your Money <br> with <span class="bg-clip-text text-transparent bg-gradient-to-r from-purple via-pink to-purple">Expensio</span>
                </h1>
                <p class="text-lg text-gray-600 max-w-md leading-relaxed">
                    Track every penny, set smart budgets, and reach your financial goals faster with our intuitive expense management platform.
                </p>
                <div class="flex flex-wrap items-center gap-6">
                    <a href="signup.php" class="bg-gray-900 text-white px-8 py-4 rounded-xl font-semibold shadow-xl hover:bg-gray-800 transition-all transform hover:-translate-y-1 active:scale-95">Create An Account</a>
                    <button class="flex items-center gap-3 group">
                        <div class="w-12 h-12 rounded-full border border-gray-200 flex items-center justify-center text-purple text-xl group-hover:bg-purple group-hover:text-white transition-all shadow-sm">
                            <i class='bx bx-play'></i>
                        </div>
                        <span class="font-semibold text-gray-900 group-hover:text-purple transition-colors">See How It Works?</span>
                    </button>
                </div>
            </div>
            
            <div class="relative">
                <div class="relative z-10 animate-[bounce_6s_ease-in-out_infinite]">
                    <img src="https://img.freepik.com/free-vector/saving-concept-illustration_114360-1513.jpg?t=st=1716300000&exp=1716303600&hmac=placeholder" alt="3D character saving money in a piggy bank" class="w-full mix-blend-multiply rounded-3xl">
                    <!-- Floating Data Card (Visual Polish) -->
                    <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-2xl shadow-2xl animate-[float_4s_ease-in-out_infinite] hidden lg:block">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center"><i class='bx bx-trending-up'></i></div>
                            <div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase">Monthly Save</div>
                                <div class="text-lg font-bold text-gray-900">₹2,450.00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section id="features" class="py-24 px-6 bg-white">
        <div class="max-w-7xl mx-auto text-center space-y-4 mb-20">
            <h2 class="text-4xl font-bold text-gray-900 tracking-tight">Why You Choose Us?</h2>
            <p class="text-gray-500 max-w-2xl mx-auto">The smart way to handle your personal and society expenditures with built-in financial intelligence.</p>
        </div>
        
        <div class="max-w-7xl mx-auto grid md:grid-cols-3 gap-16">
            <!-- Item 1 -->
            <div class="text-center group">
                <div class="w-24 h-24 bg-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl shadow-blue-500/5 group-hover:scale-110 transition-transform duration-500">
                    <i class='bx bxs-grid-alt text-4xl text-blue-500'></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Smart Categorization</h3>
                <p class="text-gray-500 text-sm leading-relaxed px-4">Auto-sort your spending into meaningful buckets like food, bills, and construction costs automatically.</p>
            </div>
            <!-- Item 2 -->
            <div class="text-center group">
                <div class="w-24 h-24 bg-pink-50 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl shadow-pink-500/5 group-hover:scale-110 transition-transform duration-500">
                    <i class='bx bxs-bolt text-4xl text-pink-500'></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Real-Time Tracking</h3>
                <p class="text-gray-500 text-sm leading-relaxed px-4">Monitor your expenses as they happen with instant sync and live dashboard updates across all devices.</p>
            </div>
            <!-- Item 3 -->
            <div class="text-center group">
                <div class="w-24 h-24 bg-purple-50 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl shadow-purple-500/5 group-hover:scale-110 transition-transform duration-500">
                    <i class='bx bxs-shield-check text-4xl text-purple-500'></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Bank-Level Security</h3>
                <p class="text-gray-500 text-sm leading-relaxed px-4">Your financial data is encrypted and protected with industry-standard security protocols to keep you safe.</p>
            </div>
        </div>
    </section>

    <!-- How Does It Work -->
    <section id="how-it-works" class="py-24 px-6 relative overflow-hidden">
        <div class="absolute -z-10 top-1/2 left-0 w-[500px] h-[500px] bg-blue-50/50 rounded-full blur-[100px]"></div>
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 items-center gap-20">
            <div class="relative order-2 lg:order-1">
                <div class="bg-gradient-to-br from-pink-200 to-purple-200 rounded-[3rem] p-1 scale-95 lg:scale-100">
                    <img src="https://img.freepik.com/free-vector/business-team-managing-tasks-with-kanban-board-work-process-organization-software-engineering-workflow-management-tools-concept_335657-2354.jpg" alt="3D character using a laptop" class="rounded-[2.9rem] w-full object-cover">
                </div>
            </div>
            
            <div class="space-y-12 order-1 lg:order-2">
                <div class="space-y-4">
                    <h2 class="text-4xl font-bold text-gray-900 tracking-tight">How Does It Works?</h2>
                    <p class="text-gray-500 leading-relaxed max-w-lg">Take control of your finances in three simple, automated steps that save you hours of manual tracking.</p>
                </div>
                
                <div class="space-y-16 relative">
                    <!-- Step 1 -->
                    <div class="flex gap-8 step-item relative">
                        <div class="relative z-10 w-12 h-12 rounded-2xl bg-white border-2 border-pink-500 flex items-center justify-center text-pink-500 font-bold shadow-lg shadow-pink-500/10 step-line shrink-0">1</div>
                        <div class="space-y-2">
                            <h4 class="font-bold text-xl">Connect Your Accounts</h4>
                            <p class="text-gray-500 leading-relaxed">Securely link your bank cards or manually record your transactions in our easy-to-use interface.</p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex gap-8 step-item relative">
                        <div class="relative z-10 w-12 h-12 rounded-2xl bg-pink-500 flex items-center justify-center text-white font-bold shadow-xl shadow-pink-500/20 step-line shrink-0">2</div>
                        <div class="space-y-2">
                            <h4 class="font-bold text-xl">Track & Categorize</h4>
                            <p class="text-gray-500 leading-relaxed">Watch your expenses get organized automatically into clear, visual groups for easy management.</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex gap-8 step-item relative">
                        <div class="relative z-10 w-12 h-12 rounded-2xl bg-white border-2 border-pink-500 flex items-center justify-center text-pink-500 font-bold shadow-lg shadow-pink-500/10 shrink-0">3</div>
                        <div class="space-y-2">
                            <h4 class="font-bold text-xl">Analyze & Save</h4>
                            <p class="text-gray-500 leading-relaxed">Use our powerful insights to identify trends, cut unnecessary costs, and grow your savings exponentially.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Integrations Strip -->
    <section class="py-20 px-6 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <p class="text-center text-xs font-bold text-gray-400 uppercase tracking-widest mb-12">Trusted by major financial institutions</p>
            <div class="flex flex-wrap justify-center items-center gap-12 md:gap-24 opacity-50 grayscale hover:grayscale-0 transition-all duration-700">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/1d/Plaid_logo.svg/2560px-Plaid_logo.svg.png" alt="Plaid" class="h-6">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/2560px-PayPal.svg.png" alt="PayPal" class="h-6">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Stripe_Logo%2C_revised_2016.svg/2560px-Stripe_Logo%2C_revised_2016.svg.png" alt="Stripe" class="h-6">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6e/QuickBooks_logo.svg/2560px-QuickBooks_logo.svg.png" alt="QuickBooks" class="h-6">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e0/Visa_2021.svg/2560px-Visa_2021.svg.png" alt="Visa" class="h-6">
            </div>
        </div>
    </section>

    <!-- App Download -->
    <section class="py-32 px-6 bg-white overflow-hidden relative">
        <div class="absolute right-0 top-0 -z-10 w-[600px] h-[600px] bg-purple-50 rounded-full blur-[120px]"></div>
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 items-center gap-20">
            <div class="space-y-10">
                <h2 class="text-4xl md:text-6xl font-bold text-gray-900 leading-tight tracking-tight">
                    Take Your Budget <br> Anywhere
                </h2>
                <p class="text-gray-600 text-lg leading-relaxed max-w-lg">
                    Download the Expensio app to log cash expenses, scan receipts with AI, and check your remaining budget on the go.
                </p>
                <div class="flex flex-wrap gap-6">
                    <button class="flex items-center gap-4 bg-gray-900 text-white px-8 py-4 rounded-2xl hover:bg-gray-800 shadow-2xl hover:-translate-y-1 transition-all">
                        <i class='bx bxl-play-store text-4xl'></i>
                        <div class="text-left">
                            <div class="text-[10px] uppercase font-bold tracking-widest opacity-60">Get it on</div>
                            <div class="text-xl font-bold leading-none">Google Play</div>
                        </div>
                    </button>
                    <button class="flex items-center gap-4 bg-gray-900 text-white px-8 py-4 rounded-2xl hover:bg-gray-800 shadow-2xl hover:-translate-y-1 transition-all">
                        <i class='bx bxl-apple text-4xl'></i>
                        <div class="text-left">
                            <div class="text-[10px] uppercase font-bold tracking-widest opacity-60">Download on the</div>
                            <div class="text-xl font-bold leading-none">App Store</div>
                        </div>
                    </button>
                </div>
            </div>
            
            <div class="relative flex justify-center lg:justify-end">
                <div class="relative group">
                    <div class="absolute inset-0 bg-purple/20 rounded-[3rem] blur-[50px] group-hover:blur-[80px] transition-all -z-10"></div>
                    <img src="https://img.freepik.com/free-vector/realistic-phone-different-view-points_52683-50074.jpg" alt="Expensio App Mockup" class="w-full max-w-md drop-shadow-2xl hover:scale-105 transition-transform duration-700">
                </div>
            </div>
        </div>
    </section>

    <!-- Great Features -->
    <section class="py-24 px-6 bg-gray-50">
        <div class="max-w-7xl mx-auto text-center space-y-4 mb-20 px-6">
            <h2 class="text-4xl font-bold text-gray-900 tracking-tight">Great Features of Expensio</h2>
            <p class="text-gray-500 max-w-2xl mx-auto">Discover the powerful, data-driven tools we built to simplify your financial life.</p>
        </div>
        
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-10 rounded-[2.5rem] space-y-8 hover:translate-y-[-12px] transition-all duration-500 border border-gray-100 shadow-sm hover:shadow-2xl group">
                <div class="w-16 h-16 bg-pink-50 rounded-2xl flex items-center justify-center text-pink-500 text-3xl group-hover:bg-pink-500 group-hover:text-white transition-all duration-500">
                    <i class='bx bx-pie-chart-alt-2'></i>
                </div>
                <h4 class="text-xl font-bold">Budget Planning</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Set monthly limits for categories and get notified when you're close to exceeding them.</p>
            </div>
            <!-- Feature 2 -->
            <div class="bg-white p-10 rounded-[2.5rem] space-y-8 hover:translate-y-[-12px] transition-all duration-500 border border-gray-100 shadow-sm hover:shadow-2xl group">
                <div class="w-16 h-16 bg-purple-50 rounded-2xl flex items-center justify-center text-purple text-3xl group-hover:bg-purple group-hover:text-white transition-all duration-500">
                    <i class='bx bx-camera'></i>
                </div>
                <h4 class="text-xl font-bold">Receipt Scanning</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Quickly scan and digitize paper receipts using our advanced AI-powered OCR technology.</p>
            </div>
            <!-- Feature 3 -->
            <div class="bg-white p-10 rounded-[2.5rem] space-y-8 hover:translate-y-[-12px] transition-all duration-500 border border-gray-100 shadow-sm hover:shadow-2xl group">
                <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-500 text-3xl group-hover:bg-orange-500 group-hover:text-white transition-all duration-500">
                    <i class='bx bx-file'></i>
                </div>
                <h4 class="text-xl font-bold">Expense Reports</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Generate beautiful, detailed PDF reports for tax filing or society meetings in seconds.</p>
            </div>
            <!-- Feature 4 -->
            <div class="bg-white p-10 rounded-[2.5rem] space-y-8 hover:translate-y-[-12px] transition-all duration-500 border border-gray-100 shadow-sm hover:shadow-2xl group">
                <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center text-green-500 text-3xl group-hover:bg-green-500 group-hover:text-white transition-all duration-500">
                    <i class='bx bx-bullseye'></i>
                </div>
                <h4 class="text-xl font-bold">Goal Setting</h4>
                <p class="text-gray-500 text-sm leading-relaxed">Define your financial targets and let us help you build a visual path to achieve them.</p>
            </div>
        </div>
    </section>

    <!-- Pre-Footer CTA -->
    <section class="max-w-7xl mx-auto px-6 mb-32 -mt-10">
        <div class="bg-gradient-to-r from-purple via-purple-dark to-purple p-12 md:p-24 rounded-[4rem] text-center text-white space-y-12 shadow-[0_50px_100px_rgba(124,58,237,0.3)] relative overflow-hidden">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-[100px] -mr-48 -mt-48"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-pink-500/10 rounded-full blur-[100px] -ml-32 -mb-32"></div>
            
            <div class="relative z-10 space-y-6">
                <h2 class="text-4xl md:text-6xl font-bold leading-tight tracking-tight">Ready to take control of <br> your finances?</h2>
                <p class="text-purple-light opacity-80 max-w-xl mx-auto text-lg">Subscribe for top-tier financial tips and master your budgeting journey with Expensio.</p>
            </div>
            
            <form action="#" class="max-w-md mx-auto relative z-10">
                <div class="flex p-3 bg-white rounded-[2rem] shadow-2xl">
                    <input type="email" placeholder="Enter Your Email" class="flex-1 px-6 py-4 text-gray-900 outline-none text-base font-medium rounded-2xl">
                    <button type="submit" class="bg-purple text-white px-10 py-4 rounded-2x; font-bold hover:bg-purple-dark transition-all transform active:scale-95 shadow-lg shadow-purple/20">Subscribe</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Premium Footer -->
    <footer class="bg-white px-6 pt-24 pb-12 border-t border-gray-100 relative overflow-hidden">
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16 lg:gap-24 mb-20">
            <!-- Brand Column -->
            <div class="space-y-8">
                <div class="text-3xl font-bold tracking-tight text-gray-900">Expensio</div>
                <p class="text-gray-500 leading-relaxed text-sm">
                    Mastering money shouldn't be hard. We build tools that make tracking and saving as easy as a single tap.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-12 h-12 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-purple hover:text-white hover:scale-110 shadow-sm transition-all duration-300"><i class='bx bxl-facebook text-xl'></i></a>
                    <a href="#" class="w-12 h-12 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-purple hover:text-white hover:scale-110 shadow-sm transition-all duration-300"><i class='bx bxl-twitter text-xl'></i></a>
                    <a href="#" class="w-12 h-12 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-purple hover:text-white hover:scale-110 shadow-sm transition-all duration-300"><i class='bx bxl-linkedin text-xl'></i></a>
                    <a href="#" class="w-12 h-12 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-purple hover:text-white hover:scale-110 shadow-sm transition-all duration-300"><i class='bx bxl-instagram text-xl'></i></a>
                </div>
            </div>

            <!-- Product Column -->
            <div class="space-y-6">
                <h4 class="text-sm font-bold uppercase tracking-widest text-gray-900">Product</h4>
                <ul class="space-y-4 text-sm text-gray-500">
                    <li><a href="#" class="hover:text-purple transition-colors">Features</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">How it Works</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Pricing</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Integrations</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Mobile App</a></li>
                </ul>
            </div>

            <!-- Company Column -->
            <div class="space-y-6">
                <h4 class="text-sm font-bold uppercase tracking-widest text-gray-900">Company</h4>
                <ul class="space-y-4 text-sm text-gray-500">
                    <li><a href="#" class="hover:text-purple transition-colors">About Us</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Careers</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Contact</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Blog</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Partner Program</a></li>
                </ul>
            </div>

            <!-- Legal Column -->
            <div class="space-y-6">
                <h4 class="text-sm font-bold uppercase tracking-widest text-gray-900">Legal</h4>
                <ul class="space-y-4 text-sm text-gray-500">
                    <li><a href="#" class="hover:text-purple transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Cookie Policy</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Security</a></li>
                    <li><a href="#" class="hover:text-purple transition-colors">Help Center</a></li>
                </ul>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="max-w-7xl mx-auto pt-12 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-sm text-gray-400 font-medium tracking-tight">
                © 2026 Expensio Global Inc. Designed with care for residents everywhere.
            </p>
            <div class="flex items-center gap-2 text-xs font-bold text-gray-400 uppercase tracking-widest">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                All Systems Operational
            </div>
        </div>
    </footer>

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
    </style>
</body>
</html>
