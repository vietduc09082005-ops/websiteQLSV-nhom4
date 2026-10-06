<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$error = "";

$makhoa = trim($_GET["makhoa"] ?? "");

if ($makhoa === "") {
    header("Location: index.php");
    exit;
}

$sql = "SELECT * FROM khoa WHERE MAKHOA = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $makhoa
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$khoa = mysqli_fetch_assoc($result);

if (!$khoa) {
    header("Location: index.php");
    exit;
}

$sqlCount = "SELECT COUNT(*) AS total FROM lop WHERE MAKHOA = ?";

$stmtCount = mysqli_prepare($conn, $sqlCount);

mysqli_stmt_bind_param(
    $stmtCount,
    "s",
    $makhoa
);

mysqli_stmt_execute($stmtCount);

$resultCount = mysqli_stmt_get_result($stmtCount);

$totalLop = mysqli_fetch_assoc($resultCount)["total"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($totalLop > 0) {

        $error = "Không thể xóa khoa vì khoa này vẫn còn lớp.";

    } else {

        $sqlDelete = "DELETE FROM khoa WHERE MAKHOA = ?";

        $stmtDelete = mysqli_prepare(
            $conn,
            $sqlDelete
        );

        mysqli_stmt_bind_param(
            $stmtDelete,
            "s",
            $makhoa
        );

        if (mysqli_stmt_execute($stmtDelete)) {

            header("Location: index.php?deleted=1");
            exit;

        } else {

            $error = "Không thể xóa khoa: " . mysqli_error($conn);
        }
    }
}

$activePage = "khoa";
?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Xóa khoa</title>

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
            Xóa khoa
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

                <strong>Mã khoa:</strong>

                <?php
                echo htmlspecialchars($khoa["MAKHOA"]);
                ?>

            </div>

            <div class="mb-3">

                <strong>Tên khoa:</strong>

                <?php
                echo htmlspecialchars($khoa["TENKHOA"]);
                ?>

            </div>

            <div class="mb-3">

                <strong>Điện thoại:</strong>

                <?php
                echo htmlspecialchars($khoa["DIENTHOAI"]);
                ?>

            </div>

            <div class="mb-3">

                <strong>Số lớp thuộc khoa:</strong>

                <?php
                echo $totalLop;
                ?>

            </div>

        </div>

        <?php if ($totalLop > 0) { ?>

            <div class="alert alert-warning">

                Khoa này hiện có
                <strong><?php echo $totalLop; ?></strong>
                lớp.

                Bạn phải chuyển hoặc xóa các lớp thuộc khoa trước khi xóa khoa.

            </div>

            <a
                href="index.php"
                class="btn btn-outline-secondary"
            >
                Quay lại
            </a>

        <?php } else { ?>

            <div class="alert alert-danger">

                Bạn có chắc chắn muốn xóa khoa này không?

                Sau khi xóa sẽ không thể khôi phục từ giao diện.

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

        <?php } ?>

    </div>

</div>

</body>

</html>