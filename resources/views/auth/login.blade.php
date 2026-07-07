<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
        }

        .card {
            width: min(100% - 2rem, 420px);
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.12);
        }

        h1 {
            margin: 0 0 1.5rem;
            font-size: 1.5rem;
        }

        label {
            display: block;
            margin: 0.85rem 0 0.35rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 0.85rem 1rem;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 1rem;
        }

        button {
            width: 100%;
            margin-top: 1.25rem;
            padding: 0.9rem 1rem;
            border: 0;
            border-radius: 10px;
            background: #111827;
            color: white;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
        }

        .error {
            margin-top: 1rem;
            color: #b91c1c;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Iniciar sesión</h1>

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password" required>

            <button type="submit">Entrar</button>
        </form>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif
    </div>
</body>
</html>
