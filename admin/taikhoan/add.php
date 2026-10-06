<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$error = "";

$username = "";
$role = "student";
$masv = "";
$trangthai = "1";

$sqlSinhVien = "
    SELECT MASV, HODEM, TEN
    FROM sinhvien
    ORDER BY MASV ASC
";

$resultSinhVien = mysqli_query($conn, $sqlSinhVien);

if (!$resultSinhVien) {
    die("Lỗi lấy danh sách sinh viên: " . mysqli_error($conn));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = trim($_POST["role"] ?? "");
    $masv = trim($_POST["masv"] ?? "");
    $trangthai = trim($_POST["trangthai"] ?? "");

    if (
        $username === "" ||
        $password === "" ||
        $role === "" ||
        $trangthai === ""
    ) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    }

    elseif (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $username)) {
        $error = "Tên đăng nhập phải từ 4 đến 50 ký tự và chỉ gồm chữ, số, dấu chấm, gạch dưới hoặc gạch ngang.";
    }

    elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    }

    elseif (!in_array($role, ["admin", "student"], true)) {
        $error = "Vai trò không hợp lệ.";
    }

    elseif (!in_array($trangthai, ["0", "1"], true)) {
        $error = "Trạng thái tài khoản không hợp lệ.";
    }

    elseif ($role === "student" && $masv === "") {
        $error = "Tài khoản sinh viên phải liên kết với một sinh viên.";
    }

    else {

        $sqlCheckUsername = "
            SELECT ID
            FROM users
            WHERE USERNAME = ?
            LIMIT 1
        ";

        $stmtCheckUsername = mysqli_prepare(
            $conn,
            $sqlCheckUsername
        );

        mysqli_stmt_bind_param(
            $stmtCheckUsername,
            "s",
            $username
        );

        mysqli_stmt_execute($stmtCheckUsername);

        $resultCheckUsername =
            mysqli_stmt_get_result($stmtCheckUsername);

        if (mysqli_num_rows($resultCheckUsername) > 0) {

            $error = "Tên đăng nhập đã tồn tại.";

        } else {

            if ($role === "student") {

                $sqlCheckSinhVien = "
                    SELECT MASV
                    FROM sinhvien
                    WHERE MASV = ?
                    LIMIT 1
                ";

                $stmtCheckSinhVien = mysqli_prepare(
                    $conn,
                    $sqlCheckSinhVien
                );

                mysqli_stmt_bind_param(
                    $stmtCheckSinhVien,
                    "s",
                    $masv
                );

                mysqli_stmt_execute($stmtCheckSinhVien);

                $resultCheckSinhVien =
                    mysqli_stmt_get_result($stmtCheckSinhVien);

                if (mysqli_num_rows($resultCheckSinhVien) == 0) {

                    $error = "Sinh viên không tồn tại.";

                } else {

                    $sqlCheckMasv = "
                        SELECT ID
                        FROM users
                        WHERE MASV = ?
                        LIMIT 1
                    ";

                    $stmtCheckMasv = mysqli_prepare(
                        $conn,
                        $sqlCheckMasv
                    );

                    mysqli_stmt_bind_param(
                        $stmtCheckMasv,
                        "s",
                        $masv
                    );

                    mysqli_stmt_execute($stmtCheckMasv);

                    $resultCheckMasv =
                        mysqli_stmt_get_result($stmtCheckMasv);

                    if (mysqli_num_rows($resultCheckMasv) > 0) {
                        $error = "Sinh viên này đã có tài khoản.";
                    }
                }
            }

            if ($error === "") {

                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $trangthaiInt = (int)$trangthai;

                if ($role === "admin") {

                    $sqlInsert = "
                        INSERT INTO users
                        (
                            USERNAME,
                            PASSWORD,
                            ROLE,
                            MASV,
                            TRANGTHAI
                        )
                        VALUES (?, ?, ?, NULL, ?)
                    ";

                    $stmtInsert = mysqli_prepare(
                        $conn,
                        $sqlInsert
                    );

                    mysqli_stmt_bind_param(
                        $stmtInsert,
                        "sssi",
                        $username,
                        $passwordHash,
                        $role,
                        $trangthaiInt
                    );

                } else {

                    $sqlInsert = "
                        INSERT INTO users
                        (
                            USERNAME,
                            PASSWORD,
                            ROLE,
                            MASV,
                            TRANGTHAI
                        )
                        VALUES (?, ?, ?, ?, ?)
                    ";

                    $stmtInsert = mysqli_prepare(
                        $conn,
                        $sqlInsert
                    );

                    mysqli_stmt_bind_param(
                        $stmtInsert,
                        "ssssi",
                        $username,
                        $passwordHash,
                        $role,
                        $masv,
                        $trangthaiInt
                    );
                }

                if (mysqli_stmt_execute($stmtInsert)) {

                    header("Location: index.php?success=1");
                    exit;

                } else {

                    $error =
                        "Không thể thêm tài khoản: "
                        . mysqli_error($conn);
                }
            }
        }
    }
}

$activePage = "taikhoan";
?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Thêm tài khoản</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../../assets/css/admin.css"
    >

</head>

<body>

<?php
require_once "../includes/sidebar.php";
?>

<div class="main-content">

    <div class="mb-4">

        <h2 class="page-title">
            Thêm tài khoản
        </h2>

        <div class="text-secondary mt-1">
            Tạo tài khoản đăng nhập mới
        </div>

    </div>

    <div class="form-card">

        <?php if ($error !== "") { ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Tên đăng nhập
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="username"
                        maxlength="50"
                        value="<?php echo htmlspecialchars($username); ?>"
                        class="form-control"
                        placeholder="Ví dụ: sv001"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Mật khẩu
                        <span class="required">*</span>
                    </label>

                    <input
                        type="password"
                        name="password"
                        minlength="6"
                        maxlength="100"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Vai trò
                        <span class="required">*</span>
                    </label>

                    <select
                        name="role"
                        id="role"
                        class="form-select"
                        required
                    >

                        <option
                            value="student"
                            <?php echo $role === "student" ? "selected" : ""; ?>
                        >
                            Sinh viên
                        </option>

                        <option
                            value="admin"
                            <?php echo $role === "admin" ? "selected" : ""; ?>
                        >
                            Admin
                        </option>

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Trạng thái
                        <span class="required">*</span>
                    </label>

                    <select
                        name="trangthai"
                        class="form-select"
                        required
                    >

                        <option
                            value="1"
                            <?php echo $trangthai === "1" ? "selected" : ""; ?>
                        >
                            Hoạt động
                        </option>

                        <option
                            value="0"
                            <?php echo $trangthai === "0" ? "selected" : ""; ?>
                        >
                            Khóa
                        </option>

                    </select>

                </div>

                <div
                    class="col-12 mb-3"
                    id="masvBox"
                >

                    <label class="form-label">
                        Sinh viên
                    </label>

                    <select
                        name="masv"
                        id="masv"
                        class="form-select"
                    >

                        <option value="">
                            -- Chọn sinh viên --
                        </option>

                        <?php
                        mysqli_data_seek($resultSinhVien, 0);

                        while (
                            $sv = mysqli_fetch_assoc($resultSinhVien)
                        ) {
                        ?>

                            <option
                                value="<?php echo htmlspecialchars($sv["MASV"]); ?>"
                                <?php echo $masv === $sv["MASV"] ? "selected" : ""; ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $sv["MASV"]
                                    . " - "
                                    . $sv["HODEM"]
                                    . " "
                                    . $sv["TEN"]
                                );
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                    <div class="form-text">
                        Chỉ bắt buộc khi vai trò là Sinh viên.
                    </div>

                </div>

            </div>

            <div class="d-flex gap-2 mt-3">

                <button
                    type="submit"
                    class="btn btn-primary px-4"
                >
                    Thêm tài khoản
                </button>

                <a
                    href="index.php"
                    class="btn btn-outline-secondary px-4"
                >
                    Quay lại
                </a>

            </div>

        </form>

    </div>

</div>

<script>

const roleSelect = document.getElementById("role");
const masvBox = document.getElementById("masvBox");
const masvSelect = document.getElementById("masv");

function updateMasvField() {

    if (roleSelect.value === "admin") {

        masvBox.style.display = "none";
        masvSelect.required = false;
        masvSelect.value = "";

    } else {

        masvBox.style.display = "block";
        masvSelect.required = true;

    }
}

roleSelect.addEventListener(
    "change",
    updateMasvField
);

updateMasvField();

</script>

</body>

</html>