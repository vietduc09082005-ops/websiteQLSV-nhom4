<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$error = "";

$mamh = trim($_GET["mamh"] ?? "");

if ($mamh === "") {
    header("Location: index.php");
    exit;
}

$sql = "
    SELECT *
    FROM monhoc
    WHERE MAMH = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $mamh
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$monhoc = mysqli_fetch_assoc($result);

if (!$monhoc) {
    header("Location: index.php");
    exit;
}

$sqlDiem = "
    SELECT COUNT(*) AS total
    FROM diem
    WHERE MAMH = ?
";

$stmtDiem = mysqli_prepare(
    $conn,
    $sqlDiem
);

mysqli_stmt_bind_param(
    $stmtDiem,
    "s",
    $mamh
);

mysqli_stmt_execute($stmtDiem);

$resultDiem =
    mysqli_stmt_get_result($stmtDiem);

$rowDiem =
    mysqli_fetch_assoc($resultDiem);

$soBangDiem =
    (int)$rowDiem["total"];

$sqlChuyenCan = "
    SELECT COUNT(*) AS total
    FROM chuyencan
    WHERE MAMH = ?
";

$stmtChuyenCan = mysqli_prepare(
    $conn,
    $sqlChuyenCan
);

mysqli_stmt_bind_param(
    $stmtChuyenCan,
    "s",
    $mamh
);

mysqli_stmt_execute($stmtChuyenCan);

$resultChuyenCan =
    mysqli_stmt_get_result($stmtChuyenCan);

$rowChuyenCan =
    mysqli_fetch_assoc($resultChuyenCan);

$soChuyenCan =
    (int)$rowChuyenCan["total"];

$coDuLieuLienQuan =
    $soBangDiem > 0 ||
    $soChuyenCan > 0;

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    !$coDuLieuLienQuan
) {

    $sqlDelete = "
        DELETE FROM monhoc
        WHERE MAMH = ?
    ";

    $stmtDelete = mysqli_prepare(
        $conn,
        $sqlDelete
    );

    mysqli_stmt_bind_param(
        $stmtDelete,
        "s",
        $mamh
    );

    if (mysqli_stmt_execute($stmtDelete)) {

        header("Location: index.php?deleted=1");
        exit;

    } else {

        $error =
            "Không thể xóa môn học: "
            . mysqli_error($conn);
    }
}

$activePage = "monhoc";
?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Xóa môn học</title>

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
            Xóa môn học
        </h2>

        <div class="text-secondary mt-1">
            Xác nhận trước khi xóa môn học
        </div>

    </div>

    <div class="form-card">

        <?php if ($error !== "") { ?>

            <div class="alert alert-danger">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php } ?>

        <div class="mb-4">

            <div class="mb-3">

                <strong>Mã môn học:</strong>

                <?php
                echo htmlspecialchars(
                    $monhoc["MAMH"]
                );
                ?>

            </div>

            <div class="mb-3">

                <strong>Tên môn học:</strong>

                <?php
                echo htmlspecialchars(
                    $monhoc["TENMH"]
                );
                ?>

            </div>

            <div class="mb-3">

                <strong>Số tín chỉ:</strong>

                <?php
                echo htmlspecialchars(
                    $monhoc["SOTINCHI"]
                );
                ?>

            </div>

        </div>

        <?php if ($coDuLieuLienQuan) { ?>

            <div class="alert alert-warning">

                Không thể xóa môn học này vì đang có dữ liệu liên quan.

                <hr>

                <div>
                    Bảng điểm:
                    <strong>
                        <?php echo $soBangDiem; ?>
                    </strong>
                    bản ghi
                </div>

                <div>
                    Chuyên cần:
                    <strong>
                        <?php echo $soChuyenCan; ?>
                    </strong>
                    bản ghi
                </div>

            </div>

            <a
                href="index.php"
                class="btn btn-outline-secondary px-4"
            >
                Quay lại
            </a>

        <?php } else { ?>

            <div class="alert alert-danger">

                Bạn có chắc chắn muốn xóa môn học này không?

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