<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$error = "";

$masv = "";
$hodem = "";
$ten = "";
$ngaysinh = "";
$gioitinh = "";
$diachi = "";
$email = "";
$malop = "";
$sdt = "";

$sqlLop = "SELECT MALOP, TENLOP FROM lop ORDER BY MALOP ASC";
$resultLop = mysqli_query($conn, $sqlLop);

if (!$resultLop) {
    die("Lỗi lấy danh sách lớp: " . mysqli_error($conn));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $masv = trim($_POST["masv"] ?? "");
    $hodem = trim($_POST["hodem"] ?? "");
    $ten = trim($_POST["ten"] ?? "");
    $ngaysinh = trim($_POST["ngaysinh"] ?? "");
    $gioitinh = trim($_POST["gioitinh"] ?? "");
    $diachi = trim($_POST["diachi"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $malop = trim($_POST["malop"] ?? "");
    $sdt = trim($_POST["sdt"] ?? "");

    if (
        $masv === "" ||
        $hodem === "" ||
        $ten === "" ||
        $ngaysinh === "" ||
        $gioitinh === "" ||
        $diachi === "" ||
        $email === "" ||
        $malop === "" ||
        $sdt === ""
    ) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    }

    elseif (!preg_match('/^[A-Za-z0-9]+$/', $masv)) {
        $error = "Mã sinh viên chỉ được chứa chữ và số.";
    }

    elseif (!preg_match('/^[\p{L}\s]+$/u', $hodem)) {
        $error = "Họ đệm chỉ được chứa chữ.";
    }

    elseif (!preg_match('/^[\p{L}\s]+$/u', $ten)) {
        $error = "Tên chỉ được chứa chữ.";
    }

    else {

        $date = DateTime::createFromFormat("Y-m-d", $ngaysinh);

        $ngaySinhHopLe =
            $date &&
            $date->format("Y-m-d") === $ngaysinh &&
            $ngaysinh >= "1900-01-01" &&
            $ngaysinh <= date("Y-m-d");

        if (!$ngaySinhHopLe) {
            $error = "Ngày sinh không hợp lệ.";
        }

        elseif (!in_array($gioitinh, ["Nam", "Nữ", "Khác"], true)) {
            $error = "Giới tính không hợp lệ.";
        }

        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email không đúng định dạng.";
        }

        elseif (!preg_match('/^[0-9]{9,15}$/', $sdt)) {
            $error = "Số điện thoại phải gồm từ 9 đến 15 chữ số.";
        }

        else {

            $sqlCheckLop = "SELECT MALOP FROM lop WHERE MALOP = ?";

            $stmtLop = mysqli_prepare($conn, $sqlCheckLop);

            mysqli_stmt_bind_param(
                $stmtLop,
                "s",
                $malop
            );

            mysqli_stmt_execute($stmtLop);

            $resultCheckLop = mysqli_stmt_get_result($stmtLop);

            if (mysqli_num_rows($resultCheckLop) == 0) {
                $error = "Lớp không tồn tại.";
            }

            else {

                $sqlCheck = "
                    SELECT MASV
                    FROM sinhvien
                    WHERE MASV = ? OR EMAIL = ?
                    LIMIT 1
                ";

                $stmtCheck = mysqli_prepare($conn, $sqlCheck);

                mysqli_stmt_bind_param(
                    $stmtCheck,
                    "ss",
                    $masv,
                    $email
                );

                mysqli_stmt_execute($stmtCheck);

                $resultCheck = mysqli_stmt_get_result($stmtCheck);

                if (mysqli_num_rows($resultCheck) > 0) {
                    $error = "Mã sinh viên hoặc Email đã tồn tại.";
                }

                else {

                    $sql = "
                        INSERT INTO sinhvien
                        (
                            MASV,
                            HODEM,
                            TEN,
                            NGAYSINH,
                            GIOITINH,
                            DIACHI,
                            EMAIL,
                            MALOP,
                            SDT
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ";

                    $stmt = mysqli_prepare($conn, $sql);

                    mysqli_stmt_bind_param(
                        $stmt,
                        "sssssssss",
                        $masv,
                        $hodem,
                        $ten,
                        $ngaysinh,
                        $gioitinh,
                        $diachi,
                        $email,
                        $malop,
                        $sdt
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        $sqlUpdateSiso = "UPDATE lop SET SOSI = SOSI + 1 WHERE MALOP = ?";
                        $stmtSiso = mysqli_prepare($conn, $sqlUpdateSiso);
                        mysqli_stmt_bind_param($stmtSiso, "s", $malop);
                        mysqli_stmt_execute($stmtSiso);
                        mysqli_stmt_close($stmtSiso);

                        mysqli_stmt_close($stmt);
                        if (isset($stmtLop)) mysqli_stmt_close($stmtLop);
                        if (isset($stmtCheck)) mysqli_stmt_close($stmtCheck);

                        header("Location: index.php?success=1");
                        exit;

                    } else {
                        $error = "Không thể thêm sinh viên: " . mysqli_error($conn);
                    }
                }
            }
        }
    }
}

$activePage = "sinhvien";
?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Thêm sinh viên</title>

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
            Thêm sinh viên
        </h2>

        <div class="text-secondary mt-1">
            Nhập thông tin sinh viên mới
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

        <form method="POST">

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Mã sinh viên
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="masv"
                        maxlength="10"
                        pattern="[A-Za-z0-9]+"
                        title="Mã sinh viên chỉ được chứa chữ và số"
                        value="<?php echo htmlspecialchars($masv); ?>"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Họ đệm
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="hodem"
                        maxlength="50"
                        pattern="[A-Za-zÀ-ỹ\s]+"
                        title="Họ đệm chỉ được chứa chữ"
                        value="<?php echo htmlspecialchars($hodem); ?>"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Tên
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="ten"
                        maxlength="30"
                        pattern="[A-Za-zÀ-ỹ\s]+"
                        title="Tên chỉ được chứa chữ"
                        value="<?php echo htmlspecialchars($ten); ?>"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Ngày sinh
                        <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        name="ngaysinh"
                        min="1900-01-01"
                        max="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo htmlspecialchars($ngaysinh); ?>"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Giới tính
                        <span class="required">*</span>
                    </label>

                    <select
                        name="gioitinh"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Chọn giới tính --
                        </option>

                        <option
                            value="Nam"
                            <?php echo $gioitinh === "Nam" ? "selected" : ""; ?>
                        >
                            Nam
                        </option>

                        <option
                            value="Nữ"
                            <?php echo $gioitinh === "Nữ" ? "selected" : ""; ?>
                        >
                            Nữ
                        </option>

                        <option
                            value="Khác"
                            <?php echo $gioitinh === "Khác" ? "selected" : ""; ?>
                        >
                            Khác
                        </option>

                    </select>

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Lớp
                        <span class="required">*</span>
                    </label>

                    <select
                        name="malop"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Chọn lớp --
                        </option>

                        <?php
                        mysqli_data_seek($resultLop, 0);

                        while ($lop = mysqli_fetch_assoc($resultLop)) {
                        ?>

                            <option
                                value="<?php echo htmlspecialchars($lop["MALOP"]); ?>"
                                <?php echo $malop === $lop["MALOP"] ? "selected" : ""; ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $lop["MALOP"] . " - " . $lop["TENLOP"]
                                );
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Email
                        <span class="required">*</span>
                    </label>

                    <input
                        type="email"
                        name="email"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($email); ?>"
                        class="form-control"
                        placeholder="vidu@gmail.com"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Số điện thoại
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="sdt"
                        maxlength="15"
                        pattern="[0-9]{9,15}"
                        inputmode="numeric"
                        title="Số điện thoại phải gồm từ 9 đến 15 chữ số"
                        value="<?php echo htmlspecialchars($sdt); ?>"
                        class="form-control"
                        placeholder="Ví dụ: 0987654321"
                        required
                    >

                </div>

                <div class="col-12 mb-3">

                    <label class="form-label">
                        Địa chỉ
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="diachi"
                        maxlength="255"
                        value="<?php echo htmlspecialchars($diachi); ?>"
                        class="form-control"
                        required
                    >

                </div>

            </div>

            <div class="d-flex gap-2 mt-3">

                <button
                    type="submit"
                    class="btn btn-primary px-4"
                >
                    Thêm sinh viên
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