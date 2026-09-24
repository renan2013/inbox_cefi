<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Exitosa - UNELA</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #5fb230;
            --primary-dark: #4e9a26;
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --border-dark: #334155;
            --text-light: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .success-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 1.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        .success-icon {
            font-size: 4.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

    <div class="success-card">
        <i class="bi bi-patch-check-fill success-icon"></i>
        <h2 class="fw-bold mb-3">¡Inscripción Recibida!</h2>
        <p class="text-white-50 mb-4">Hemos registrado tu solicitud de admisión para el curso libre. Un asesor de UNELA se pondrá en contacto contigo muy pronto para formalizar tu ingreso.</p>
        <div class="d-grid">
            <a href="https://unela.ac.cr" class="btn btn-success py-3 rounded-3 fw-bold" style="background-color: var(--primary); border: none;">
                Volver a UNELA
            </a>
        </div>
    </div>

</body>
</html>
