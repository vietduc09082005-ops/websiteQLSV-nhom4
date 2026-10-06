<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$error = "";

$masv = "";
$mamh = "";
$ngayhoc = date("Y-m-d");
$trangthai = "";
$ghichu = "";

$sqlSinhVien = "
    SELECT MASV, HODEM, TEN
    FROM sinhvien
    ORDER BY MASV ASC
";

$resultSinhVien = mysqli_query($conn, $sqlSinhVien);

if (!$resultSinhVien) {
    die("Lỗi lấy danh sách sinh viên: " . mysqli_error($conn));
}

$sqlMonHoc = "
    SELECT MAMH, TENMH
    FROM monhoc
    ORDER BY MAMH ASC
";

$resultMonHoc = mysqli_query($conn, $sqlMonHoc);

if (!$resultMonHoc) {
    die("Lỗi lấy danh sách môn học: " . mysqli_error($conn));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $masv = trim($_POST["masv"] ?? "");
    $mamh = trim($_POST["mamh"] ?? "");
    $ngayhoc = trim($_POST["ngayhoc"] ?? "");
    $trangthai = trim($_POST["trangthai"] ?? "");
    $ghichu = trim($_POST["ghichu"] ?? "");

    if (
        $masv === "" ||
        $mamh === "" ||
        $ngayhoc === "" ||
        $trangthai === ""
    ) {
        $error = "Vui lòng nhập đầy đủ thông tin bắt buộc.";
    }

    elseif (
        !in_array(
            $trangthai,
            ["Có mặt", "Vắng", "Muộn"],
            true
        )
    ) {
        $error = "Trạng thái chuyên cần không hợp lệ.";
    }

    else {

        $date = DateTime::createFromFormat("Y-m-d", $ngayhoc);

        $ngayHopLe =
            $date &&
            $date->format("Y-m-d") === $ngayhoc &&
            $ngayhoc <= date("Y-m-d");

        if (!$ngayHopLe) {

            $error = "Ngày học không hợp lệ.";

        } else {

            $sqlCheckSinhVien = "
                SELECT MASV
                FROM sinhvien
                WHERE MASV = ?
            ";

            $stmtSinhVien = mysqli_prepare(
                $conn,
                $sqlCheckSinhVien
            );

            mysqli_stmt_bind_param(
                $stmtSinhVien,
                "s",
                $masv
            );

            mysqli_stmt_execute($stmtSinhVien);

            $resultCheckSinhVien =
                mysqli_stmt_get_result($stmtSinhVien);

            if (mysqli_num_rows($resultCheckSinhVien) == 0) {

                $error = "Sinh viên không tồn tại.";

            } else {

                $sqlCheckMonHoc = "
                    SELECT MAMH
                    FROM monhoc
                    WHERE MAMH = ?
                ";

                $stmtMonHoc = mysqli_prepare(
                    $conn,
                    $sqlCheckMonHoc
                );

                mysqli_stmt_bind_param(
                    $stmtMonHoc,
                    "s",
                    $mamh
                );

                mysqli_stmt_execute($stmtMonHoc);

                $resultCheckMonHoc =
                    mysqli_stmt_get_result($stmtMonHoc);

                if (mysqli_num_rows($resultCheckMonHoc) == 0) {

                    $error = "Môn học không tồn tại.";

                } else {

                    $sqlDuplicate = "
                        SELECT MACC
                        FROM chuyencan
                        WHERE MASV = ?
                          AND MAMH = ?
                          AND NGAYHOC = ?
                        LIMIT 1
                    ";

                    $stmtDuplicate = mysqli_prepare(
                        $conn,
                        $sqlDuplicate
                    );

                    mysqli_stmt_bind_param(
                        $stmtDuplicate,
                        "sss",
                        $masv,
                        $mamh,
                        $ngayhoc
                    );

                    mysqli_stmt_execute($stmtDuplicate);

                    $resultDuplicate =
                        mysqli_stmt_get_result($stmtDuplicate);

                    if (mysqli_num_rows($resultDuplicate) > 0) {

                        $error =
                            "Sinh viên này đã được điểm danh môn học này trong ngày đã chọn.";

                    } else {

                        $sqlInsert = "
                            INSERT INTO chuyencan
                            (
                                MASV,
                                MAMH,
                                NGAYHOC,
                                TRANGTHAI,
                                GHICHU
                            )
                            VALUES (?, ?, ?, ?, ?)
                        ";

                        $stmtInsert = mysqli_prepare(
                            $conn,
                            $sqlInsert
                        );

                        mysqli_stmt_bind_param(
                            $stmtInsert,
                            "sssss",
                            $masv,
                            $mamh,
                            $ngayhoc,
                            $trangthai,
                            $ghichu
                        );

                        if (mysqli_stmt_execute($stmtInsert)) {

                            header("Location: index.php?success=1");
                            exit;

                        } else {

                            $error =
                                "Không thể thêm chuyên cần: "
                                . mysqli_error($conn);
                        }
                    }
                }
            }
        }
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

    <title>Điểm danh sinh viên</title>

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
            Điểm danh sinh viên
        </h2>

        <div class="text-secondary mt-1">
            Thêm thông tin chuyên cần
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
                        Sinh viên
                        <span class="required">*</span>
                    </label>

                    <select
                        name="masv"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Chọn sinh viên --
                        </option>

                        <?php
                        mysqli_data_seek($resultSinhVien, 0);

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

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Môn học
                        <span class="required">*</span>
                    </label>

                    <select
                        name="mamh"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Chọn môn học --
                        </option>

                        <?php
                        mysqli_data_seek($resultMonHoc, 0);

                        while (
                            $mh = mysqli_fetch_assoc($resultMonHoc)
                        ) {
                        ?>

                            <option
                                value="<?php echo htmlspecialchars($mh["MAMH"]); ?>"
                                <?php echo $mamh === $mh["MAMH"] ? "selected" : ""; ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $mh["MAMH"]
                                    . " - "
                                    . $mh["TENMH"]
                                );
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Ngày học
                        <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        name="ngayhoc"
                        max="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo htmlspecialchars($ngayhoc); ?>"
                        class="form-control"
                        required
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Trạng thái
                        <span class="required">*</span>
                    </label>

                    <select
                        name="trangthai"
                        class="form-select"
                        required
                    >

                        <option value="">
                            -- Chọn trạng thái --
                        </option>

                        <option
                            value="Có mặt"
                            <?php echo $trangthai === "Có mặt" ? "selected" : ""; ?>
                        >
                            Có mặt
                        </option>

                        <option
                            value="Vắng"
                            <?php echo $trangthai === "Vắng" ? "selected" : ""; ?>
                        >
                            Vắng
                        </option>

                        <option
                            value="Muộn"
                            <?php echo $trangthai === "Muộn" ? "selected" : ""; ?>
                        >
                            Muộn
                        </option>

                    </select>

                </div>

                <div class="col-12 mb-3">

                    <label class="form-label">
                        Ghi chú
                    </label>

                    <textarea
                        name="ghichu"
                        maxlength="255"
                        rows="3"
                        class="form-control"
                        placeholder="Ví dụ: Có phép, muộn 15 phút..."
                    ><?php echo htmlspecialchars($ghichu); ?></textarea>

                </div>

            </div>

            <div class="d-flex gap-2 mt-3">

                <button
                    type="submit"
                    class="btn btn-primary px-4"
                >
                    Lưu chuyên cần
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