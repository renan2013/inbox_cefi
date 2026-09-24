<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox BPM - Iniciar Sesión</title>

    <!-- Meta etiquetas para previsualización en WhatsApp (Open Graph) -->
    <meta property="og:title" content="&#8203;">
    <meta property="og:image" content="https://unela.org/bpm_unela/imgs/logo.png">
    <meta property="og:image:secure_url" content="https://unela.org/bpm_unela/imgs/logo.png">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="600">
    <meta property="og:image:height" content="600">
    <meta property="og:url" content="https://unela.org/bpm_unela/">
    <meta property="og:type" content="website">
    <link rel="image_src" href="https://unela.org/bpm_unela/imgs/logo.png">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #5fb230;
            --primary-hover: #4e9a26;
            --text-dark: #2d3436;
            --bg-gradient: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: var(--text-dark);
        }

        .login-card {
            background: #f8fafc;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 420px;
            padding: 3rem 2.5rem;
            transition: all 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.1);
        }

        .brand-logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 2rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }

        .welcome-text {
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            color: #1a1a1a;
        }

        .subtitle-text {
            color: #636e72;
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group-custom .prefix-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #b2bec3;
            transition: color 0.2s;
            z-index: 10;
        }

        .form-control {
            border-radius: 0.85rem;
            padding: 0.8rem 3rem 0.8rem 2.75rem;
            border: 2px solid #e2e8f0;
            background-color: #ffffff;
            font-size: 0.95rem;
            transition: all 0.2s;
            width: 100%;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(95, 178, 48, 0.15);
            outline: none;
        }

        .form-control:focus + .prefix-icon {
            color: var(--primary-color);
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #b2bec3;
            cursor: pointer;
            z-index: 11;
            transition: color 0.2s;
            width: auto;
            left: auto;
        }

        .toggle-password:hover {
            color: var(--primary-color);
        }

        .btn-login {
            background-color: var(--primary-color);
            border: none;
            color: white;
            padding: 0.9rem;
            border-radius: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            margin-top: 1rem;
            width: 100%;
        }

        .btn-login:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(95, 178, 48, 0.25);
            color: white;
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border: none;
            border-radius: 1rem;
            padding: 1rem;
            font-size: 0.9rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .alert i {
            font-size: 1.25rem;
            margin-right: 0.75rem;
        }

        .login-footer-text {
            margin-top: 2.5rem;
            color: #a0aec0;
            font-size: 0.8rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center">
            <img src="{{ asset('imgs/SVG/logo_color.svg') }}" alt="Inbox BPM" class="brand-logo">
            <h1 class="welcome-text">¡Hola de nuevo!</h1>
            <p class="subtitle-text">Ingresa tus credenciales para continuar</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="post">
            @csrf
            <div class="input-group-custom">
                <i class="bi bi-envelope prefix-icon"></i>
                <input type="email" name="email" id="email" class="form-control" placeholder="Correo electrónico" value="{{ old('email') }}" required autofocus>
            </div>
            
            <div class="input-group-custom">
                <i class="bi bi-shield-lock prefix-icon"></i>
                <input type="password" name="password" id="password" class="form-control" placeholder="Contraseña" required>
                <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
            </div>

            <div class="input-group-custom">
                <i class="bi bi-shield-check prefix-icon"></i>
                <input type="number" name="captcha_val" id="captcha_val" class="form-control" placeholder="¿Cuánto es {{ session('captcha_num1') }} + {{ session('captcha_num2') }}?" required>
            </div>

            <button type="submit" class="btn btn-login">
                Iniciar Sesión <i class="bi bi-arrow-right-short ms-1"></i>
            </button>
        </form>

        <div class="login-footer-text">
            &copy; {{ date('Y') }} BPM Intelligence System<br>
            <div class="mt-2" style="opacity: 0.8;">
                Designed and developed by <a href="https://renangalvan.net" target="_blank" style="color: inherit; text-decoration: none; font-weight: 600;">renangalvan.net</a>
            </div>
            <div class="mt-1 fw-bold text-dark" style="opacity: 0.85;">
                Un producto de grupodivisoft
            </div>
            <div class="mt-2" style="opacity: 0.75;">
                San José, Costa Rica - +506 87777849
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>
