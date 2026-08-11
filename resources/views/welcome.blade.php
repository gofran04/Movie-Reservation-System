<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Reservation System API</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:Arial, Helvetica, sans-serif;
        }

        body{
            background:#f4f6f9;
            color:#222;
            line-height:1.6;
        }

        header{
            background:#111827;
            color:white;
            padding:70px 20px;
            text-align:center;
        }

        header h1{
            font-size:42px;
            margin-bottom:15px;
        }

        header p{
            font-size:18px;
            color:#d1d5db;
            max-width:800px;
            margin:auto;
        }

        .buttons{
            margin-top:35px;
        }

        .btn{
            display:inline-block;
            margin:8px;
            padding:12px 22px;
            border-radius:8px;
            text-decoration:none;
            color:white;
            background:#2563eb;
            transition:.2s;
        }

        .btn:hover{
            background:#1d4ed8;
        }

        section{
            max-width:1100px;
            margin:50px auto;
            padding:0 20px;
        }

        h2{
            margin-bottom:20px;
            color:#111827;
        }

        .grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
            gap:20px;
        }

        .card{
            background:white;
            border-radius:10px;
            padding:20px;
            box-shadow:0 2px 10px rgba(0,0,0,.08);
        }

        ul{
            padding-left:20px;
        }

        li{
            margin:8px 0;
        }

        code{
            background:#eee;
            padding:2px 5px;
            border-radius:4px;
        }

        pre{
            background:#111827;
            color:#fff;
            padding:20px;
            border-radius:8px;
            overflow:auto;
        }

        footer{
            margin-top:60px;
            padding:30px;
            text-align:center;
            background:#111827;
            color:white;
        }
    </style>
</head>

<body>

<header>

    <h1>🎬 Movie Reservation System API</h1>

    <p>
        A Dockerized Laravel REST API for managing movies, cinemas, halls,
        showtimes, reservations and Stripe payments.
    </p>

    <div class="buttons">

        <a class="btn" href="/up">
            API Health
        </a>

        <a class="btn" href="https://github.com/gofran04/Movie-Reservation-System/tree/dev" target="_blank">
            GitHub
        </a>

    </div>

</header>

<section>

    <h2>About</h2>

    <div class="card">

        <p>
            This project is a portfolio-grade RESTful Movie Reservation System
            developed with Laravel 12. It demonstrates authentication,
            authorization, seat reservation, online payment integration,
            Docker deployment and production hosting.
        </p>

    </div>

</section>

<section>

    <h2>Features</h2>

    <div class="grid">

        <div class="card">

            <h3>Customer</h3>

            <ul>
                <li>User Registration & Login</li>
                <li>Browse Movies</li>
                <li>Browse Showtimes</li>
                <li>View Seat Availability</li>
                <li>Create Reservations</li>
                <li>Stripe Payments</li>
                <li>Cancel Reservations</li>
            </ul>

        </div>

        <div class="card">

            <h3>Administration</h3>

            <ul>
                <li>User Management</li>
                <li>Movie Management</li>
                <li>Cinema Management</li>
                <li>Hall Management</li>
                <li>Seat Management</li>
                <li>Showtime Management</li>
                <li>Role & Permission Management</li>
            </ul>

        </div>

    </div>

</section>

<section>

    <h2>Technology Stack</h2>

    <div class="grid">

        <div class="card">Laravel 12</div>
        <div class="card">PHP 8.3</div>
        <div class="card">PostgreSQL</div>
        <div class="card">Sanctum</div>
        <div class="card">Stripe</div>
        <div class="card">Docker</div>
        <div class="card">Nginx</div>
        <div class="card">Redis</div>
        <div class="card">Neon PostgreSQL</div>
        <div class="card">Render</div>
        <div class="card">cron-job.org</div>

    </div>

</section>

<section>

    <h2>Important API Endpoints</h2>

<pre>POST   /api/register
POST   /api/login

GET    /api/movies
GET    /api/movies/{movie}

GET    /api/cinemas
GET    /api/cinemas/{cinema}

GET    /api/showtimes
GET    /api/showtimes/{showtime}

GET    /api/showtimes/{showtime}/available-seats

POST   /api/reservations
POST   /api/payments/{reservation}</pre>

</section>

<section>

    <h2>Demo Administrator</h2>

    <div class="card">

        <p><strong>Email</strong></p>

        <code>GeneralManager@mail.com</code>

        <br><br>

        <p><strong>Password</strong></p>

        <code>admin123</code>

    </div>

</section>

<footer>

    <h3>Movie Reservation System</h3>

    <p>
        Laravel • Docker • PostgreSQL • Stripe • Render
    </p>

</footer>

</body>
</html>