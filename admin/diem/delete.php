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
    SELECT
        diem.*,
        sinhvien.HODEM,
        sinhvien.TEN,
        monhoc.TENMH
    FROM diem
    INNER JOIN sinhvien
        ON diem.MASV = sinhvien.MASV
    INNER JOIN monhoc
        ON diem.MAMH = monhoc.MAMH
    WHERE diem.MADIEM = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$diem = mysqli_fetch_assoc($result);

if (!$diem) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $sqlDelete = "
        DELETE FROM diem
        WHERE MADIEM = ?
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
            "Không thể xóa điểm: "
            . mysqli_error($conn);
    }
}

$activePage = "diem";
?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Xóa điểm</title>

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
            Xóa điểm
        </h2>

        <div class="text-secondary mt-1">
            Xác nhận trước khi xóa bảng điểm
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

                <strong>Mã điểm:</strong>

                <?php
                echo htmlspecialchars($diem["MADIEM"]);
                ?>

            </div>

            <div class="mb-3">

                <strong>Sinh viên:</strong>

                <?php
                echo htmlspecialchars(
                    $diem["MASV"]
                    . " - "
                    . $diem["HODEM"]
                    . " "
                    . $diem["TEN"]
                );
                ?>

            </div>

            <div class="mb-3">

                <strong>Môn học:</strong>

                <?php
                echo htmlspecialchars(
                    $diem["MAMH"]
                    . " - "
                    . $diem["TENMH"]
                );
                ?>

            </div>

            <hr>

            <div class="row">

                <div class="col-md-4 mb-3">
                    <strong>1 tiết 1:</strong>
                    <?php echo $diem["DIEM1TIET1"] ?? "-"; ?>
                </div>

                <div class="col-md-4 mb-3">
                    <strong>1 tiết 2:</strong>
                    <?php echo $diem["DIEM1TIET2"] ?? "-"; ?>
                </div>

                <div class="col-md-4 mb-3">
                    <strong>1 tiết 3:</strong>
                    <?php echo $diem["DIEM1TIET3"] ?? "-"; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Giữa kỳ 1:</strong>
                    <?php echo $diem["DIEMGIUAKY1"] ?? "-"; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Giữa kỳ 2:</strong>
                    <?php echo $diem["DIEMGIUAKY2"] ?? "-"; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Cuối kỳ 1:</strong>
                    <?php echo $diem["DIEMCUOIKY1"] ?? "-"; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <strong>Cuối kỳ 2:</strong>
                    <?php echo $diem["DIEMCUOIKY2"] ?? "-"; ?>
                </div>

            </div>

        </div>

        <div class="alert alert-danger">

            Bạn có chắc chắn muốn xóa toàn bộ bảng điểm này không?

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