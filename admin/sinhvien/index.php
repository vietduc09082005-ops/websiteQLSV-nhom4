<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$masv = trim($_GET["masv"] ?? "");
$hoten = trim($_GET["hoten"] ?? "");
$ngaysinh = trim($_GET["ngaysinh"] ?? "");
$gioitinh = trim($_GET["gioitinh"] ?? "");
$malop = trim($_GET["malop"] ?? "");
$email = trim($_GET["email"] ?? "");
$sdt = trim($_GET["sdt"] ?? "");
$diachi = trim($_GET["diachi"] ?? "");

$masvLike = "%" . $masv . "%";
$hotenLike = "%" . $hoten . "%";
$malopLike = "%" . $malop . "%";
$emailLike = "%" . $email . "%";
$sdtLike = "%" . $sdt . "%";
$diachiLike = "%" . $diachi . "%";

$sql = "
    SELECT *
    FROM sinhvien
    WHERE
        (? = '' OR MASV LIKE ?)
        AND
        (? = '' OR CONCAT(HODEM, ' ', TEN) LIKE ?)
        AND
        (? = '' OR NGAYSINH = ?)
        AND
        (? = '' OR GIOITINH = ?)
        AND
        (? = '' OR MALOP LIKE ?)
        AND
        (? = '' OR EMAIL LIKE ?)
        AND
        (? = '' OR SDT LIKE ?)
        AND
        (? = '' OR DIACHI LIKE ?)
    ORDER BY MASV ASC
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssssssssssssssss",
    $masv,
    $masvLike,
    $hoten,
    $hotenLike,
    $ngaysinh,
    $ngaysinh,
    $gioitinh,
    $gioitinh,
    $malop,
    $malopLike,
    $email,
    $emailLike,
    $sdt,
    $sdtLike,
    $diachi,
    $diachiLike
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
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

    <title>Quản lý sinh viên</title>

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

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="page-title">
                Quản lý sinh viên
            </h2>

            <div class="text-secondary mt-1">
                Quản lý và tra cứu thông tin sinh viên
            </div>

        </div>

        <a
            href="add.php"
            class="btn btn-primary px-4"
        >
            + Thêm sinh viên
        </a>

    </div>

    <?php
    if (isset($_GET["success"]) && $_GET["success"] == "1") {
    ?>

        <div class="alert alert-success">
            Thêm sinh viên thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["updated"]) && $_GET["updated"] == "1") {
    ?>

        <div class="alert alert-success">
            Cập nhật sinh viên thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["deleted"]) && $_GET["deleted"] == "1") {
    ?>

        <div class="alert alert-success">
            Xóa sinh viên thành công.
        </div>

    <?php
    }
    ?>

    <div class="table-card mb-4">

        <h5 class="mb-3">
            Tìm kiếm sinh viên
        </h5>

        <form method="GET">

            <div class="row g-3">

                <div class="col-md-4">

                    <label class="form-label">
                        Mã sinh viên
                    </label>

                    <input
                        type="text"
                        name="masv"
                        value="<?php echo htmlspecialchars($masv); ?>"
                        class="form-control"
                        placeholder="Ví dụ: SV001"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Họ tên
                    </label>

                    <input
                        type="text"
                        name="hoten"
                        value="<?php echo htmlspecialchars($hoten); ?>"
                        class="form-control"
                        placeholder="Ví dụ: Nguyễn Văn An"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Mã lớp
                    </label>

                    <input
                        type="text"
                        name="malop"
                        value="<?php echo htmlspecialchars($malop); ?>"
                        class="form-control"
                        placeholder="Ví dụ: CNTT01"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Ngày sinh
                    </label>

                    <input
                        type="date"
                        name="ngaysinh"
                        value="<?php echo htmlspecialchars($ngaysinh); ?>"
                        class="form-control"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Giới tính
                    </label>

                    <select
                        name="gioitinh"
                        class="form-select"
                    >

                        <option value="">
                            -- Tất cả --
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

                <div class="col-md-4">

                    <label class="form-label">
                        Số điện thoại
                    </label>

                    <input
                        type="text"
                        name="sdt"
                        value="<?php echo htmlspecialchars($sdt); ?>"
                        class="form-control"
                        placeholder="Nhập số điện thoại"
                    >

                </div>

                <div class="col-md-6">

                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="text"
                        name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        class="form-control"
                        placeholder="Nhập email"
                    >

                </div>

                <div class="col-md-6">

                    <label class="form-label">
                        Địa chỉ
                    </label>

                    <input
                        type="text"
                        name="diachi"
                        value="<?php echo htmlspecialchars($diachi); ?>"
                        class="form-control"
                        placeholder="Nhập địa chỉ"
                    >

                </div>

            </div>

            <div class="d-flex gap-2 mt-4">

                <button
                    type="submit"
                    class="btn btn-primary px-4"
                >
                    Tìm kiếm
                </button>

                <a
                    href="index.php"
                    class="btn btn-outline-secondary px-4"
                >
                    Xóa bộ lọc
                </a>

            </div>

        </form>

    </div>

    <div class="table-card">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="mb-0">
                Danh sách sinh viên
            </h5>

            <span class="text-secondary">

                Tìm thấy:

                <strong>
                    <?php echo mysqli_num_rows($result); ?>
                </strong>

                sinh viên

            </span>

        </div>

        <div class="table-wrapper">

            <table class="table table-bordered table-hover student-table">

                <colgroup>

                    <col class="col-masv">
                    <col class="col-hodem">
                    <col class="col-ten">
                    <col class="col-ngaysinh">
                    <col class="col-gioitinh">
                    <col class="col-diachi">
                    <col class="col-email">
                    <col class="col-malop">
                    <col class="col-sdt">
                    <col class="col-action">

                </colgroup>

                <thead>

                    <tr>

                        <th>Mã SV</th>
                        <th>Họ đệm</th>
                        <th>Tên</th>
                        <th>Ngày sinh</th>
                        <th>Giới tính</th>
                        <th>Địa chỉ</th>
                        <th>Email</th>
                        <th>Mã lớp</th>
                        <th>SĐT</th>
                        <th>Thao tác</th>

                    </tr>

                </thead>

                <tbody>

                <?php
                if (mysqli_num_rows($result) > 0) {

                    while ($row = mysqli_fetch_assoc($result)) {
                ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($row["MASV"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["HODEM"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["TEN"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["NGAYSINH"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["GIOITINH"]); ?>
                        </td>

                        <td class="text-wrap-cell">
                            <?php echo htmlspecialchars($row["DIACHI"]); ?>
                        </td>

                        <td class="text-wrap-cell">
                            <?php echo htmlspecialchars($row["EMAIL"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["MALOP"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["SDT"]); ?>
                        </td>

                        <td class="action-cell">

                            <a
                                href="edit.php?masv=<?php echo urlencode($row["MASV"]); ?>"
                                class="btn btn-outline-warning btn-sm"
                            >
                                Sửa
                            </a>

                            <a
                                href="delete.php?masv=<?php echo urlencode($row["MASV"]); ?>"
                                class="btn btn-outline-danger btn-sm"
                            >
                                Xóa
                            </a>

                        </td>

                    </tr>

                <?php
                    }

                } else {
                ?>

                    <tr>

                        <td
                            colspan="10"
                            class="text-center py-5 text-secondary"
                        >
                            Không tìm thấy sinh viên phù hợp.
                        </td>

                    </tr>

                <?php
                }
                ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>

</html>