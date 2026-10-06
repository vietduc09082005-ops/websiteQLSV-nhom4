<?php
session_start();

if (isset($_SESSION["ID"]) && isset($_SESSION["ROLE"])) {
    if ($_SESSION["ROLE"] === "admin") {
        header("Location: admin/dashboard.php");
        exit;
    } elseif ($_SESSION["ROLE"] === "student" || $_SESSION["ROLE"] === "sinhvien") {
        header("Location: student/dashboard.php");
        exit;
    }
}

require_once "config/database.php";

$error = "";
$username = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($username) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
    } else {
        mysqli_report(MYSQLI_REPORT_OFF);

        $sqlUsers = "SELECT ID, USERNAME, PASSWORD, ROLE FROM users WHERE USERNAME = ? LIMIT 1";
        $stmtUsers = mysqli_prepare($conn, $sqlUsers);

        $loginSuccess = false;

        if ($stmtUsers) {
            mysqli_stmt_bind_param($stmtUsers, "s", $username);
            mysqli_stmt_execute($stmtUsers);
            $resultUsers = mysqli_stmt_get_result($stmtUsers);

            if ($user = mysqli_fetch_assoc($resultUsers)) {
                if (password_verify($password, $user["PASSWORD"]) || $password === $user["PASSWORD"] || md5($password) === $user["PASSWORD"]) {
                    $_SESSION["ID"] = $user["ID"];
                    $_SESSION["USERNAME"] = $user["USERNAME"];
                    $_SESSION["ROLE"] = $user["ROLE"];
                    $loginSuccess = true;

                    if ($user["ROLE"] === "admin") {
                        header("Location: admin/dashboard.php");
                    } else {
                        header("Location: student/dashboard.php");
                    }
                    exit;
                }
            }
            mysqli_stmt_close($stmtUsers);
        }
        if (!$loginSuccess) {
            $sqlSV = "SELECT * FROM sinhvien WHERE MASV = ? OR ma_sv = ? LIMIT 1";
            $stmtSV = mysqli_prepare($conn, $sqlSV);

            if ($stmtSV) {
                mysqli_stmt_bind_param($stmtSV, "ss", $username, $username);
                mysqli_stmt_execute($stmtSV);
                $resultSV = mysqli_stmt_get_result($stmtSV);

                if ($sv = mysqli_fetch_assoc($resultSV)) {
                    $dbPass = $sv["MATKHAU"] ?? $sv["matkhau"] ?? "123456";

                    if ($password === "123456" || $password === $dbPass || password_verify($password, $dbPass)) {
                        $_SESSION["ID"] = $sv["MASV"] ?? $sv["ma_sv"];
                        $_SESSION["USERNAME"] = $sv["TENSV"] ?? $sv["hoten"] ?? $username;
                        $_SESSION["ROLE"] = "student";

                        header("Location: student/dashboard.php");
                        exit;
                    } else {
                        $error = "Mật khẩu không chính xác.";
                    }
                } else {
                    $error = "Tên đăng nhập hoặc Mã sinh viên không tồn tại.";
                }
                mysqli_stmt_close($stmtSV);
            } else {
                $error = "Lỗi kết nối CSDL.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản lý Sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            background: #ffffff;
        }
        .login-header {
            background: #2b3648;
            color: #ffffff;
            padding: 24px;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            text-align: center;
        }
        .login-header h4 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .login-body {
            padding: 28px;
        }
        .btn-custom {
            background-color: #2b3648;
            border-color: #2b3648;
            color: #fff;
            padding: 10px;
            font-weight: 500;
        }
        .btn-custom:hover {
            background-color: #1e2634;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="login-header">
        <h4>CỔNG THÔNG TIN ĐĂNG NHẬP</h4>
        <small class="text-white-50">Dành cho Admin & Sinh viên</small>
    </div>
    
    <div class="login-body">
        <?php if ($error !== ""): ?>
            <div class="alert alert-danger py-2 fs-6">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label fw-medium">Tên đăng nhập / Mã SV</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    placeholder="Nhập tên tài khoản hoặc mã sinh viên"
                    value="<?php echo htmlspecialchars($username); ?>" 
                    required 
                    autofocus
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-medium">Mật khẩu</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Mật khẩu sinh viên: 123456" 
                    required
                >
            </div>

            <button type="submit" class="btn btn-custom w-100">
                Đăng nhập
            </button>
        </form>
    </div>
</div>

</body>
</html>