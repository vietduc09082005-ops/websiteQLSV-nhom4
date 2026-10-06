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

$tenmh = $monhoc["TENMH"];
$sotinchi = $monhoc["SOTINCHI"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $tenmh = trim($_POST["tenmh"] ?? "");
    $sotinchi = trim($_POST["sotinchi"] ?? "");

    if ($tenmh === "" || $sotinchi === "") {

        $error = "Vui lòng nhập đầy đủ thông tin.";

    } elseif (
        !preg_match(
            '/^[\p{L}\p{N}\s\-\(\)]+$/u',
            $tenmh
        )
    ) {

        $error = "Tên môn học chứa ký tự không hợp lệ.";

    } elseif (!ctype_digit($sotinchi)) {

        $error = "Số tín chỉ phải là số nguyên.";

    } elseif (
        (int)$sotinchi < 1 ||
        (int)$sotinchi > 10
    ) {

        $error = "Số tín chỉ phải từ 1 đến 10.";

    } else {

        $sotinchiInt = (int)$sotinchi;

        $sqlUpdate = "
            UPDATE monhoc
            SET
                TENMH = ?,
                SOTINCHI = ?
            WHERE MAMH = ?
        ";

        $stmtUpdate = mysqli_prepare(
            $conn,
            $sqlUpdate
        );

        mysqli_stmt_bind_param(
            $stmtUpdate,
            "sis",
            $tenmh,
            $sotinchiInt,
            $mamh
        );

        if (mysqli_stmt_execute($stmtUpdate)) {

            header("Location: index.php?updated=1");
            exit;

        } else {

            $error =
                "Không thể cập nhật môn học: "
                . mysqli_error($conn);
        }
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

    <title>Sửa môn học</title>

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
            Sửa môn học
        </h2>

        <div class="text-secondary mt-1">
            Cập nhật thông tin môn học
        </div>

    </div>

    <div class="form-card">

        <?php if ($error !== "") { ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php } ?>

        <form method="POST">

            <div class="mb-3">

                <label class="form-label">
                    Mã môn học
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="<?php echo htmlspecialchars($mamh); ?>"
                    readonly
                >

                <div class="form-text">
                    Mã môn học không được thay đổi.
                </div>

            </div>

            <div class="mb-3">

                <label class="form-label">
                    Tên môn học
                    <span class="required">*</span>
                </label>

                <input
                    type="text"
                    name="tenmh"
                    maxlength="100"
                    value="<?php echo htmlspecialchars($tenmh); ?>"
                    class="form-control"
                    required
                >

            </div>

            <div class="mb-4">

                <label class="form-label">
                    Số tín chỉ
                    <span class="required">*</span>
                </label>

                <input
                    type="number"
                    name="sotinchi"
                    min="1"
                    max="10"
                    step="1"
                    value="<?php echo htmlspecialchars($sotinchi); ?>"
                    class="form-control"
                    required
                >

            </div>

            <div class="d-flex gap-2">

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

</body>

</html>