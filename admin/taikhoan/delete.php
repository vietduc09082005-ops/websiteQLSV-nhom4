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

if ((int)$id === (int)$_SESSION["ID"]) {
    header("Location: index.php?selfdelete=1");
    exit;
}

$sql = "
    SELECT
        ID,
        USERNAME,
        ROLE,
        MASV,
        TRANGTHAI
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

$sinhvien = null;

if (!empty($user["MASV"])) {

    $sqlSinhVien = "
        SELECT
            MASV,
            HODEM,
            TEN
        FROM sinhvien
        WHERE MASV = ?
    ";

    $stmtSinhVien = mysqli_prepare(
        $conn,
        $sqlSinhVien
    );

    mysqli_stmt_bind_param(
        $stmtSinhVien,
        "s",
        $user["MASV"]
    );

    mysqli_stmt_execute($stmtSinhVien);

    $resultSinhVien =
        mysqli_stmt_get_result($stmtSinhVien);

    $sinhvien =
        mysqli_fetch_assoc($resultSinhVien);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sqlDelete = "
        DELETE FROM users
        WHERE ID = ?
    ";

    $stmtDelete = mysqli_prepare(
        $conn,
        $sqlDelete
    );

    mysqli_stmt_bind_param(
        $stmtDelete,
        "i",
        $id
    );

    if (mysqli_stmt_execute($stmtDelete)) {

        header("Location: index.php?deleted=1");
        exit;

    } else {

        $error =
            "Không thể xóa tài khoản: "
            . mysqli_error($conn);
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

    <title>Xóa tài khoản</title>

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
            Xóa tài khoản
        </h2>

        <div class="text-secondary mt-1">
            Xác nhận trước khi xóa tài khoản
        </div>

    </div>

    <div class="form-card">

        <?php if ($error !== "") { ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>

        <div class="mb-4">

            <div class="mb-3">

                <strong>ID:</strong>

                <?php
                echo htmlspecialchars($user["ID"]);
                ?>

            </div>

            <div class="mb-3">

                <strong>Tên đăng nhập:</strong>

                <?php
                echo htmlspecialchars($user["USERNAME"]);
                ?>

            </div>

            <div class="mb-3">

                <strong>Vai trò:</strong>

                <?php
                if ($user["ROLE"] === "admin") {
                    echo "Admin";
                } else {
                    echo "Sinh viên";
                }
                ?>

            </div>

            <div class="mb-3">

                <strong>Sinh viên:</strong>

                <?php
                if ($user["MASV"] === null || $user["MASV"] === "") {

                    echo "Không liên kết";

                } else {

                    echo htmlspecialchars(
                        $user["MASV"]
                        . " - "
                        . ($sinhvien["HODEM"] ?? "")
                        . " "
                        . ($sinhvien["TEN"] ?? "")
                    );
                }
                ?>

            </div>

            <div class="mb-3">

                <strong>Trạng thái:</strong>

                <?php
                if ((int)$user["TRANGTHAI"] === 1) {
                    echo "Hoạt động";
                } else {
                    echo "Đã khóa";
                }
                ?>

            </div>

        </div>

        <div class="alert alert-danger">

            Bạn có chắc chắn muốn xóa tài khoản này không?

        </div>

        <form method="POST">

            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-danger px-4"
                >
                    Xác nhận xóa
                </button>

                <a
                    href="index.php"
                    class="btn btn-outline-secondary px-4"
                >
                    Hủy
                </a>

            </div>

        </form>

    </div>

</div>

</body>

</html>