<?php
/**
 * SHAKE NATURE - LOGIN
 * Variáveis recebidas do AuthController: $erro, $email, $csrf
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<link rel="icon" type="image/png" href="<?= h(sn_asset('assets/img/logo.png')) ?>">
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Shake Nature - Login</title>

<link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@600;700&family=Nunito:wght@400;600;700&family=Pacifico&display=swap" rel="stylesheet">

<style>
:root {
    --bg: #F8EEDC;
    --card: #FAF3E6;
    --ink: #A07F63;
    --yellow: #F4C26B;
    --orange: #F2A25C;
    --peach: #FBE3CD;
    --line: #E3D6C6;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: var(--bg);
    color: var(--ink);
    font-family: 'Nunito', sans-serif;
    overflow-x: hidden;
}

.deco {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

.deco svg {
    position: absolute;
}

.blob-tl { top: -60px; left: -60px; width: 340px; }
.blob-br { bottom: -70px; right: -60px; width: 420px; }
.shake-bg { bottom: -30px; left: -40px; width: 230px; color: #F8CE9E; opacity: .75; }
.sparks-tr { top: 50px; right: 60px; width: 140px; }
.sparks-r { top: 48%; right: 70px; width: 110px; }
.heart-l { top: 34%; left: 11%; width: 46px; }
.heart-r { top: 64%; right: 12%; width: 34px; }

.page {
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 32px 16px;
}

.card {
    width: 100%;
    max-width: 560px;
    padding: 34px 48px 44px;
    background: var(--card);
    border-radius: 36px;
    box-shadow: 0 20px 60px rgba(214,170,110,.25);
}

.logo {
    text-align: center;
}

.logo .cup {
    width: 110px;
    height: 110px;
    object-fit: contain;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 8px 22px rgba(214,170,110,.3);
    display: block;
    margin: 0 auto 10px;
}

.logo .t1 {
    font-family: 'Fredoka', sans-serif;
    font-size: 4.2rem;
    line-height: 1;
    margin: 0;
    background: linear-gradient(180deg,#F6CF78,#F2B65F);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.logo .t2 {
    font-family: 'Pacifico', cursive;
    font-size: 3.1rem;
    line-height: 1;
    margin: -12px 0 0;
    background: linear-gradient(180deg,#F4A867,#EE8A55);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.sub {
    text-align: center;
    font-size: 1.15rem;
    margin: 10px 0 28px;
}

label {
    display: block;
    font-weight: 700;
    margin-bottom: 8px;
}

.field {
    margin-bottom: 20px;
}

.input {
    position: relative;
}

.input svg {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    width: 28px;
    height: 28px;
    color: var(--orange);
}

.input input {
    width: 100%;
    height: 56px;
    padding: 0 16px 0 62px;
    border: 2px solid var(--line);
    border-radius: 16px;
    background: transparent;
    font: inherit;
    color: #7A5C43;
}

.input input:focus {
    outline: 3px solid rgba(242,162,92,.45);
    border-color: var(--orange);
}

.erro, .ok {
    border-radius: 12px;
    padding: 10px 14px;
    margin-bottom: 18px;
    font-weight: 600;
}

.erro {
    background: #FDE7E2;
    color: #B3261E;
}

.ok {
    background: #E6F4E4;
    color: #2E6B2A;
}

.btn {
    display: block;
    width: 100%;
    height: 58px;
    border: 0;
    border-radius: 999px;
    font-family: 'Nunito', sans-serif;
    font-weight: 700;
    font-size: 1.2rem;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    line-height: 58px;
    transition: .15s;
}

.btn:hover {
    transform: translateY(-2px);
}

.btn-main {
    background: linear-gradient(90deg,#F2BA68,#F5C97E);
    color: white;
    margin-top: 6px;
}

.btn-alt {
    background: var(--peach);
    color: #EE8A45;
    margin-top: 14px;
}

@media (max-width: 520px) {
    .card {
        padding: 28px 24px 36px;
    }
}
</style>
</head>

<body>

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="shake" viewBox="0 0 100 140">
        <g fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M30 50C26 36 40 30 48 18C54 26 70 28 70 42C70 47 67 50 67 50"/>
            <rect x="25" y="52" width="50" height="9" rx="4.5"/>
            <path d="M30 61L36 123Q37 130 44 130H56Q63 130 64 123L70 61"/>
            <path d="M43 72L46 118M57 72L54 118"/>
            <path d="M60 32L68 8H84"/>
        </g>
    </symbol>
</svg>

<div class="deco">
    <svg class="blob-tl" viewBox="0 0 340 300">
        <path d="M0 0H300C270 40 240 80 190 110C140 140 90 120 50 170C30 195 15 230 0 260Z" fill="#F8DFAE" opacity=".7"/>
        <path d="M0 200C60 190 120 130 200 120C250 114 300 70 340 0" fill="none" stroke="#F2A25C" stroke-width="3"/>
    </svg>

    <svg class="blob-br" viewBox="0 0 420 300">
        <path d="M420 40C330 60 280 140 200 170C140 190 60 180 0 300H420Z" fill="#F7C9A0" opacity=".85"/>
        <path d="M130 300C200 250 300 230 360 150C390 110 410 80 420 60" fill="none" stroke="#F2A25C" stroke-width="3"/>
    </svg>

    <svg class="shake-bg"><use href="#shake"/></svg>

    <svg class="sparks-tr" viewBox="0 0 140 110" fill="#F6D58A">
        <path d="M20 30C40 25 60 45 66 60C45 62 28 48 20 30Z"/>
        <path d="M82 6C96 8 98 40 94 56C80 48 76 24 82 6Z"/>
        <path d="M130 20C124 38 112 52 104 54C104 40 114 24 130 20Z"/>
    </svg>

    <svg class="sparks-r" viewBox="0 0 110 120" fill="#F6D58A">
        <path d="M70 6C70 30 50 56 28 70C26 44 44 14 70 6Z"/>
        <path d="M104 70C86 90 56 104 20 108C36 88 70 70 104 70Z"/>
    </svg>

    <svg class="heart-l" viewBox="0 0 46 60" fill="#F6C777">
        <path d="M4 12C14 12 20 24 22 34C28 24 34 8 44 4C42 24 34 44 22 56C10 46 4 30 4 12Z"/>
    </svg>

    <svg class="heart-r" viewBox="0 0 46 60" fill="#F6C777">
        <path d="M4 12C14 12 20 24 22 34C28 24 34 8 44 4C42 24 34 44 22 56C10 46 4 30 4 12Z"/>
    </svg>
</div>

<div class="page">
    <main class="card">

        <div class="logo">
            <img class="cup" src="<?= h(sn_asset('assets/img/logo.png')) ?>" alt="Shake Nature">
            <h1 class="t1">SHAKE</h1>
            <p class="t2">nature</p>
        </div>

        <p class="sub">Acesso ao sistema</p>

        <?php if ($erro): ?>
            <div class="erro" role="alert"><?= h($erro) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= h(sn_url_login()) ?>">

                <input type="hidden" name="csrf" value="<?= h($csrf) ?>">

                <div class="field">
                    <label for="email">Email</label>
                    <div class="input">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="M3 7l9 6 9-6"/>
                        </svg>
                        <input id="email" name="email" type="email" placeholder="exemplo@email.com" value="<?= h($email) ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label for="senha">Senha</label>
                    <div class="input">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="11" width="14" height="10" rx="2"/>
                            <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
                            <circle cx="12" cy="16" r="1" fill="currentColor"/>
                        </svg>
                        <input id="senha" name="senha" type="password" placeholder="••••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-main">Entrar</button>

            </form>

    </main>
</div>

</body>
</html>
