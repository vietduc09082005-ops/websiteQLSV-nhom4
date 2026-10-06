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

$tenkhoa = $khoa["TENKHOA"];
$dienthoai = $khoa["DIENTHOAI"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tenkhoa = trim($_POST["tenkhoa"] ?? "");
    $dienthoai = trim($_POST["dienthoai"] ?? "");

    if (
        $tenkhoa === "" ||
        $dienthoai === ""
    ) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    }

    elseif (!preg_match('/^[\p{L}\p{N}\s\-\&().]+$/u', $tenkhoa)) {
        $error = "Tên khoa chứa ký tự không hợp lệ.";
    }

    elseif (!preg_match('/^[0-9]{9,15}$/', $dienthoai)) {
        $error = "Số điện thoại phải gồm từ 9 đến 15 chữ số.";
    }

    else {

        $sqlUpdate = "
            UPDATE khoa
            SET
                TENKHOA = ?,
                DIENTHOAI = ?
            WHERE MAKHOA = ?
        ";

        $stmtUpdate = mysqli_prepare(
            $conn,
            $sqlUpdate
        );

        mysqli_stmt_bind_param(
            $stmtUpdate,
            "sss",
            $tenkhoa,
            $dienthoai,
            $makhoa
        );

        if (mysqli_stmt_execute($stmtUpdate)) {

            header("Location: index.php?updated=1");
            exit;

        } else {

            $error = "Không thể cập nhật khoa: " . mysqli_error($conn);
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

    <title>Sửa khoa</title>

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
            Sửa khoa
        </h2>

        <div class="text-secondary mt-1">
            Cập nhật thông tin khoa
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

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Mã khoa
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?php echo htmlspecialchars($makhoa); ?>"
                        readonly
                    >

                </div>

                <div class="col-md-8 mb-3">

                    <label class="form-label">
                        Tên khoa
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="tenkhoa"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($tenkhoa); ?>"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Điện thoại
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="dienthoai"
                        maxlength="15"
                        pattern="[0-9]{9,15}"
                        inputmode="numeric"
                        value="<?php echo htmlspecialchars($dienthoai); ?>"
                        class="form-control"
                        required
                    >

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

</body>

</html>