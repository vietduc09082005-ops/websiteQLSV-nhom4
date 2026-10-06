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

$diem1tiet1 = "";
$diem1tiet2 = "";
$diem1tiet3 = "";

$diemgiuaky1 = "";
$diemgiuaky2 = "";

$diemcuoiky1 = "";
$diemcuoiky2 = "";

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

function diemHopLe($diem)
{
    if ($diem === "") {
        return true;
    }

    if (!is_numeric($diem)) {
        return false;
    }

    $diem = (float)$diem;

    return $diem >= 0 && $diem <= 10;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $masv = trim($_POST["masv"] ?? "");
    $mamh = trim($_POST["mamh"] ?? "");

    $diem1tiet1 = trim($_POST["diem1tiet1"] ?? "");
    $diem1tiet2 = trim($_POST["diem1tiet2"] ?? "");
    $diem1tiet3 = trim($_POST["diem1tiet3"] ?? "");

    $diemgiuaky1 = trim($_POST["diemgiuaky1"] ?? "");
    $diemgiuaky2 = trim($_POST["diemgiuaky2"] ?? "");

    $diemcuoiky1 = trim($_POST["diemcuoiky1"] ?? "");
    $diemcuoiky2 = trim($_POST["diemcuoiky2"] ?? "");

    if ($masv === "" || $mamh === "") {

        $error = "Vui lòng chọn sinh viên và môn học.";

    } elseif (
        $diem1tiet1 === "" &&
        $diem1tiet2 === "" &&
        $diem1tiet3 === "" &&
        $diemgiuaky1 === "" &&
        $diemgiuaky2 === "" &&
        $diemcuoiky1 === "" &&
        $diemcuoiky2 === ""
    ) {

        $error = "Vui lòng nhập ít nhất một đầu điểm.";

    } elseif (
        !diemHopLe($diem1tiet1) ||
        !diemHopLe($diem1tiet2) ||
        !diemHopLe($diem1tiet3) ||
        !diemHopLe($diemgiuaky1) ||
        !diemHopLe($diemgiuaky2) ||
        !diemHopLe($diemcuoiky1) ||
        !diemHopLe($diemcuoiky2)
    ) {

        $error = "Tất cả điểm phải nằm trong khoảng từ 0 đến 10.";

    } else {

        $sqlCheckSinhVien = "
            SELECT MASV
            FROM sinhvien
            WHERE MASV = ?
            LIMIT 1
        ";

        $stmtCheckSinhVien = mysqli_prepare(
            $conn,
            $sqlCheckSinhVien
        );

        mysqli_stmt_bind_param(
            $stmtCheckSinhVien,
            "s",
            $masv
        );

        mysqli_stmt_execute($stmtCheckSinhVien);

        $resultCheckSinhVien =
            mysqli_stmt_get_result($stmtCheckSinhVien);

        if (mysqli_num_rows($resultCheckSinhVien) == 0) {

            $error = "Sinh viên không tồn tại.";

        } else {

            $sqlCheckMonHoc = "
                SELECT MAMH
                FROM monhoc
                WHERE MAMH = ?
                LIMIT 1
            ";

            $stmtCheckMonHoc = mysqli_prepare(
                $conn,
                $sqlCheckMonHoc
            );

            mysqli_stmt_bind_param(
                $stmtCheckMonHoc,
                "s",
                $mamh
            );

            mysqli_stmt_execute($stmtCheckMonHoc);

            $resultCheckMonHoc =
                mysqli_stmt_get_result($stmtCheckMonHoc);

            if (mysqli_num_rows($resultCheckMonHoc) == 0) {

                $error = "Môn học không tồn tại.";

            } else {

                $sqlDuplicate = "
                    SELECT MADIEM
                    FROM diem
                    WHERE MASV = ?
                      AND MAMH = ?
                    LIMIT 1
                ";

                $stmtDuplicate = mysqli_prepare(
                    $conn,
                    $sqlDuplicate
                );

                mysqli_stmt_bind_param(
                    $stmtDuplicate,
                    "ss",
                    $masv,
                    $mamh
                );

                mysqli_stmt_execute($stmtDuplicate);

                $resultDuplicate =
                    mysqli_stmt_get_result($stmtDuplicate);

                if (mysqli_num_rows($resultDuplicate) > 0) {

                    $error =
                        "Sinh viên này đã có bảng điểm của môn học này.";

                } else {

                    $sqlInsert = "
                        INSERT INTO diem
                        (
                            MASV,
                            MAMH,
                            DIEM1TIET1,
                            DIEM1TIET2,
                            DIEM1TIET3,
                            DIEMGIUAKY1,
                            DIEMGIUAKY2,
                            DIEMCUOIKY1,
                            DIEMCUOIKY2,
                            DIEMTONGKET
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            NULLIF(?, ''),
                            NULLIF(?, ''),
                            NULLIF(?, ''),
                            NULLIF(?, ''),
                            NULLIF(?, ''),
                            NULLIF(?, ''),
                            NULLIF(?, ''),
                            NULL
                        )
                    ";

                    $stmtInsert = mysqli_prepare(
                        $conn,
                        $sqlInsert
                    );

                    mysqli_stmt_bind_param(
                        $stmtInsert,
                        "sssssssss",
                        $masv,
                        $mamh,
                        $diem1tiet1,
                        $diem1tiet2,
                        $diem1tiet3,
                        $diemgiuaky1,
                        $diemgiuaky2,
                        $diemcuoiky1,
                        $diemcuoiky2
                    );

                    if (mysqli_stmt_execute($stmtInsert)) {

                        header("Location: index.php?success=1");
                        exit;

                    } else {

                        $error =
                            "Không thể thêm điểm: "
                            . mysqli_error($conn);
                    }
                }
            }
        }
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

    <title>Thêm điểm</title>

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
            Thêm điểm
        </h2>

        <div class="text-secondary mt-1">
            Nhập kết quả học tập của sinh viên
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

                <div class="col-md-6 mb-4">

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

                        while ($sv = mysqli_fetch_assoc($resultSinhVien)) {
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

                <div class="col-md-6 mb-4">

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

                        while ($mh = mysqli_fetch_assoc($resultMonHoc)) {
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

            </div>

            <hr>

            <h5 class="mb-3">
                Kiểm tra 1 tiết
            </h5>

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Điểm 1 tiết 1
                    </label>

                    <input
                        type="number"
                        name="diem1tiet1"
                        min="0"
                        max="10"
                        step="0.1"
                        value="<?php echo htmlspecialchars($diem1tiet1); ?>"
                        class="form-control"
                        placeholder="0 - 10"
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Điểm 1 tiết 2
                    </label>

                    <input
                        type="number"
                        name="diem1tiet2"
                        min="0"
                        max="10"
                        step="0.1"
                        value="<?php echo htmlspecialchars($diem1tiet2); ?>"
                        class="form-control"
                        placeholder="0 - 10"
                    >

                </div>

                <div class="col-md-4 mb-3">

                    <label class="form-label">
                        Điểm 1 tiết 3
                    </label>

                    <input
                        type="number"
                        name="diem1tiet3"
                        min="0"
                        max="10"
                        step="0.1"
                        value="<?php echo htmlspecialchars($diem1tiet3); ?>"
                        class="form-control"
                        placeholder="0 - 10"
                    >

                </div>

            </div>

            <hr>

            <h5 class="mb-3">
                Điểm giữa kỳ
            </h5>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Giữa kỳ 1
                    </label>

                    <input
                        type="number"
                        name="diemgiuaky1"
                        min="0"
                        max="10"
                        step="0.1"
                        value="<?php echo htmlspecialchars($diemgiuaky1); ?>"
                        class="form-control"
                        placeholder="0 - 10"
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Giữa kỳ 2
                    </label>

                    <input
                        type="number"
                        name="diemgiuaky2"
                        min="0"
                        max="10"
                        step="0.1"
                        value="<?php echo htmlspecialchars($diemgiuaky2); ?>"
                        class="form-control"
                        placeholder="0 - 10"
                    >

                </div>

            </div>

            <hr>

            <h5 class="mb-3">
                Điểm cuối kỳ
            </h5>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Cuối kỳ 1
                    </label>

                    <input
                        type="number"
                        name="diemcuoiky1"
                        min="0"
                        max="10"
                        step="0.1"
                        value="<?php echo htmlspecialchars($diemcuoiky1); ?>"
                        class="form-control"
                        placeholder="0 - 10"
                    >

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        Cuối kỳ 2
                    </label>

                    <input
                        type="number"
                        name="diemcuoiky2"
                        min="0"
                        max="10"
                        step="0.1"
                        value="<?php echo htmlspecialchars($diemcuoiky2); ?>"
                        class="form-control"
                        placeholder="0 - 10"
                    >

                </div>

            </div>

            <div class="alert alert-light border mt-3">

                Có thể để trống những đầu điểm chưa có kết quả.
                Hệ thống hiện chưa tính điểm tổng kết cho đến khi mình chốt công thức tính.

            </div>

            <div class="d-flex gap-2 mt-4">

                <button
                    type="submit"
                    class="btn btn-primary px-4"
                >
                    Lưu điểm
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