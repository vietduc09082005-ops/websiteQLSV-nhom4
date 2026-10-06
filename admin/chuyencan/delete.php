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
    FROM chuyencan
    WHERE MACC = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$chuyencan = mysqli_fetch_assoc($result);

if (!$chuyencan) {
    header("Location: index.php");
    exit;
}

$sqlSinhVien = "
    SELECT HODEM, TEN
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
    $chuyencan["MASV"]
);

mysqli_stmt_execute($stmtSinhVien);

$resultSinhVien = mysqli_stmt_get_result($stmtSinhVien);

$sinhvien = mysqli_fetch_assoc($resultSinhVien);

$sqlMonHoc = "
    SELECT TENMH
    FROM monhoc
    WHERE MAMH = ?
";

$stmtMonHoc = mysqli_prepare(
    $conn,
    $sqlMonHoc
);

mysqli_stmt_bind_param(
    $stmtMonHoc,
    "s",
    $chuyencan["MAMH"]
);

mysqli_stmt_execute($stmtMonHoc);

$resultMonHoc = mysqli_stmt_get_result($stmtMonHoc);

$monhoc = mysqli_fetch_assoc($resultMonHoc);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sqlDelete = "
        DELETE FROM chuyencan
        WHERE MACC = ?
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

        $error = "Không thể xóa chuyên cần: " . mysqli_error($conn);
    }
}

$activePage = "chuyencan";
?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Xóa chuyên cần</title>

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
            Xóa chuyên cần
        </h2>

        <div class="text-secondary mt-1">
            Xác nhận trước khi xóa dữ liệu
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

                <strong>Mã chuyên cần:</strong>

                <?php
                echo htmlspecialchars($chuyencan["MACC"]);
                ?>

            </div>

            <div class="mb-3">

                <strong>Sinh viên:</strong>

                <?php
                echo htmlspecialchars(
                    $chuyencan["MASV"]
                    . " - "
                    . ($sinhvien["HODEM"] ?? "")
                    . " "
                    . ($sinhvien["TEN"] ?? "")
                );
                ?>

            </div>

            <div class="mb-3">

                <strong>Môn học:</strong>

                <?php
                echo htmlspecialchars(
                    $chuyencan["MAMH"]
                    . " - "
                    . ($monhoc["TENMH"] ?? "")
                );
                ?>

            </div>

            <div class="mb-3">

                <strong>Ngày học:</strong>

                <?php
                echo htmlspecialchars($chuyencan["NGAYHOC"]);
                ?>

            </div>

            <div class="mb-3">

                <strong>Trạng thái:</strong>

                <?php
                echo htmlspecialchars($chuyencan["TRANGTHAI"]);
                ?>

            </div>

            <div class="mb-3">

                <strong>Ghi chú:</strong>

                <?php
                echo htmlspecialchars(
                    $chuyencan["GHICHU"] ?? ""
                );
                ?>

            </div>

        </div>

        <div class="alert alert-danger">

            Bạn có chắc chắn muốn xóa bản ghi chuyên cần này không?

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