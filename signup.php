<?php 

    require_once ("db_connection/conn.php");

    if (student_is_logged_in()) {
        redirect(PROOT . 'board');
    }

    $output = '';
    $student_id = ((isset($_POST['student_id']) && !empty($_POST['student_id'])) ? sanitize($_POST['student_id']) : '');
    $fullname = ((isset($_POST['fullname']) && !empty($_POST['fullname'])) ? sanitize($_POST['fullname']) : '');
    $level = ((isset($_POST['level']) && !empty($_POST['level'])) ? sanitize($_POST['level']) : '');
    $email = ((isset($_POST['email']) && !empty($_POST['email'])) ? sanitize($_POST['email']) : '');

    if (isset($_POST['submit_form'])) {

        $password = sanitize($_POST['user_password']);
        $password_repeat = sanitize($_POST['repeat_password']);
        $createdAt = date('Y-m-d H:i:s A');
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $email_q = $conn->query("SELECT * FROM students WHERE email = '" . $email . "'")->rowCount();
        if ($email_q > 0) {
            $output =  'Student email already in use!';
        } else {
            $id_q = $conn->query("SELECT * FROM students WHERE student_id = '" . $student_id . "'")->rowCount();
            if ($id_q > 0) {
                $output =  'Student ID already in use';
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // code...
                $output = 'Invalid email provided!';
            }

            if (strlen($password) < 6) {
                $output = 'Password must be at least 6 characters!';
            }

            if ($password != $password_repeat) {
                $output = 'The new password and confirm new password does not match!';
            }

            if (empty($output)) {
                // code...
                $data = [$student_id, $fullname, $email, $level, $password_hash, $createdAt];
                $query = "
                    INSERT INTO students (student_id, fullname, email, level, password, createdAt) 
                    VALUES (?, ?, ?, ?, ?, ?); 
                ";
                $statement = $conn->prepare($query);
                $result = $statement->execute($data);
                $user_id = $conn->lastInsertId();

                if (isset($result)) {
                    $_SESSION['flash_success'] = 'Account successfully setup, go ahead and login';
                    redirect(PROOT . 'login');
                }
            }

        }
    }

?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="auto">
    <head>
        <script src="dist/js/color-modes.js"></script>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Sign up . Student Complaint System</title>
        <link href="dist/css/bootstrap.min.css" rel="stylesheet">
        <meta name="theme-color" content="#712cf9">

        <style type="text/css">
            .bd-placeholder-img {
                font-size: 1.125rem;
                text-anchor: middle;
                -webkit-user-select: none;
                -moz-user-select: none;
                user-select: none;
            }

            @media (min-width: 768px) {
                .bd-placeholder-img-lg {
                    font-size: 3.5rem;
                }
            }

            .b-example-divider {
                width: 100%;
                height: 3rem;
                background-color: rgba(0, 0, 0, .1);
                border: solid rgba(0, 0, 0, .15);
                border-width: 1px 0;
                box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
            }

            .b-example-vr {
                flex-shrink: 0;
                width: 1.5rem;
                height: 100vh;
            }

            .bi {
                vertical-align: -.125em;
                fill: currentColor;
            }

            .nav-scroller {
                position: relative;
                z-index: 2;
                height: 2.75rem;
                overflow-y: hidden;
            }

            .nav-scroller .nav {
                display: flex;
                flex-wrap: nowrap;
                padding-bottom: 1rem;
                margin-top: -1px;
                overflow-x: auto;
                text-align: center;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }

            .btn-bd-primary {
                --bd-violet-bg: #712cf9;
                --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

                --bs-btn-font-weight: 600;
                --bs-btn-color: var(--bs-white);
                --bs-btn-bg: var(--bd-violet-bg);
                --bs-btn-border-color: var(--bd-violet-bg);
                --bs-btn-hover-color: var(--bs-white);
                --bs-btn-hover-bg: #6528e0;
                --bs-btn-hover-border-color: #6528e0;
                --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
                --bs-btn-active-color: var(--bs-btn-hover-color);
                --bs-btn-active-bg: #5a23c8;
                --bs-btn-active-border-color: #5a23c8;
            }

            .bd-mode-toggle {
                z-index: 1500;
            }

            .bd-mode-toggle .dropdown-menu .active .bi {
                display: block !important;
            }
            
            .border-dashed { --bs-border-style: dashed; }
        </style>
    </head>
    <body>
        <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
            <symbol id="check2" viewBox="0 0 16 16">
            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
          </symbol>
          <symbol id="circle-half" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
          </symbol>
          <symbol id="moon-stars-fill" viewBox="0 0 16 16">
            <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
            <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
          </symbol>
          <symbol id="sun-fill" viewBox="0 0 16 16">
            <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
          </symbol>
        </svg>

        <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
            <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
                id="bd-theme"
                type="button"
                aria-expanded="false"
                data-bs-toggle="dropdown"
                aria-label="Toggle theme (auto)">
                <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#circle-half"></use></svg>
                <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
                        <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#sun-fill"></use></svg>
                        Light
                        <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                        <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
                        Dark
                        <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
                        <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#circle-half"></use></svg>
                        Auto
                        <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
                    </button>
                </li>
            </ul>
        </div>

        <div class="container">
            <nav>
                <ul class="nav justify-content-center">
                    <li class="nav-item"><a href="index" class="nav-link px-2 text-body-secondary">Home</a></li>
                    <li class="nav-item"><a href="signup" class="nav-link px-2 text-body-secondary">Signup</a></li>
                    <li class="nav-item"><a href="login" class="nav-link px-2 text-body-secondary">Login</a></li>
                    <li class="nav-item"><a href="about" class="nav-link px-2 text-body-secondary">About</a></li>
                </ul>
            </nav>
        </div>
        <?= $flash; ?>
        <div class="container my-4">
            <div class="p-3 text-center bg-body-tertiary rounded-3">
                <img src="dist/media/logo.png" class="bi mt-4 mb-3" style="color: var(--bs-indigo);" width="100" height="100">
                <h1 class="text-body-emphasis">Student complaint system</h1>
                <form method="POST" class="col-lg-8 mx-auto fs-5 text-muted">
                    <p class="mb-3">
                        Create an account. 
                        <p class="text-danger"><?= $output; ?></p>
                        <div class="mb-3">
                            <input class="form-control" type="text" id="student_id" name="student_id" placeholder="Student ID" value="<?= $student_id; ?>" required />
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="text" id="fullname" name="fullname" placeholder="Full name" value="<?= $fullname; ?>" required />
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="text" id="level" name="level" placeholder="Level" value="<?= $level; ?>" required />
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="email" id="email" name="email" placeholder="Email" value="<?= $email; ?>" required />
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="password" id="user_password" name="user_password" placeholder="Password" required />
                        </div>
                        <div class="mb-0">
                            <input class="form-control" type="password" id="repeat_password" name="repeat_password" placeholder="Repeat password" required />
                        </div>
                    </p>
                    <div class="d-inline-flex gap-2 mb-5">
                        <button class="d-inline-flex align-items-center btn btn-primary btn-lg px-4 rounded-pill" type="submit" name="submit_form">
                            Setup account
                            <svg class="bi ms-2" width="24" height="24"><use xlink:href="#arrow-right-short"/></svg>
                        </button>
                        <a class="btn btn-outline-secondary btn-lg px-4 rounded-pill" href="login">
                            Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="dist/js/popper.min.js"></script>
    <script src="dist/js/bootstrap.min.js"></script>
</body>
</html>