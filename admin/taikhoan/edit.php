<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$error = "";

$id = trim($_GET["id"] ?? "");

if ($id === "" || !ctype_digit($id)) {
    header("Location: index.php");
    exit;
}

$sql = "
    SELECT *
    FROM users
    WHERE ID = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

if (!$user) {
    header("Location: index.php");
    exit;
}

$username = $user["USERNAME"];
$role = $user["ROLE"];
$masv = $user["MASV"] ?? "";
$trangthai = (string)$user["TRANGTHAI"];

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
        $role === "" ||
        $trangthai === ""
    ) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    }

    elseif (!preg_match('/^[A-Za-z0-9._-]{4,50}$/', $username)) {
        $error = "Tên đăng nhập phải từ 4 đến 50 ký tự.";
    }

    elseif ($password !== "" && strlen($password) < 6) {
        $error = "Mật khẩu mới phải có ít nhất 6 ký tự.";
    }

    elseif (!in_array($role, ["admin", "student"], true)) {
        $error = "Vai trò không hợp lệ.";
    }

    elseif (!in_array($trangthai, ["0", "1"], true)) {
        $error = "Trạng thái không hợp lệ.";
    }

    elseif ($role === "student" && $masv === "") {
        $error = "Tài khoản sinh viên phải liên kết với sinh viên.";
    }

    elseif (
        (int)$id === (int)$_SESSION["ID"] &&
        $trangthai === "0"
    ) {
        $error = "Bạn không thể tự khóa tài khoản đang đăng nhập.";
    }

    else {

        $sqlCheckUsername = "
            SELECT ID
            FROM users
            WHERE USERNAME = ?
              AND ID != ?
            LIMIT 1
        ";

        $stmtCheckUsername = mysqli_prepare(
            $conn,
            $sqlCheckUsername
        );

        mysqli_stmt_bind_param(
            $stmtCheckUsername,
            "si",
            $username,
            $id
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
                          AND ID != ?
                        LIMIT 1
                    ";

                    $stmtCheckMasv = mysqli_prepare(
                        $conn,
                        $sqlCheckMasv
                    );

                    mysqli_stmt_bind_param(
                        $stmtCheckMasv,
                        "si",
                        $masv,
                        $id
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

                $trangthaiInt = (int)$trangthai;

                if ($role === "admin") {
                    $masv = null;
                }

                if ($password !== "") {

                    $passwordHash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $sqlUpdate = "
                        UPDATE users
                        SET
                            USERNAME = ?,
                            PASSWORD = ?,
                            ROLE = ?,
                            MASV = ?,
                            TRANGTHAI = ?
                        WHERE ID = ?
                    ";

                    $stmtUpdate = mysqli_prepare(
                        $conn,
                        $sqlUpdate
                    );

                    mysqli_stmt_bind_param(
                        $stmtUpdate,
                        "ssssii",
                        $username,
                        $passwordHash,
                        $role,
                        $masv,
                        $trangthaiInt,
                        $id
                    );

                } else {

                    $sqlUpdate = "
                        UPDATE users
                        SET
                            USERNAME = ?,
                            ROLE = ?,
                            MASV = ?,
                            TRANGTHAI = ?
                        WHERE ID = ?
                    ";

                    $stmtUpdate = mysqli_prepare(
                        $conn,
                        $sqlUpdate
                    );

                    mysqli_stmt_bind_param(
                        $stmtUpdate,
                        "sssii",
                        $username,
                        $role,
                        $masv,
                        $trangthaiInt,
                        $id
                    );
                }

                if (mysqli_stmt_execute($stmtUpdate)) {

                    if ((int)$id === (int)$_SESSION["ID"]) {
                        $_SESSION["USERNAME"] = $username;
                        $_SESSION["ROLE"] = $role;
                        $_SESSION["MASV"] = $masv;
                    }

                    header("Location: index.php?updated=1");
                    exit;

                } else {

                    $error =
                        "Không thể cập nhật tài khoản: "
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

    <title>Sửa tài khoản</title>

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
            Sửa tài khoản
        </h2>

        <div class="text-secondary mt-1">
            Cập nhật thông tin tài khoản
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
                    </label>

                    <input
                        type="text"
                        name="username"
                        maxlength="50"
                        value="<?php echo htmlspecialchars($username); ?>"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Mật khẩu mới
                    </label>

                    <input
                        type="password"
                        name="password"
                        minlength="6"
                        maxlength="100"
                        class="form-control"
                        placeholder="Để trống nếu không muốn đổi"
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Vai trò
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

                </div>

            </div>

            <div class="d-flex gap-2 mt-3">

                <button
                    type="submit"
                    class="btn btn-warning px-4"
                >
                    Lưu thay đổi
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