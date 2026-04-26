<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafraWise — Página não encontrada</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/notfound/notfound.css">
</head>

<body>

    <div class="nf-grid"></div>
    <div class="nf-glow"></div>

    <div class="nf-container">

        <!-- Logo -->
        <a href="/public/" class="nf-logo">
            <div class="nf-logo-icon">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- monitor -->
                    <rect x="4" y="14" width="40" height="26" rx="4" fill="#0f2318" stroke="#40916c" stroke-width="1.5" />
                    <rect x="7" y="17" width="34" height="20" rx="2.5" fill="#0a1c10" />
                    <!-- pé e base -->
                    <rect x="21" y="40" width="6" height="4" rx="1.5" fill="#2d6a4f" />
                    <rect x="15" y="44" width="18" height="2.5" rx="1.5" fill="#40916c" />
                    <!-- caule -->
                    <path d="M24 36 Q23 28 24 18 Q25 12 23 6" stroke="#40916c" stroke-width="2" stroke-linecap="round" />
                    <!-- folha esquerda -->
                    <path d="M24 26 Q16 22 13 14 Q20 12 24 26Z" fill="#2d6a4f" />
                    <!-- folha direita -->
                    <path d="M24 20 Q32 16 34 8 Q27 6 24 20Z" fill="#40916c" />
                    <!-- broto topo -->
                    <path d="M23 10 Q22 4 23 0 Q25 4 23 10Z" fill="#74c69d" />
                </svg>
            </div>
            <span class="nf-logo-name">SafraWise</span>
        </a>

        <!-- 404 grande + planta -->
        <div class="nf-plant-wrap">
            <div class="nf-code">404</div>

            <!-- Planta SVG animada -->
            <svg class="nf-plant" width="90" height="110" viewBox="0 0 90 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Caule -->
                <path d="M45 105 Q43 80 46 55 Q47 35 44 12" stroke="#40916c" stroke-width="2.5" stroke-linecap="round" fill="none" />
                <!-- Folha esquerda grande -->
                <path d="M44 65 Q22 55 14 28 Q36 22 44 65Z" fill="#2d6a4f" />
                <!-- Folha direita grande -->
                <path d="M46 48 Q68 35 74 10 Q52 6 46 48Z" fill="#40916c" />
                <!-- Folha esquerda pequena -->
                <path d="M43 38 Q28 30 24 12 Q40 10 43 38Z" fill="#52b788" opacity="0.8" />
                <!-- Solo -->
                <ellipse cx="45" cy="107" rx="26" ry="5" fill="#1a3a28" />
            </svg>
        </div>

        <h1 class="nf-title">
            Esta página se <em>perdeu no campo</em>
        </h1>

        <p class="nf-desc">
            A rota que você tentou acessar não existe ou foi removida.
            Verifique o endereço ou volte para o início.
        </p>

        <div class="nf-actions">
            <a href="/public/" class="btn-primary">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
                Ir para o início
            </a>

            <a href="javascript:history.back()" class="btn-ghost">
                <svg viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Voltar
            </a>
        </div>

        <!-- URL que gerou o 404 -->
        <?php if (!empty($_SERVER['REQUEST_URI'])): ?>
            <div class="nf-url-hint">
                Rota solicitada:
                <span><?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></span>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>