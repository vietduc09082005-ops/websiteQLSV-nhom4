<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$error = "";

$makhoa = "";
$tenkhoa = "";
$dienthoai = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $makhoa = trim($_POST["makhoa"] ?? "");
    $tenkhoa = trim($_POST["tenkhoa"] ?? "");
    $dienthoai = trim($_POST["dienthoai"] ?? "");

    if (
        $makhoa === "" ||
        $tenkhoa === "" ||
        $dienthoai === ""
    ) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    }

    elseif (!preg_match('/^[A-Za-z0-9]+$/', $makhoa)) {
        $error = "Mã khoa chỉ được chứa chữ và số.";
    }

    elseif (!preg_match('/^[\p{L}\p{N}\s\-\&().]+$/u', $tenkhoa)) {
        $error = "Tên khoa chứa ký tự không hợp lệ.";
    }

    elseif (!preg_match('/^[0-9]{9,15}$/', $dienthoai)) {
        $error = "Số điện thoại phải gồm từ 9 đến 15 chữ số.";
    }

    else {

        $sqlCheck = "SELECT MAKHOA FROM khoa WHERE MAKHOA = ? LIMIT 1";

        $stmtCheck = mysqli_prepare($conn, $sqlCheck);

        mysqli_stmt_bind_param(
            $stmtCheck,
            "s",
            $makhoa
        );

        mysqli_stmt_execute($stmtCheck);

        $resultCheck = mysqli_stmt_get_result($stmtCheck);

        if (mysqli_num_rows($resultCheck) > 0) {

            $error = "Mã khoa đã tồn tại.";

        } else {

            $sql = "
                INSERT INTO khoa
                (
                    MAKHOA,
                    TENKHOA,
                    DIENTHOAI
                )
                VALUES (?, ?, ?)
            ";

            $stmt = mysqli_prepare($conn, $sql);

            mysqli_stmt_bind_param(
                $stmt,
                "sss",
                $makhoa,
                $tenkhoa,
                $dienthoai
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: index.php?success=1");
                exit;

            } else {

                $error = "Không thể thêm khoa: " . mysqli_error($conn);

            }
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

    <title>Thêm khoa</title>

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
            Thêm khoa
        </h2>

        <div class="text-secondary mt-1">
            Nhập thông tin khoa mới
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
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="makhoa"
                        maxlength="10"
                        pattern="[A-Za-z0-9]+"
                        value="<?php echo htmlspecialchars($makhoa); ?>"
                        class="form-control"
                        required
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
                        placeholder="Ví dụ: 0241234567"
                        required
                    >

                </div>

            </div>

            <div class="d-flex gap-2 mt-3">

                <button
                    type="submit"
                    class="btn btn-primary px-4"
                >
                    Thêm khoa
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