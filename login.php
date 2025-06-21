<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin Panel</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/tsparticles-slim@2.12.0/tsparticles.slim.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    
    <style>
        body { font-family: 'Inter', sans-serif; overflow-x: hidden; }
        #particles-container {
            position: relative;
            background-image: url('login-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        #particles-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to top, rgba(13, 17, 23, 0.8), rgba(13, 17, 23, 0.4));
            z-index: 1;
        }
        #tsparticles { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2; }
        .branding-content { position: relative; z-index: 3; }
        
        .form-group { position: relative; }
        .form-input {
            background-color: #1e293b; border: 2px solid #334155; color: #f1f5f9;
            padding: 1rem 1rem 1rem 2.5rem; transition: all 0.2s ease-in-out;
        }
        .form-input:focus { outline: none; border-color: #f97316; background-color: #1e293b; }
        .form-label {
            position: absolute; left: 2.5rem; top: 1rem; color: #94a3b8;
            pointer-events: none; transition: all 0.2s ease-out;
            background-color: #1e293b; padding: 0 5px;
        }
        .form-input:focus ~ .form-label,
        .form-input:not(:placeholder-shown) ~ .form-label {
            top: -0.65rem; left: 0.75rem; font-size: 0.875rem; color: #f97316;
        }
        
        @keyframes slide-in-left { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes slide-in-right { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        .animate-slide-in-left { animation: slide-in-left 0.8s ease-out forwards; }
        .animate-slide-in-right { animation: slide-in-right 0.8s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-900">

    <div class="flex min-h-screen">
        <div id="particles-container" class="hidden md:flex w-1/2 items-center justify-center p-12">
            <div id="tsparticles"></div>
            <div class="branding-content text-center animate-slide-in-left">
                <h1 class="text-5xl font-bold text-white mb-4 flex items-center justify-center">
                    <i class="fas fa-shoe-prints mr-4 text-orange-500"></i> Admin Rewalk
                </h1>
                <p class="text-xl text-slate-300">Panel Manajemen untuk Toko Sepatu Anda.</p>
            </div>
        </div>

        <div class="w-full md:w-1/2 flex items-center justify-center p-8 bg-slate-900">
            <div class="w-full max-w-md animate-slide-in-right">
                <div class="bg-slate-800/50 backdrop-blur-sm p-8 rounded-2xl border border-slate-700 shadow-2xl shadow-orange-500/10">
                    <div class="text-left mb-8">
                        <h2 class="text-3xl font-bold text-white">Selamat Datang!</h2>
                        <p class="text-slate-400 mt-2">Login untuk mengakses dashboard.</p>
                    </div>
                    
                    <form id="loginForm" action="modules/auth/proses_login.php" method="POST" class="space-y-6">
                        <div class="form-group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5"><i class="fas fa-envelope text-slate-500"></i></span>
                            <input type="email" name="email" id="email" class="form-input w-full rounded-lg placeholder-transparent" placeholder="Email" required>
                            <label for="email" class="form-label">Alamat Email</label>
                        </div>
                        <div class="form-group">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5"><i class="fas fa-lock text-slate-500"></i></span>
                            <input type="password" name="password" id="password" class="form-input w-full rounded-lg placeholder-transparent" placeholder="Password" required>
                            <label for="password" class="form-label">Password</label>
                        </div>
                        <div>
                            <button id="loginButton" type="submit" class="w-full flex justify-center bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-4 rounded-lg focus:outline-none transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
                                <span id="buttonText">Login</span>
                                <i id="loadingIcon" class="fas fa-spinner fa-spin hidden"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <p class="text-center text-sm text-slate-400 mt-6">Belum punya akun? <a href="register.php" class="font-medium text-orange-400 hover:underline">Daftar di sini</a></p>
            </div>
        </div>
    </div>
    
    <script>
        tsParticles.load("tsparticles", { background: { color: { value: "transparent" } }, fpsLimit: 60, interactivity: { events: { onHover: { enable: true, mode: "grab" }, onClick: { enable: true, mode: "push" }, }, modes: { grab: { distance: 140, links: { opacity: 0.5 } }, push: { quantity: 4 }, }, }, particles: { color: { value: "#ffffff" }, links: { color: "#ffffff", distance: 150, enable: true, opacity: 0.1, width: 1, }, move: { enable: true, speed: 1, direction: "none", random: false, straight: false, outModes: { default: "out" } }, number: { density: { enable: true, area: 800 }, value: 80, }, opacity: { value: 0.3 }, shape: { type: "circle" }, size: { value: { min: 1, max: 4 } }, }, detectRetina: true, });
        document.getElementById('loginForm').addEventListener('submit', function() { const button = document.getElementById('loginButton'); button.disabled = true; document.getElementById('buttonText').classList.add('hidden'); document.getElementById('loadingIcon').classList.remove('hidden'); });
    </script>
    <?php if (isset($_SESSION['flash_message'])) { $message = $_SESSION['flash_message']; unset($_SESSION['flash_message']); echo "<script> Swal.fire({ icon: '{$message['type']}', title: '{$message['title']}', text: '{$message['text']}', background: '#1e293b', color: '#ffffff', confirmButtonColor: '#f97316' }); </script>"; } ?>
</body>
</html>