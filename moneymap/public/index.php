<!DOCTYPE html>
<html lang="en" style="scroll-behavior: smooth;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyMap - Master Your Personal Finance | Expense Tracking, Budgeting, & Financial Tools</title>
    <meta name="description" content="MoneyMap is your all-in-one personal finance platform. Effortlessly track expenses, set budgets, calculate loans, convert currencies, and achieve financial freedom with our powerful tools.">
    <meta name="keywords" content="personal finance, expense tracker, budgeting app, loan calculator, currency converter, financial freedom, money management, savings planner, money map">
    <meta name="author" content="MoneyMap Solutions">
    <link rel="canonical" href="https://www.moneymap.com/">
    
    <!-- Open Graph Meta Tags for Social Sharing -->
    <meta property="og:title" content="MoneyMap - Master Your Personal Finance">
    <meta property="og:description" content="Effortlessly track expenses, set budgets, calculate loans, convert currencies, and achieve financial freedom.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.moneymap.com/">
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        // Tailwind Custom Configuration
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        // Custom Primary Color Palette (Green/Teal)
                        'primary': {
                            50: '#ECFDF5', 100: '#D1FAE5', 200: '#A7F3D0', 300: '#6EE7B7', 400: '#34D399',
                            500: '#10B981', 600: '#059669', 700: '#047857', 800: '#065F46', 900: '#064E3B',
                        },
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Base styles for the animation classes (hidden state) */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
            will-change: opacity, transform;
        }
        /* Special starting positions for Hero section elements */
        .slide-in-left { transform: translateX(-80px); }
        .slide-in-right { transform: translateX(80px); }

        /* Final, visible state */
        .is-visible {
            opacity: 1;
            transform: translateX(0) translateY(0);
        }

        /* Keyframes for a gentle float effect for background shapes */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(1deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .floating-shape { animation: float 8s ease-in-out infinite; }

        /* Gradient Text for Titles */
        .gradient-text {
            background: linear-gradient(135deg, #059669 30%, #34D399 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        /* Feature Card Border Glow and Hover Effect */
        .feature-card {
            position: relative;
            z-index: 10;
            transition: all 0.5s cubic-bezier(0.2, 0, 0, 1);
            transform: perspective(1000px) rotateX(0deg);
            overflow: hidden;
        }
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--card-color-start) 0%, var(--card-color-end) 100%);
            transition: all 0.5s ease;
            z-index: 20;
        }
        .feature-card:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px) perspective(1000px) rotateX(1deg) scale(1.02);
        }
        .feature-card:hover::before {
            height: 100%;
            opacity: 0.1;
            filter: blur(8px);
        }
        .feature-card > * { position: relative; z-index: 30; }

        /* Styles for FAQ Accordion Icons */
        .accordion-icon { transition: transform 0.3s ease-in-out; }
        .accordion-icon.open { transform: rotate(180deg); }

        /* Nav Link Underline Hover Effect */
        .nav-link {
            position: relative;
        }
        .nav-link:hover::after {
            transform: scaleX(1);
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #059669;
            transform: scaleX(0);
            transition: transform 0.3s ease-in-out;
        }
        
        /* Ensure mobile menu is visible */
        #mobile-menu {
            display: none;
        }
        
        #mobile-menu.show {
            display: block;
        }
    </style>
</head>
<body class="bg-primary-50 font-sans antialiased text-slate-700">
    
    <header class="sticky top-0 z-50" role="banner">
        <nav id="navbar" class="bg-white shadow-lg w-full transition-all duration-300 ease-in-out" role="navigation" aria-label="Main Navigation">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-20 items-center">
                    
                    <a href="#hero" class="flex-shrink-0 flex items-center group" aria-label="MoneyMap Home">
                        <svg class="h-8 w-8 text-primary-600 mr-2 transform group-hover:scale-110 transition duration-300" 
                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>

                        <span class="text-3xl font-extrabold text-gray-800 tracking-tighter">
                            Money<span class="text-primary-600">Map</span>
                        </span>
                    </a>

                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#features" class="text-gray-600 hover:text-primary-600 font-medium transition duration-300 nav-link">
                            <i class="fa-solid fa-rocket mr-1 text-primary-500" aria-hidden="true"></i> Features
                        </a>
                        <a href="#how-it-works" class="text-gray-600 hover:text-primary-600 font-medium transition duration-300 nav-link">
                            <i class="fa-solid fa-cogs mr-1 text-primary-500" aria-hidden="true"></i> How It Works
                        </a>
                        <a href="#faq" class="text-gray-600 hover:text-primary-600 font-medium transition duration-300 nav-link">
                            <i class="fa-solid fa-question-circle mr-1 text-primary-500" aria-hidden="true"></i> FAQ
                        </a>
                        
                        <div class="flex space-x-4 ml-4">
                            <a href="login.php" class="px-5 py-2 font-semibold rounded-full transition duration-300 ease-in-out text-primary-600 border-2 border-primary-600 hover:bg-primary-600 hover:text-white hover:shadow-xl hover:shadow-primary-300/50 hover:scale-105">
                                Login
                            </a>
                            <a href="signup.php" class="px-5 py-2 font-semibold rounded-full transition duration-300 ease-in-out bg-primary-600 text-white shadow-lg shadow-primary-500/50 hover:bg-primary-700 hover:shadow-2xl hover:scale-[1.05] transform">
                                Sign Up
                            </a>
                        </div>
                    </div>

                    <div class="md:hidden flex items-center">
                        <button id="mobile-menu-btn" class="text-gray-700 hover:text-primary-600 focus:outline-none p-2 rounded-md transition duration-300" aria-label="Toggle mobile menu" aria-expanded="false">
                            <i class="fa-solid fa-bars text-xl" id="menu-icon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div id="mobile-menu" class="hidden md:hidden bg-white shadow-2xl border-t border-gray-100 transition-all duration-300 ease-in-out">
                <div class="px-4 pt-2 pb-4 space-y-2 sm:px-6">
                    <a href="#features" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-200 mobile-nav-link"><i class="fa-solid fa-rocket mr-2" aria-hidden="true"></i>Features</a>
                    <a href="#how-it-works" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-200 mobile-nav-link"><i class="fa-solid fa-cogs mr-2" aria-hidden="true"></i>How It Works</a>
                    <a href="#faq" class="block px-3 py-2 rounded-lg text-base font-medium text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-200 mobile-nav-link"><i class="fa-solid fa-question-circle mr-2" aria-hidden="true"></i>FAQ</a>
                    <hr class="my-2 border-primary-100">
                    <a href="login.php" class="block px-5 py-2 rounded-full text-base font-medium text-primary-600 border border-primary-600 hover:bg-primary-50 transition duration-200 text-center mobile-nav-link"><i class="fa-solid fa-sign-in-alt mr-2" aria-hidden="true"></i>Login</a>
                    <a href="signup.php" class="block px-5 py-2 rounded-full text-base font-medium bg-primary-600 text-white hover:bg-primary-700 transition duration-200 text-center shadow-md mt-2 mobile-nav-link"><i class="fa-solid fa-user-plus mr-2" aria-hidden="true"></i>Sign Up</a>
                </div>
            </div>
        </nav>
    </header>
    
    <main role="main">
        <section id="hero" class="pt-20 pb-24 sm:py-32 bg-gradient-to-br from-primary-50 to-primary-100 overflow-hidden relative" role="region" aria-labelledby="hero-title">
            <div class="absolute top-10 left-1/4 w-32 h-32 bg-primary-300 rounded-full opacity-20 filter blur-xl floating-shape" style="animation-delay: 0s;"></div>
            <div class="absolute bottom-20 right-1/4 w-48 h-48 bg-primary-400 rounded-lg opacity-10 filter blur-xl floating-shape" style="animation-delay: 2s; transform: rotate(15deg);"></div>
            <div class="absolute top-1/2 left-0 w-24 h-24 bg-primary-200 rounded-full opacity-30 filter blur-xl floating-shape" style="animation-delay: 4s;"></div>

            <div class="container mx-auto px-4 max-w-6xl relative z-10">
                <div class="flex flex-col lg:flex-row items-center justify-between gap-12 text-center lg:text-left">
                    
                    <div class="w-full lg:w-1/2 max-w-xl slide-in-left animate-on-scroll">
                        <h1 id="hero-title" class="text-5xl sm:text-6xl font-extrabold leading-tight mb-4 text-slate-800">
                            Map Your <span class="text-primary-600">Financial Journey</span> With Clarity
                        </h1>
                        <p class="text-xl text-slate-600 mb-8">
                            <strong>MoneyMap</strong> is your modern all-in-one personal finance platform. Effortlessly <strong>track expenses</strong>, calculate loans, convert currencies, and visualize your path to <strong>financial freedom</strong>.
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-4 mb-10">
                            <a href="#" class="px-8 py-3 font-bold text-lg rounded-full transition duration-300 ease-in-out bg-primary-600 text-white shadow-xl shadow-primary-500/50 hover:bg-primary-700 hover:shadow-2xl hover:scale-[1.05] transform" aria-label="Get Started Free">
                                Get Started Free 
                            </a>
                            <a href="#" class="px-8 py-3 font-bold text-lg rounded-full transition duration-300 ease-in-out bg-transparent text-primary-600 border-2 border-primary-600 hover:bg-primary-600 hover:text-white hover:shadow-lg hover:scale-105" aria-label="Watch Demo Video">
                                <i class="fa-solid fa-play-circle mr-2" aria-hidden="true"></i> Watch Demo
                            </a>
                        </div>
                        <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-6 text-sm font-medium text-slate-700">
                            <p class="animate-on-scroll" style="transition-delay: 0.1s;"><i class="fa-solid fa-users text-primary-600 mr-2" aria-hidden="true"></i> 10,000+ Happy Users</p>
                            <p class="animate-on-scroll" style="transition-delay: 0.2s;"><i class="fa-solid fa-rupee-sign text-primary-600 mr-2" aria-hidden="true"></i> Tracked Over ₹5 Crore in Expenses</p>
                        </div>
                    </div>

                    <div class="w-full lg:w-1/2 flex justify-center slide-in-right animate-on-scroll">
                        <article class="w-full max-w-md bg-white rounded-xl shadow-2xl p-6 transition duration-500 ease-in-out transform hover:rotate-0 hover:scale-[1.02] perspective-1000 [transform:rotateY(-8deg)] hover:[transform:rotateY(0deg)] border-t-4 border-primary-600" aria-label="Financial Dashboard Mockup">
                            <header class="flex justify-between items-center mb-6">
                                <h2 class="font-bold text-xl text-slate-800 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-primary-500" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    Financial Overview
                                </h2>
                            </header>
                            <div class="bg-slate-100 rounded-lg p-4 h-52 mb-6 flex items-end gap-3 shadow-inner" aria-label="Monthly Financial Performance Chart">
                                <div class="flex-1 min-w-3 h-3/4 bg-primary-600 rounded-t-md transition duration-300 hover:opacity-100 opacity-80 hover:translate-y-[-10px] shadow-lg" title="January"></div>
                                <div class="flex-1 min-w-3 h-[85%] bg-blue-500 rounded-t-md transition duration-300 hover:opacity-100 opacity-80 hover:translate-y-[-10px] shadow-lg" title="February"></div>
                                <div class="flex-1 min-w-3 h-1/2 bg-amber-500 rounded-t-md transition duration-300 hover:opacity-100 opacity-80 hover:translate-y-[-10px] shadow-lg" title="March"></div>
                                <div class="flex-1 min-w-3 h-[95%] bg-violet-600 rounded-t-md transition duration-300 hover:opacity-100 opacity-80 hover:translate-y-[-10px] shadow-lg" title="April"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white rounded-lg p-4 text-center border border-primary-200 transition duration-300 hover:border-primary-600 hover:shadow-xl hover:scale-[1.02]">
                                    <div class="font-extrabold text-3xl text-primary-700">₹42,500</div>
                                    <div class="text-xs text-slate-500 font-medium mt-1">Monthly Income</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 text-center border border-primary-200 transition duration-300 hover:border-primary-600 hover:shadow-xl hover:scale-[1.02]">
                                    <div class="font-extrabold text-3xl text-red-500">₹28,900</div>
                                    <div class="text-xs text-slate-500 font-medium mt-1">Monthly Expenses</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 text-center border border-primary-200 transition duration-300 hover:border-primary-600 hover:shadow-xl hover:scale-[1.02]">
                                    <div class="font-extrabold text-3xl text-primary-700">₹13,600</div>
                                    <div class="text-xs text-slate-500 font-medium mt-1">Monthly Savings</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 text-center border border-primary-200 transition duration-300 hover:border-primary-600 hover:shadow-xl hover:scale-[1.02]">
                                    <div class="font-extrabold text-3xl text-primary-700">32%</div>
                                    <div class="text-xs text-slate-500 font-medium mt-1">Savings Rate</div>
                                </div>
                            </div>
                        </article>
                    </div>

                </div>
            </div>
        </section>

        <hr class="border-primary-200">

        <section id="features" class="py-24 sm:py-32 bg-white" role="region" aria-labelledby="features-title">
            <div class="container mx-auto px-4 max-w-7xl">
                <header class="text-center mb-16 animate-on-scroll">
                    <p class="text-primary-600 font-semibold mb-2 text-lg uppercase tracking-wider">Features &amp; Tools</p>
                    <h2 id="features-title" class="text-4xl sm:text-5xl font-extrabold leading-tight mb-4 text-slate-800">
                        Powerful Financial <span class="gradient-text">Tools</span>
                    </h2>
                    <p class="text-xl text-slate-600 max-w-2xl mx-auto">
                        All the features you need to take control of your finances and plan for the future.
                    </p>
                </header>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    
                    <article class="bg-white rounded-xl shadow-xl p-8 border-t-4 border-cyan-500 animate-on-scroll feature-card" style="transition-delay: 0.1s; --card-color-start: #06B6D4; --card-color-end: #67E8F9;" role="article" aria-labelledby="feature-title-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-500 mb-4 p-2 bg-cyan-100 rounded-lg" aria-hidden="true">
                            <path d="M3 3v18h18"/><path d="m18.7 8.7-2.9-2.9-2.9 2.9-2.9-2.9L3 17"/>
                            <path d="m14 14 3.3-3.3c.6-.6 1.4-.9 2.2-.9.4 0 .7.1 1 .2.3.1.5.3.7.5.2.2.4.4.5.7.1.3.2.6.2 1 0 .8-.3 1.6-.9 2.2l-3.3 3.3"/>
                        </svg>
                        <h3 id="feature-title-1" class="text-2xl font-bold text-slate-800 mb-3">Financial Dashboard</h3>
                        <p class="text-slate-600 mb-4">
                            Get a complete overview of your financial health with <strong>interactive charts</strong> and <strong>real-time insights</strong> into your income, expenses, and savings.
                        </p>
                        <a href="#" class="text-primary-600 font-semibold hover:text-primary-700 transition duration-300 group inline-flex items-center">
                            Explore Dashboard 
                            <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="bg-white rounded-xl shadow-xl p-8 border-t-4 border-blue-500 animate-on-scroll feature-card" style="transition-delay: 0.2s; --card-color-start: #3B82F6; --card-color-end: #60A5FA;" role="article" aria-labelledby="feature-title-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-500 mb-4 p-2 bg-blue-100 rounded-lg" aria-hidden="true">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                        <h3 id="feature-title-2" class="text-2xl font-bold text-slate-800 mb-3">Currency Converter</h3>
                        <p class="text-slate-600 mb-4">
                            Convert between <strong>150+ currencies</strong> with live exchange rates. Track conversion history and set alerts for favorable rates.
                        </p>
                        <a href="#" class="text-primary-600 font-semibold hover:text-primary-700 transition duration-300 group inline-flex items-center">
                            Try Converter
                            <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="bg-white rounded-xl shadow-xl p-8 border-t-4 border-amber-500 animate-on-scroll feature-card" style="transition-delay: 0.3s; --card-color-start: #F59E0B; --card-color-end: #FCD34D;" role="article" aria-labelledby="feature-title-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600 mb-4 p-2 bg-amber-100 rounded-lg" aria-hidden="true">
                            <rect width="16" height="16" x="4" y="4" rx="2" ry="2"/>
                            <rect x="8" y="8" width="8" height="8" rx="1" ry="1"/>
                            <path d="m15 1h-6"/><path d="m15 23h-6"/>
                        </svg>
                        <h3 id="feature-title-3" class="text-2xl font-bold text-slate-800 mb-3">Loan Calculator</h3>
                        <p class="text-slate-600 mb-4">
                            Calculate <strong>monthly payments</strong>, total interest, and create detailed amortization schedules for any type of loan or mortgage.
                        </p>
                        <a href="#" class="text-primary-600 font-semibold hover:text-primary-700 transition duration-300 group inline-flex items-center">
                            Calculate Loans
                            <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="bg-white rounded-xl shadow-xl p-8 border-t-4 border-red-500 animate-on-scroll feature-card" style="transition-delay: 0.4s; --card-color-start: #EF4444; --card-color-end: #F87171;" role="article" aria-labelledby="feature-title-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-500 mb-4 p-2 bg-red-100 rounded-lg" aria-hidden="true">
                            <path d="M6 16c0 1 2 2 4 2s4-1 4-2V4c0-1-2-2-4-2S6 3 6 4z"/>
                            <path d="M12 20v2M4 16v-6M20 16v-6M12 16v2"/>
                        </svg>
                        <h3 id="feature-title-4" class="text-2xl font-bold text-slate-800 mb-3">Savings Planner</h3>
                        <p class="text-slate-600 mb-4">
                            Set savings goals and track your progress with <strong>projections</strong> based on your contribution rate and expected investment returns.
                        </p>
                        <a href="#" class="text-primary-600 font-semibold hover:text-primary-700 transition duration-300 group inline-flex items-center">
                            Plan Savings
                            <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="bg-white rounded-xl shadow-xl p-8 border-t-4 border-violet-500 animate-on-scroll feature-card" style="transition-delay: 0.5s; --card-color-start: #8B5CF6; --card-color-end: #A78BFA;" role="article" aria-labelledby="feature-title-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-500 mb-4 p-2 bg-violet-100 rounded-lg" aria-hidden="true">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <line x1="10" y1="9" x2="8" y2="9"/>
                        </svg>
                        <h3 id="feature-title-5" class="text-2xl font-bold text-slate-800 mb-3">Budget Management</h3>
                        <p class="text-slate-600 mb-4">
                            Easily <strong>categorize expenses</strong>, track spending against your budget goals, and identify areas for optimization to save more.
                        </p>
                        <a href="#" class="text-primary-600 font-semibold hover:text-primary-700 transition duration-300 group inline-flex items-center">
                            Manage Budget
                            <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                        </a>
                    </article>

                    <article class="bg-white rounded-xl shadow-xl p-8 border-t-4 border-teal-500 animate-on-scroll feature-card" style="transition-delay: 0.6s; --card-color-start: #14B8A6; --card-color-end: #2DD4BF;" role="article" aria-labelledby="feature-title-6">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-teal-600 mb-4 p-2 bg-teal-100 rounded-lg" aria-hidden="true">
                            <path d="M12 20V10M18 20V4M6 20v-4"/>
                        </svg>
                        <h3 id="feature-title-6" class="text-2xl font-bold text-slate-800 mb-3">Financial Reports</h3>
                        <p class="text-slate-600 mb-4">
                            Generate detailed PDF and CSV reports of your financial activity for any time period. <strong>Export data</strong> for tax preparation or analysis.
                        </p>
                        <a href="#" class="text-primary-600 font-semibold hover:text-primary-700 transition duration-300 group inline-flex items-center">
                            View Reports
                            <i class="fa-solid fa-arrow-right ml-2 transition-transform group-hover:translate-x-1" aria-hidden="true"></i>
                        </a>
                    </article>
                </div>
            </div>
        </section>
        
        <hr class="border-primary-200">

        <section id="how-it-works" class="py-24 sm:py-32 bg-primary-50" role="region" aria-labelledby="how-it-works-title">
            <div class="container mx-auto px-4 max-w-7xl">
                <header class="text-center mb-16 animate-on-scroll">
                    <p class="text-primary-600 font-semibold mb-2 text-lg uppercase tracking-wider">Simple Process</p>
                    <h2 id="how-it-works-title" class="text-4xl sm:text-5xl font-extrabold leading-tight mb-4 text-slate-800">
                        Your Path to Financial <span class="gradient-text">Freedom</span>
                    </h2>
                    <p class="text-xl text-slate-600 max-w-2xl mx-auto">
                        Three easy steps to start mastering your money today.
                    </p>
                </header>

                <div class="relative flex flex-col md:flex-row justify-between items-start">
                    <div class="absolute hidden md:block w-full top-[calc(50%-1px)] h-0.5 bg-primary-200 z-0 px-8"></div>
                    <div class="absolute block md:hidden left-1/2 transform -translate-x-1/2 h-full w-0.5 bg-primary-200 z-0"></div>

                    <div class="w-full md:w-1/3 text-center p-4 md:p-8 animate-on-scroll" style="transition-delay: 0.1s;">
                        <div class="relative mx-auto w-16 h-16 mb-6 rounded-full bg-primary-600 flex items-center justify-center text-white text-2xl font-bold shadow-xl shadow-primary-500/50 transform hover:scale-110 transition duration-300 ring-4 ring-white z-10">
                            1
                            <div class="absolute inset-0 w-full h-full bg-primary-300 rounded-full animate-ping opacity-75"></div>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3"><i class="fa-solid fa-user-check mr-2 text-primary-600" aria-hidden="true"></i>Sign Up &amp; Connect</h3>
                        <p class="text-slate-600">Create your secure account and optionally link your bank accounts for <strong>automatic transaction tracking</strong>.</p>
                    </div>

                    <div class="w-full md:w-1/3 text-center p-4 md:p-8 animate-on-scroll" style="transition-delay: 0.3s;">
                        <div class="relative mx-auto w-16 h-16 mb-6 rounded-full bg-primary-600 flex items-center justify-center text-white text-2xl font-bold shadow-xl shadow-primary-500/50 transform hover:scale-110 transition duration-300 ring-4 ring-white z-10">
                            2
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3"><i class="fa-solid fa-map-marker-alt mr-2 text-primary-600" aria-hidden="true"></i>Map Your Finances</h3>
                        <p class="text-slate-600">Categorize your spending, <strong>set up budgets</strong>, and use our tools to visualize your current financial map.</p>
                    </div>

                    <div class="w-full md:w-1/3 text-center p-4 md:p-8 animate-on-scroll" style="transition-delay: 0.5s;">
                        <div class="relative mx-auto w-16 h-16 mb-6 rounded-full bg-primary-600 flex items-center justify-center text-white text-2xl font-bold shadow-xl shadow-primary-500/50 transform hover:scale-110 transition duration-300 ring-4 ring-white z-10">
                            3
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3"><i class="fa-solid fa-chart-bar mr-2 text-primary-600" aria-hidden="true"></i>Grow &amp; Optimize</h3>
                        <p class="text-slate-600">Receive <strong>personalized insights</strong>, optimize your budget, and track your progress toward your long-term goals.</p>
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-primary-200">

        <section id="faq" class="py-24 sm:py-32 bg-white" role="region" aria-labelledby="faq-title">
            <div class="container mx-auto px-4 max-w-4xl">
                <header class="text-center mb-16 animate-on-scroll">
                    <p class="text-primary-600 font-semibold mb-2 text-lg uppercase tracking-wider">Need Answers?</p>
                    <h2 id="faq-title" class="text-4xl sm:text-5xl font-extrabold leading-tight mb-4 text-slate-800">
                        Frequently Asked <span class="gradient-text">Questions</span>
                    </h2>
                    <p class="text-xl text-slate-600 max-w-2xl mx-auto">
                        Everything you need to know about MoneyMap's platform and security.
                    </p>
                </header>

                <div id="faq-accordion" class="space-y-4" role="list">
                    
                    <article class="border border-primary-200 rounded-xl bg-primary-50 animate-on-scroll" style="transition-delay: 0.1s;" role="listitem">
                        <h3 class="font-bold text-lg">
                            <button id="faq-btn-1" class="flex justify-between items-center w-full p-6 text-left text-slate-800 hover:text-primary-600 transition duration-300 accordion-btn" aria-expanded="false" aria-controls="faq-content-1">
                                Is MoneyMap secure for linking bank accounts?
                                <i class="fa-solid fa-chevron-down ml-2 accordion-icon" aria-hidden="true"></i>
                            </button>
                        </h3>
                        <div id="faq-content-1" class="accordion-content hidden p-6 pt-0 text-slate-600" role="region" aria-hidden="true">
                            <p>Yes, security is our top priority. We use <strong>256-bit encryption</strong>, multi-factor authentication, and never store your bank login credentials. We use secure, <strong>read-only APIs</strong> to connect to your financial institutions, ensuring your data is safe and private.</p>
                        </div>
                    </article>

                    <article class="border border-primary-200 rounded-xl bg-primary-50 animate-on-scroll" style="transition-delay: 0.2s;" role="listitem">
                        <h3 class="font-bold text-lg">
                            <button id="faq-btn-2" class="flex justify-between items-center w-full p-6 text-left text-slate-800 hover:text-primary-600 transition duration-300 accordion-btn" aria-expanded="false" aria-controls="faq-content-2">
                                What currencies and regions does MoneyMap support?
                                <i class="fa-solid fa-chevron-down ml-2 accordion-icon" aria-hidden="true"></i>
                            </button>
                        </h3>
                        <div id="faq-content-2" class="accordion-content hidden p-6 pt-0 text-slate-600" role="region" aria-hidden="true">
                            <p>MoneyMap is designed for global use. Our platform fully supports <strong>150+ currencies</strong> and provides localization features for major markets, including India (INR), the US (USD), Europe (EUR), and more. You can track accounts and expenses in multiple currencies simultaneously.</p>
                        </div>
                    </article>

                    <article class="border border-primary-200 rounded-xl bg-primary-50 animate-on-scroll" style="transition-delay: 0.3s;" role="listitem">
                        <h3 class="font-bold text-lg">
                            <button id="faq-btn-3" class="flex justify-between items-center w-full p-6 text-left text-slate-800 hover:text-primary-600 transition duration-300 accordion-btn" aria-expanded="false" aria-controls="faq-content-3">
                                Is there a free version of MoneyMap?
                                <i class="fa-solid fa-chevron-down ml-2 accordion-icon" aria-hidden="true"></i>
                            </button>
                        </h3>
                        <div id="faq-content-3" class="accordion-content hidden p-6 pt-0 text-slate-600" role="region" aria-hidden="true">
                            <p>We offer a <strong>14-day free trial</strong> of our premium features, with no credit card required to start. After the trial, you can choose one of our affordable plans or continue using a limited version of our core expense tracking and reporting features for free.</p>
                        </div>
                    </article>

                    <article class="border border-primary-200 rounded-xl bg-primary-50 animate-on-scroll" style="transition-delay: 0.4s;" role="listitem">
                        <h3 class="font-bold text-lg">
                            <button id="faq-btn-4" class="flex justify-between items-center w-full p-6 text-left text-slate-800 hover:text-primary-600 transition duration-300 accordion-btn" aria-expanded="false" aria-controls="faq-content-4">
                                How does the Savings Planner work?
                                <i class="fa-solid fa-chevron-down ml-2 accordion-icon" aria-hidden="true"></i>
                            </button>
                        </h3>
                        <div id="faq-content-4" class="accordion-content hidden p-6 pt-0 text-slate-600" role="region" aria-hidden="true">
                            <p>Our Savings Planner helps you define a goal (e.g., retirement, down payment) and calculates the <strong>required monthly contribution</strong> based on your target amount, timeframe, and expected interest/return rate. It automatically tracks your actual progress and provides motivational alerts.</p>
                        </div>
                    </article>

                </div>
            </div>
        </section>

        <hr class="border-primary-200">

        <section id="cta" class="py-20 sm:py-24 bg-primary-600 text-white overflow-hidden relative" role="region" aria-labelledby="cta-title">
            <div class="container mx-auto px-4 max-w-7xl text-center z-10 relative animate-on-scroll">
                <h2 id="cta-title" class="text-4xl sm:text-5xl font-extrabold mb-4">
                    Ready to Master Your Money?
                </h2>
                <p class="text-xl text-primary-200 mb-10 max-w-3xl mx-auto">
                    Join the thousands who are achieving financial clarity and freedom with MoneyMap. Start your <strong>14-day free trial</strong> today—no credit card required.
                </p>
                <a href="#" class="px-10 py-4 font-bold text-xl rounded-full transition duration-300 ease-in-out bg-white text-primary-600 shadow-2xl shadow-primary-900/50 hover:bg-primary-50 hover:text-primary-700 hover:scale-[1.05] transform" aria-label="Start Your Free Trial">
                    Start My Free Trial Now <i class="fa-solid fa-arrow-right ml-2" aria-hidden="true"></i>
                </a>
            </div>
            <svg class="absolute inset-0 w-full h-full opacity-10" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <pattern id="pattern-circles" x="0" y="0" width="10" height="10" patternUnits="userSpaceOnUse">
                        <circle cx="5" cy="5" r="0.5" fill="#ECFDF5"/>
                    </pattern>
                </defs>
                <rect x="0" y="0" width="100%" height="100%" fill="url(#pattern-circles)"/>
            </svg>
        </section>
    </main>

    <footer class="bg-gray-800 text-gray-400 py-12" role="contentinfo">
        <div class="container mx-auto px-4 max-w-7xl grid grid-cols-2 sm:grid-cols-4 gap-12">
            
            <div class="col-span-2 sm:col-span-1">
                <a href="#" class="text-3xl font-extrabold text-white tracking-tighter mb-4 block" aria-label="MoneyMap Home">
                    Money<span class="text-primary-400">Map</span>
                </a>
                <p class="text-sm">Your all-in-one platform for mastering personal finance with clarity and confidence.</p>
                <div class="flex space-x-4 mt-6">
                    <a href="#" class="text-primary-400 hover:text-white transition duration-300 text-xl" aria-label="Follow us on Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
                    <a href="#" class="text-primary-400 hover:text-white transition duration-300 text-xl" aria-label="Follow us on Twitter"><i class="fab fa-twitter" aria-hidden="true"></i></a>
                    <a href="#" class="text-primary-400 hover:text-white transition duration-300 text-xl" aria-label="Follow us on LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
                    <a href="#" class="text-primary-400 hover:text-white transition duration-300 text-xl" aria-label="Follow us on Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
                </div>
            </div>

            <nav aria-label="Footer Resources">
                <h4 class="text-lg font-semibold text-white mb-4">Resources</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#features" class="hover:text-primary-400 transition duration-300">Features</a></li>
                    <li><a href="#" class="hover:text-primary-400 transition duration-300">Pricing</a></li>
                    <li><a href="#" class="hover:text-primary-400 transition duration-300">Help Center</a></li>
                    <li><a href="#faq" class="hover:text-primary-400 transition duration-300">FAQ</a></li>
                </ul>
            </nav>

            <nav aria-label="Footer Company">
                <h4 class="text-lg font-semibold text-white mb-4">Company</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-primary-400 transition duration-300">About Us</a></li>
                    <li><a href="#" class="hover:text-primary-400 transition duration-300">Blog</a></li>
                    <li><a href="#" class="hover:text-primary-400 transition duration-300">Careers <span class="ml-1 bg-primary-600 text-primary-50 px-2 py-0.5 rounded-full text-xs font-medium">Hiring</span></a></li>
                    <li><a href="#" class="hover:text-primary-400 transition duration-300">Security &amp; Trust</a></li>
                </ul>
            </nav>
            
            <div class="col-span-2 sm:col-span-1">
                <h4 class="text-lg font-semibold text-white mb-4">Stay Updated</h4>
                <p class="text-sm mb-4">Subscribe to our newsletter for weekly finance tips and product updates.</p>
                <form aria-label="Newsletter Subscription">
                    <div class="flex">
                        <label for="email-input" class="sr-only">Your email address</label>
                        <input id="email-input" type="email" placeholder="Your email address" class="p-3 rounded-l-lg w-full text-gray-800 focus:outline-none focus:ring-2 focus:ring-primary-400" required aria-required="true">
                        <button type="submit" class="p-3 bg-primary-600 rounded-r-lg text-white font-semibold hover:bg-primary-700 transition duration-300" aria-label="Subscribe to newsletter">
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-12 pt-8 text-center text-sm">
            &copy; 2025 MoneyMap. All rights reserved. | <a href="#" class="hover:text-primary-400">Privacy Policy</a> | <a href="#" class="hover:text-primary-400">Terms of Service</a>
        </div>
    </footer>

    <script>
        // JS for Mobile Menu Toggle and Close
        const menuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        const mobileLinks = document.querySelectorAll('.mobile-nav-link');

        if (menuBtn && mobileMenu && menuIcon) {
            menuBtn.addEventListener('click', function() {
                const isHidden = mobileMenu.classList.toggle('show');
                const isExpanded = !isHidden;
                
                menuBtn.setAttribute('aria-expanded', isExpanded.toString());
                
                // Toggle icon from bars to times and vice versa
                if (isExpanded) {
                    menuIcon.classList.remove('fa-times');
                    menuIcon.classList.add('fa-bars');
                } else {
                    menuIcon.classList.remove('fa-bars');
                    menuIcon.classList.add('fa-times');
                }
            });

            // Close mobile menu when a link is clicked
            mobileLinks.forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenu.classList.remove('show');
                    menuBtn.setAttribute('aria-expanded', 'false');
                    menuIcon.classList.remove('fa-times');
                    menuIcon.classList.add('fa-bars');
                });
            });
        }
        
        // JS for Sticky Navbar Shadow on Scroll
        const navbar = document.getElementById('navbar');
        if (navbar) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 80) { 
                    navbar.classList.add('shadow-2xl', 'bg-white/95', 'backdrop-blur-sm');
                    navbar.classList.remove('shadow-lg', 'bg-white');
                } else {
                    navbar.classList.remove('shadow-2xl', 'bg-white/95', 'backdrop-blur-sm');
                    navbar.classList.add('shadow-lg', 'bg-white');
                }
            }, { passive: true });
        }

        // JS for Scroll Animations (Intersection Observer)
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElems = document.querySelectorAll('.animate-on-scroll');
            
            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                             entry.target.classList.add('is-visible');
                             observer.unobserve(entry.target);
                        }, 50); 
                    }
                });
            }, { 
                threshold: 0.1, 
                rootMargin: "0px 0px -15% 0px"
            }); 
            
            fadeElems.forEach(elem => {
                const delayString = elem.style.transitionDelay;
                const delayMs = delayString ? parseFloat(delayString) * 1000 : 0;
                const isHeroElement = elem.closest('#hero');

                if (isHeroElement) {
                    // Elements in the Hero section animate immediately on load after their delay
                    setTimeout(() => {
                        elem.classList.add('is-visible');
                    }, delayMs + 100); 
                } else {
                    // Other elements wait for the scroll trigger
                    observer.observe(elem);
                }
            });
            
            // FAQ Accordion JS
            const accordionButtons = document.querySelectorAll('.accordion-btn');

            accordionButtons.forEach(button => {
                const contentId = button.getAttribute('aria-controls');
                const content = document.getElementById(contentId);
                const icon = button.querySelector('.accordion-icon');

                // Skip if essential elements are missing
                if (!content || !icon) return; 
                
                button.addEventListener('click', () => {
                    const isOpening = content.classList.contains('hidden');
                    
                    // Close all other open items
                    accordionButtons.forEach(otherButton => {
                        if (otherButton === button) return; // Skip current button
                        
                        const otherContentId = otherButton.getAttribute('aria-controls');
                        const otherContent = document.getElementById(otherContentId);
                        const otherIcon = otherButton.querySelector('.accordion-icon');
                        
                        if (otherContent && otherIcon && !otherContent.classList.contains('hidden')) {
                            otherContent.classList.add('hidden');
                            otherButton.setAttribute('aria-expanded', 'false');
                            otherContent.setAttribute('aria-hidden', 'true');
                            otherIcon.classList.remove('open');
                        }
                    });

                    // Toggle current item
                    if (isOpening) {
                        content.classList.remove('hidden');
                        button.setAttribute('aria-expanded', 'true');
                        content.setAttribute('aria-hidden', 'false');
                        icon.classList.add('open');
                    } else {
                        content.classList.add('hidden');
                        button.setAttribute('aria-expanded', 'false');
                        content.setAttribute('aria-hidden', 'true');
                        icon.classList.remove('open');
                    }
                });
            });
        });
    </script>

</body>
</html>