<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pure Power | Maintenance</title>

    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html,
    body {
        height: 100%;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #000;
        color: #fff;
        overflow: hidden;
    }

    .background {
        position: fixed;
        inset: 0;

        background:
            linear-gradient(rgba(0, 0, 0, .72), rgba(0, 0, 0, .82)),
            url('/smoke.webp') center center / cover no-repeat;

        z-index: -2;
    }

    .overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .35);
        z-index: -1;
    }

    .container {
        min-height: 100vh;

        display: flex;
        align-items: center;
        justify-content: center;

        text-align: center;

        padding: 2rem;
    }

    .content {
        max-width: 700px;
    }

    .logo {
        width: 220px;
        max-width: 70%;
        margin-bottom: 2rem;
    }

    h1 {
        font-family: "IM FELL English SC", serif;
        font-size: clamp(3rem, 6vw, 5rem);
        letter-spacing: .18em;
        color: #d8a84b;
        text-transform: uppercase;

        text-shadow:
            0 0 8px rgba(216, 168, 75, .4),
            2px 2px 0 #555;

        margin-bottom: .75rem;
    }

    h2 {
        font-size: 1.6rem;
        font-weight: 600;
        color: #ffffff;

        margin-bottom: 1.5rem;
    }

    p {
        color: #d4d4d4;
        line-height: 1.8;
        font-size: 1rem;
        max-width: 600px;
        margin: auto;
    }

    .divider {
        width: 120px;
        height: 2px;
        background: #d8a84b;
        margin: 2rem auto;
        box-shadow: 0 0 10px rgba(216, 168, 75, .5);
    }
    </style>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English+SC&display=swap" rel="stylesheet">
</head>

<body>

    <div class="background"></div>
    <div class="overlay"></div>

    <div class="container">

        <div class="content">

            <img class="logo" src="/logo.png" alt="Pure Power">

            <h1>Pure Power</h1>

            <h2>We'll Be Back Shortly</h2>

            <div class="divider"></div>

            <p>
                We're currently carrying out scheduled maintenance to improve the
                Pure Power website.
                <br><br>
                Please check back soon. Thank you for your patience and support.
            </p>

        </div>

    </div>

</body>

</html>