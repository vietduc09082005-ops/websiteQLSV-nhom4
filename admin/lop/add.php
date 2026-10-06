<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$error = "";
$malop = "";
$tenlop = "";
$makhoa = "";

$sqlKhoa = "SELECT * FROM khoa ORDER BY TENKHOA ASC";
$resultKhoa = mysqli_query($conn, $sqlKhoa);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $malop = trim($_POST["malop"] ?? "");
    $tenlop = trim($_POST["tenlop"] ?? "");
    $makhoa = trim($_POST["makhoa"] ?? "");

    if (empty($malop) || empty($tenlop) || empty($makhoa)) {
        $error = "Vui lòng điền đầy đủ các thông tin bắt buộc.";
    } else {
        $sqlCheck = "SELECT MALOP FROM lop WHERE MALOP = ? LIMIT 1";
        $stmtCheck = mysqli_prepare($conn, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, "s", $malop);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_store_result($stmtCheck);

        if (mysqli_stmt_num_rows($stmtCheck) > 0) {
            $error = "Mã lớp này đã tồn tại trong hệ thống.";
            mysqli_stmt_close($stmtCheck);
        } else {
            mysqli_stmt_close($stmtCheck);

            // Mặc định SOSI là 0 khi vừa tạo lớp
            $sqlInsert = "INSERT INTO lop (MALOP, TENLOP, MAKHOA, SOSI) VALUES (?, ?, ?, 0)";
            $stmtInsert = mysqli_prepare($conn, $sqlInsert);
            mysqli_stmt_bind_param($stmtInsert, "sss", $malop, $tenlop, $makhoa);

            if (mysqli_stmt_execute($stmtInsert)) {
                mysqli_stmt_close($stmtInsert);
                header("Location: index.php?success=1");
                exit;
            } else {
                $error = "Lỗi khi thêm lớp: " . mysqli_error($conn);
            }
        }
    }
}

$activePage = "lop";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm lớp mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>

<?php require_once "../includes/sidebar.php"; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title">Thêm lớp mới</h2>
            <div class="text-secondary mt-1">Nhập thông tin lớp học vào hệ thống</div>
        </div>
        <a href="index.php" class="btn btn-outline-secondary px-3">Quay lại</a>
    </div>

    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-body p-4">

            <?php if ($error !== ""): ?>
                <div class="alert alert-danger py-2 mb-3">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label fw-medium">Mã lớp <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="malop" 
                        class="form-control" 
                        placeholder="Ví dụ: CNTT01"
                        value="<?php echo htmlspecialchars($malop); ?>" 
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">Tên lớp <span class="text-danger">*</span></label>
                    <input 
                        type="text" 
                        name="tenlop" 
                        class="form-control" 
                        placeholder="Ví dụ: Công nghệ thông tin 1"
                        value="<?php echo htmlspecialchars($tenlop); ?>" 
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label fw-medium">Thuộc khoa <span class="text-danger">*</span></label>
                    <select name="makhoa" class="form-select" required>
                        <option value="">-- Chọn khoa --</option>
                        <?php 
                        if ($resultKhoa && mysqli_num_rows($resultKhoa) > 0) {
                            while ($rowKhoa = mysqli_fetch_assoc($resultKhoa)) {
                                $selected = ($rowKhoa["MAKHOA"] == $makhoa) ? "selected" : "";
                                echo "<option value='" . htmlspecialchars($rowKhoa["MAKHOA"]) . "' $selected>" 
                                     . htmlspecialchars($rowKhoa["TENKHOA"]) 
                                     . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="index.php" class="btn btn-light border px-4">Hủy</a>
                    <button type="submit" class="btn btn-primary px-4">Lưu lại</button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>