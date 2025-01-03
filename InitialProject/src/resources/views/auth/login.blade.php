<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KKU Login Portal">
    <title>KKU - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4285f4;
            --primary-hover: #3367d6;
            --error-color: #dc2626;
            --text-color: #374151;
            --light-bg: #f5f5f5;
            --border-radius: 8px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(45deg, rgba(66, 183, 245, 0.8) 0%, rgba(66, 245, 189, 0.4) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
            color: var(--text-color);
            padding: clamp(1rem, 5vw, 2rem);
            line-height: 1.5;
        }

        .form-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            width: 100%;
            max-width: min(90vw, 500px);
            padding: clamp(1.5rem, 5vw, 2rem);
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .back-link {
            color: var(--text-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: var(--primary-color);
        }

        .form-header h1 {
            color: var(--primary-color);
            font-size: clamp(1.25rem, 4vw, 1.5rem);
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: var(--text-color);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 0.5rem;
        }

        .input-group {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            background: var(--light-bg);
            border: 2px solid transparent;
            border-radius: var(--border-radius);
            transition: all 0.2s;
            font-size: 1rem;
        }

        .form-control::placeholder {
            color: #9ca3af;
            opacity: 1;
        }

        .form-control:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
            outline: none;
        }

        .input-icon {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
            user-select: none;
        }

        .form-check input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            accent-color: var(--primary-color);
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            text-transform: uppercase;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            font-size: 0.875rem;
        }

        .btn-login:hover {
            background: var(--primary-hover);
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .warning-text {
            color: var(--error-color);
            text-align: right;
            font-size: 0.875rem;
            margin-top: 1rem;
        }

        .info-list {
            color: var(--error-color);
            font-size: 0.875rem;
            padding-left: 1.5rem;
            margin-top: 1rem;
        }

        .alert {
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            background-color: #fee2e2;
            color: var(--error-color);
            font-size: 0.875rem;
        }

        .invalid-feedback {
            color: var(--error-color);
            font-size: 0.75rem;
            margin-top: 0.25rem;
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 1.25rem;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <a href="/" class="back-link" aria-label="Back to home">
            <i class="fas fa-arrow-left"></i>
            <span>Home</span>
        </a>

        <div class="form-header">
            <h1>Account Login</h1>
        </div>

        <form method="POST" action="{{ route('login') }}" class="validate-form" autocomplete="off">
            @csrf
            
            @if($errors->any())
            <div class="alert" role="alert">
                {{ $errors->first() }}
            </div>
            @endif

            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-group">
                    <input 
                        id="username" 
                        type="text" 
                        class="form-control @error('username') is-invalid @enderror" 
                        name="username" 
                        value="{{ old('username') }}" 
                        placeholder="Enter your KKU-Mail"
                        required
                        aria-required="true"
                    >
                    <i class="fas fa-user input-icon" aria-hidden="true"></i>
                </div>
                @error('username')
                <span class="invalid-feedback" role="alert">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <input 
                        id="password" 
                        type="password" 
                        class="form-control" 
                        name="password" 
                        placeholder="Enter your password"
                        required
                        aria-required="true"
                    >
                    <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                </div>
            </div>

            <div class="form-check">
                <input type="checkbox" id="remember" name="remember" class="form-check-input">
                <label for="remember">Remember Me</label>
            </div>

            <button type="submit" class="btn-login">
                Log In
            </button>

            <p class="warning-text">*** หากลืมรหัสผ่าน ให้ติดต่อผู้ดูแลระบบ</p>

            <ul class="info-list">
                <li>สำหรับ Username ใช้ KKU-Mail ในการเข้าสู่ระบบ</li>
                <li>สำหรับนักศึกษาที่เข้าระบบเป็นครั้งแรกให้เข้าสู่ระด้วยรหัสนักศึกษา</li>
            </ul>
        </form>
    </div>
</body>
</html>