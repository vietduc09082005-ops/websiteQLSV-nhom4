<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$masv = trim($_GET["masv"] ?? "");
$hoten = trim($_GET["hoten"] ?? "");
$mamh = trim($_GET["mamh"] ?? "");
$tenmh = trim($_GET["tenmh"] ?? "");
$ngayhoc = trim($_GET["ngayhoc"] ?? "");
$trangthai = trim($_GET["trangthai"] ?? "");

$masvLike = "%" . $masv . "%";
$hotenLike = "%" . $hoten . "%";
$mamhLike = "%" . $mamh . "%";
$tenmhLike = "%" . $tenmh . "%";

$sql = "
    SELECT
        chuyencan.*,
        sinhvien.HODEM,
        sinhvien.TEN,
        monhoc.TENMH
    FROM chuyencan
    INNER JOIN sinhvien
        ON chuyencan.MASV = sinhvien.MASV
    INNER JOIN monhoc
        ON chuyencan.MAMH = monhoc.MAMH
    WHERE
        (? = '' OR chuyencan.MASV LIKE ?)
        AND
        (? = '' OR CONCAT(sinhvien.HODEM, ' ', sinhvien.TEN) LIKE ?)
        AND
        (? = '' OR chuyencan.MAMH LIKE ?)
        AND
        (? = '' OR monhoc.TENMH LIKE ?)
        AND
        (? = '' OR chuyencan.NGAYHOC = ?)
        AND
        (? = '' OR chuyencan.TRANGTHAI = ?)
    ORDER BY chuyencan.NGAYHOC DESC, chuyencan.MACC DESC
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssssssssssss",
    $masv,
    $masvLike,
    $hoten,
    $hotenLike,
    $mamh,
    $mamhLike,
    $tenmh,
    $tenmhLike,
    $ngayhoc,
    $ngayhoc,
    $trangthai,
    $trangthai
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
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

    <title>Quản lý chuyên cần</title>

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
                Quản lý chuyên cần
            </h2>

            <div class="text-secondary mt-1">
                Quản lý và tra cứu tình trạng đi học của sinh viên
            </div>

        </div>

        <a
            href="add.php"
            class="btn btn-primary px-4"
        >
            + Điểm danh
        </a>

    </div>

    <?php
    if (isset($_GET["success"]) && $_GET["success"] == "1") {
    ?>

        <div class="alert alert-success">
            Thêm chuyên cần thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["updated"]) && $_GET["updated"] == "1") {
    ?>

        <div class="alert alert-success">
            Cập nhật chuyên cần thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["deleted"]) && $_GET["deleted"] == "1") {
    ?>

        <div class="alert alert-success">
            Xóa chuyên cần thành công.
        </div>

    <?php
    }
    ?>

    <div class="table-card mb-4">

        <h5 class="mb-3">
            Tìm kiếm chuyên cần
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
                        Họ tên sinh viên
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
                        Ngày học
                    </label>

                    <input
                        type="date"
                        name="ngayhoc"
                        value="<?php echo htmlspecialchars($ngayhoc); ?>"
                        class="form-control"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Mã môn học
                    </label>

                    <input
                        type="text"
                        name="mamh"
                        value="<?php echo htmlspecialchars($mamh); ?>"
                        class="form-control"
                        placeholder="Ví dụ: PHP01"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Tên môn học
                    </label>

                    <input
                        type="text"
                        name="tenmh"
                        value="<?php echo htmlspecialchars($tenmh); ?>"
                        class="form-control"
                        placeholder="Ví dụ: Lập trình PHP"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Trạng thái
                    </label>

                    <select
                        name="trangthai"
                        class="form-select"
                    >

                        <option value="">
                            -- Tất cả --
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
                Danh sách chuyên cần
            </h5>

            <span class="text-secondary">

                Tìm thấy:

                <strong>
                    <?php echo mysqli_num_rows($result); ?>
                </strong>

                bản ghi

            </span>

        </div>

        <div class="table-wrapper">

            <table
                class="table table-bordered table-hover align-middle"
                style="min-width: 1150px; table-layout: fixed;"
            >

                <colgroup>

                    <col style="width: 80px;">
                    <col style="width: 220px;">
                    <col style="width: 230px;">
                    <col style="width: 130px;">
                    <col style="width: 140px;">
                    <col>
                    <col style="width: 170px;">

                </colgroup>

                <thead>

                    <tr>

                        <th>Mã</th>
                        <th>Sinh viên</th>
                        <th>Môn học</th>
                        <th>Ngày học</th>
                        <th>Trạng thái</th>
                        <th>Ghi chú</th>
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
                            <?php echo htmlspecialchars($row["MACC"]); ?>
                        </td>

                        <td class="text-wrap-cell">

                            <?php
                            echo htmlspecialchars(
                                $row["MASV"]
                                . " - "
                                . $row["HODEM"]
                                . " "
                                . $row["TEN"]
                            );
                            ?>

                        </td>

                        <td class="text-wrap-cell">

                            <?php
                            echo htmlspecialchars(
                                $row["MAMH"]
                                . " - "
                                . $row["TENMH"]
                            );
                            ?>

                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["NGAYHOC"]); ?>
                        </td>

                        <td>

                            <?php
                            $trangthaiDong = $row["TRANGTHAI"];

                            if ($trangthaiDong === "Có mặt") {
                                $badge = "success";
                            } elseif ($trangthaiDong === "Vắng") {
                                $badge = "danger";
                            } elseif ($trangthaiDong === "Muộn") {
                                $badge = "warning";
                            } else {
                                $badge = "secondary";
                            }
                            ?>

                            <span class="badge bg-<?php echo $badge; ?>">
                                <?php echo htmlspecialchars($trangthaiDong); ?>
                            </span>

                        </td>

                        <td class="text-wrap-cell">

                            <?php
                            echo htmlspecialchars(
                                $row["GHICHU"] ?? ""
                            );
                            ?>

                        </td>

                        <td class="action-cell">

                            <a
                                href="edit.php?id=<?php echo urlencode($row["MACC"]); ?>"
                                class="btn btn-outline-warning btn-sm"
                            >
                                Sửa
                            </a>

                            <a
                                href="delete.php?id=<?php echo urlencode($row["MACC"]); ?>"
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
                            colspan="7"
                            class="text-center py-5 text-secondary"
                        >
                            Không tìm thấy dữ liệu chuyên cần phù hợp.
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