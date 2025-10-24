<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to MoneyMap | Financial Freedom</title>
    <meta name="description" content="Sign in to your MoneyMap account and continue your journey to financial freedom.">
    
    <!-- Preload important resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
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
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
        }
        
        /* Floating Background Elements */
        .floating-element {
            position: fixed;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.15) 100%);
            filter: blur(40px);
            z-index: 0;
            opacity: 0.8;
        }

        .floating-element-sm {
            width: 10rem;
            height: 10rem;
        }
        
        .form-container {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); 
        }
        
        /* Custom focus style for inputs */
        .input-focus:focus {
            box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
            border-color: #059669;
        }

        /* Mobile-specific styles */
        @media (max-width: 1023px) {
            body, html {
                overflow: hidden;
                height: 100%;
                margin: 0;
                padding: 0;
            }
            
            .mobile-container {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 1rem;
                background: linear-gradient(to bottom right, #ECFDF5, white, #D1FAE5);
            }
            
            .mobile-form-container {
                width: 100%;
                max-width: 22rem;
            }
            
            .hero-section {
                display: none;
            }
        }

        /* Desktop-specific styles */
        @media (min-width: 1024px) {
            .desktop-container {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
                margin-left: 10rem;
            }
        }
    </style>
</head>
<body class="font-sans">
    <!-- Floating Background Elements -->
    <div class="floating-element floating-element-sm -top-10 -left-10"></div>
    <div class="floating-element floating-element-sm -bottom-10 -right-10"></div>
    
    <!-- Mobile Layout -->
    <div class="mobile-container lg:hidden">
        <section class="mobile-form-container"> 
            <div class="form-container rounded-xl p-4 border border-primary-100">
                <header class="text-center mb-4">
                    <a href="index.php" class="inline-flex items-center text-xl font-bold text-gray-800 tracking-tight mb-2">
                        <svg class="h-6 w-6 text-primary-600 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                        Money<span class="text-primary-600">Map</span>
                    </a>
                    <h2 class="text-lg font-bold text-slate-800">Welcome Back</h2>
                    <p class="text-slate-600 text-xs">Sign in to your account</p>
                </header>
                
                <form id="loginForm" action="../api/auth.php" method="POST" class="space-y-3">
                    <div>
                        <label for="email" class="block text-xs font-medium text-slate-700 mb-1">Email</label>
                        <div class="relative">
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none input-focus transition duration-200"
                                placeholder="you@example.com"
                                aria-describedby="email-error"
                            >
                            <i class="fa-solid fa-envelope absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-xs"></i>
                        </div>
                        <div id="email-error" class="text-red-500 text-xs mt-1 hidden font-medium" role="alert"></div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-xs font-medium text-slate-700 mb-1">Password</label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none input-focus transition duration-200 pr-10"
                                placeholder="Enter your password"
                                aria-describedby="password-error"
                            >
                            <i id="password-toggle" class="password-toggle fa-solid fa-eye text-xs" aria-label="Toggle password visibility"></i>
                        </div>
                        <div id="password-error" class="text-red-500 text-xs mt-1 hidden font-medium" role="alert"></div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center">
                            <input 
                                id="remember" 
                                name="remember" 
                                type="checkbox" 
                                class="w-3 h-3 text-primary-600 border-slate-300 rounded focus:ring-primary-500"
                            >
                            <label for="remember" class="ml-2 text-xs text-slate-700">
                                Remember me
                            </label>
                        </div>
                        <a href="forgot-password.php" class="text-xs text-primary-600 font-medium hover:text-primary-700">
                            Forgot password?
                        </a>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
                    >
                        Sign In
                    </button>
                    
                    <div class="text-center mt-3">
                        <p class="text-slate-600 text-xs">
                            Don't have an account? 
                            <a href="signup.php" class="text-primary-600 font-bold">Sign up</a>
                        </p>
                    </div>
                </form>
                <!-- ✅ Back button placed outside the form -->

<!-- Mobile-friendly Back button (centered) -->
<div class="mt-4 flex justify-center">
  <a href="index.php" 
     class="inline-flex items-center px-6 py-2 text-xs font-medium text-primary-600 
            border border-primary-600 rounded-lg shadow-sm 
            hover:bg-primary-600 hover:text-white transition-all duration-200">
      <i class="fa-solid fa-arrow-left mr-2"></i> Back
  </a>
</div>
            </div>
        </section>
    </div>

    <!-- Desktop Layout -->
    <div class="desktop-container hidden lg:flex max-w-6xl w-full">
        <!-- Hero Section -->
        <section class="w-1/2 text-left p-2">
            <header class="mb-8">
                <a href="index.php" class="inline-flex items-center text-2xl font-extrabold text-gray-800 tracking-tight">
                    <svg class="h-7 w-7 text-primary-600 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                    Money<span class="text-primary-600">Map</span>
                </a>
            </header>
            
            <h1 class="text-4xl font-extrabold leading-tight mb-4 text-slate-800">
                Welcome Back to <span class="text-primary-600">MoneyMap</span>
            </h1>
            
            <p class="text-lg text-slate-600 mb-6 max-w-md">
                Continue your journey to financial freedom. Track your progress and achieve your goals with our powerful tools.
            </p>
            
            <article class="bg-white rounded-2xl shadow-lg p-6 max-w-sm">
                <div class="flex items-start mb-4">
                    <div class="bg-primary-100 rounded-full p-2 mr-3 flex-shrink-0">
                        <i class="fa-solid fa-chart-line text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800">Track Your Progress</h2>
                        <p class="text-slate-600 text-sm">See how your financial health has improved since your last visit.</p>
                    </div>
                </div>
                
                <div class="flex items-start mb-4">
                    <div class="bg-primary-100 rounded-full p-2 mr-3 flex-shrink-0">
                        <i class="fa-solid fa-bell text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800">Stay Updated</h2>
                        <p class="text-slate-600 text-sm">Check your notifications and recent financial insights.</p>
                    </div>
                </div>
                
                <div class="flex items-start">
                    <div class="bg-primary-100 rounded-full p-2 mr-3 flex-shrink-0">
                        <i class="fa-solid fa-target text-primary-600"></i>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800">Achieve Goals</h2>
                        <p class="text-slate-600 text-sm">Continue working towards your financial milestones.</p>
                    </div>
                </div>
            </article>
        </section>

        <!-- Login Form Section -->
        <section class="w-1/2 max-w-md"> 
            <div class="form-container rounded-2xl shadow-xl p-6 border border-primary-100">
                <header class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Welcome Back</h2>
                    <p class="text-slate-600 mt-1 text-sm">Sign in to your account</p>
                </header>
                
               <form id="desktopLoginForm" action="../api/auth.php" method="POST" class="space-y-4">
    <!-- Email -->
    <div>
        <label for="desktop-email" class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
        <div class="relative">
            <input 
                type="email" 
                id="desktop-email" 
                name="email" 
                required 
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none input-focus transition duration-200"
                placeholder="you@example.com"
                aria-describedby="desktop-email-error"
            >
            <i class="fa-solid fa-envelope absolute right-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
        </div>
        <div id="desktop-email-error" class="text-red-500 text-sm mt-1 hidden font-medium" role="alert"></div>
    </div>
    
    <!-- Password -->
    <div>
        <label for="desktop-password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
        <div class="relative">
            <input 
                type="password" 
                id="desktop-password" 
                name="password" 
                required 
                class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:outline-none input-focus transition duration-200 pr-10"
                placeholder="Enter your password"
                aria-describedby="desktop-password-error"
            >
            <i id="desktop-password-toggle" class="password-toggle fa-solid fa-eye" aria-label="Toggle password visibility"></i>
        </div>
        <div id="desktop-password-error" class="text-red-500 text-sm mt-1 hidden font-medium" role="alert"></div>
    </div>
    
    <!-- Remember + Forgot -->
    <div class="flex items-center justify-between pt-1">
        <div class="flex items-center">
            <input 
                id="desktop-remember" 
                name="remember" 
                type="checkbox" 
                class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500"
            >
            <label for="desktop-remember" class="ml-2 text-sm text-slate-700">
                Remember me
            </label>
        </div>
        <a href="forgot-password.php" class="text-sm text-primary-600 font-medium hover:text-primary-700">
            Forgot password?
        </a>
    </div>
    
    <!-- Submit -->
    <button 
        type="submit" 
        class="w-full py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/50"
    >
        Sign In
    </button>
    
    <!-- Sign Up link -->
    <div class="text-center mt-4">
        <p class="text-slate-600 text-sm">
            Don't have an account? 
            <a href="signup.php" class="text-primary-600 hover:text-primary-700 font-bold">Sign up</a>
        </p>
    </div>
</form>

<!-- ✅ Desktop Back button (also centered) -->
<div class="mt-6 flex justify-center">
    <a href="index.php" 
       class="inline-flex items-center px-6 py-3 text-white bg-primary-600 hover:bg-primary-700 
              font-semibold rounded-xl shadow-lg transition duration-300 ease-in-out">
        <i class="fa-solid fa-arrow-left mr-2"></i> Back
    </a>
</div>

            </div>
        </section>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity duration-300" aria-modal="true" role="dialog" aria-labelledby="modalTitle" aria-describedby="modalDescription">
        <div class="bg-white rounded-xl p-5 transform transition-all duration-300 scale-95 opacity-0 shadow-xl mx-4 max-w-xs">
            <div class="text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary-100">
                    <i class="fa-solid fa-check text-primary-600 text-xl"></i>
                </div>
                
                <h3 id="modalTitle" class="mt-3 text-lg font-bold text-slate-900">Login Successful!</h3>
                
                <p id="modalDescription" class="mt-1 text-slate-600 text-sm">
                    Welcome back! Redirecting to your dashboard...
                </p>
                
                <div class="mt-3">
                    <button id="modalRedirectButton" type="button" class="w-full py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/50 text-sm">
                        Go to Dashboard (3s)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // DOM elements
            const mobileForm = document.getElementById('loginForm');
            const desktopForm = document.getElementById('desktopLoginForm');
            const successModal = document.getElementById('successModal');
            const modalMessage = document.getElementById('modalDescription');
            const modalRedirectButton = document.getElementById('modalRedirectButton');

            // Helper Functions
            function displayError(formType, fieldId, message) {
                const prefix = formType === 'mobile' ? '' : 'desktop-';
                const errorEl = document.getElementById(prefix + fieldId + '-error');
                const inputEl = document.getElementById(prefix + fieldId);

                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.remove('hidden');
                }
                
                if (inputEl) {
                    inputEl.classList.add('border-red-500');
                    inputEl.classList.remove('border-slate-300');
                    inputEl.focus();
                }
            }

            function clearErrors(formType) {
                const prefix = formType === 'mobile' ? '' : 'desktop-';
                document.querySelectorAll(`[id$="${prefix}-error"]`).forEach(el => {
                    el.classList.add('hidden');
                    el.textContent = '';
                });
                document.querySelectorAll(`#${prefix}email, #${prefix}password`).forEach(input => {
                    input.classList.remove('border-red-500');
                    input.classList.add('border-slate-300');
                });
            }
            
            // Function to show the modal with animation and handle redirect countdown
            function showModal(message, redirectUrl = 'dashboard.php') {
                // Update content
                modalMessage.textContent = message;
                
                // Show modal background
                successModal.classList.remove('hidden');
                successModal.classList.add('flex');
                
                // Prevent body scrolling when modal is open
                document.body.style.overflow = 'hidden';
                
                // Trigger transition for the modal box
                setTimeout(() => {
                    const modalBox = successModal.querySelector('div > div');
                    modalBox.classList.remove('scale-95', 'opacity-0');
                    modalBox.classList.add('scale-100', 'opacity-100');
                }, 10);
                
                // Handle countdown and redirection
                let countdown = 3;
                const interval = setInterval(() => {
                    countdown--;
                    modalRedirectButton.textContent = `Go to Dashboard (${countdown}s)`;
                    
                    if (countdown <= 0) {
                        clearInterval(interval);
                        document.body.style.overflow = '';
                        window.location.href = redirectUrl;
                    }
                }, 1000);
                
                // Allow instant redirection on button click
                modalRedirectButton.onclick = () => {
                    clearInterval(interval);
                    document.body.style.overflow = '';
                    window.location.href = redirectUrl;
                };
            }
            
            // Password Toggle Setup
            function setupPasswordToggle(toggleId, inputId) {
                const toggle = document.getElementById(toggleId);
                const input = document.getElementById(inputId);
                
                if (toggle && input) {
                    toggle.addEventListener('click', function() {
                        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                        input.setAttribute('type', type);
                        
                        // Toggle eye icon
                        toggle.classList.toggle('fa-eye');
                        toggle.classList.toggle('fa-eye-slash');
                    });
                }
            }

            // Setup password toggles for both forms
            setupPasswordToggle('password-toggle', 'password');
            setupPasswordToggle('desktop-password-toggle', 'desktop-password');

            // Form Submission Logic
            function setupFormSubmission(form, formType) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    clearErrors(formType);
                    
                    let isValid = true;
                    
                    const prefix = formType === 'mobile' ? '' : 'desktop-';
                    const email = document.getElementById(prefix + 'email').value.trim();
                    const password = document.getElementById(prefix + 'password').value;
                    
                    // Client-side validation
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    if (!emailRegex.test(email)) {
                        displayError(formType, 'email', 'Please enter a valid email address');
                        isValid = false;
                    }
                    if (isValid && password.length < 1) {
                        displayError(formType, 'password', 'Please enter your password');
                        isValid = false;
                    }

                    if (!isValid) {
                        return;
                    }
                    
                    // Prepare the data for the API call
                    const formData = {
                        email: email,
                        password: password,
                        remember: document.getElementById(prefix + 'remember').checked ? 'on' : 'off'
                    };

                    // Disable button and show loading state
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonText = submitButton.textContent;
                    submitButton.textContent = 'Signing In...';
                    submitButton.disabled = true;

                    try {
                        const response = await fetch('../api/auth.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(formData),
                        });

                        if (!response.ok) {
                            throw new Error(`Server returned status: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.success) {
                            // SUCCESS: Show the custom modal and handle redirect
                            showModal(result.message || "Login successful. Welcome back!"); 
                        } else {
                            // Handle server-side validation/logic errors
                            if (result.field) {
                                displayError(formType, result.field, result.message);
                            } else {
                                alert('Login failed: ' + result.message);
                            }
                        }
                    } catch (error) {
                        console.error('Submission Error:', error);
                        alert('An unexpected error occurred. Please check server logs or network connection.');
                    } finally {
                        // Only re-enable the button if login was NOT successful (i.e., modal is not displayed)
                        if (!successModal.classList.contains('flex')) {
                            submitButton.textContent = originalButtonText;
                            submitButton.disabled = false;
                        }
                    }
                });
            }

            // Setup both forms
            if (mobileForm) setupFormSubmission(mobileForm, 'mobile');
            if (desktopForm) setupFormSubmission(desktopForm, 'desktop');
        });
    </script>
</body>
</html>


