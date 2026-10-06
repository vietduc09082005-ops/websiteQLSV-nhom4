<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$makhoa = trim($_GET["makhoa"] ?? "");
$tenkhoa = trim($_GET["tenkhoa"] ?? "");
$dienthoai = trim($_GET["dienthoai"] ?? "");

$makhoaLike = "%" . $makhoa . "%";
$tenkhoaLike = "%" . $tenkhoa . "%";
$dienthoaiLike = "%" . $dienthoai . "%";

$sql = "
    SELECT *
    FROM khoa
    WHERE
        (? = '' OR MAKHOA LIKE ?)
        AND
        (? = '' OR TENKHOA LIKE ?)
        AND
        (? = '' OR DIENTHOAI LIKE ?)
    ORDER BY MAKHOA ASC
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssssss",
    $makhoa,
    $makhoaLike,
    $tenkhoa,
    $tenkhoaLike,
    $dienthoai,
    $dienthoaiLike
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
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

    <title>Quản lý khoa</title>

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
                Quản lý khoa
            </h2>

            <div class="text-secondary mt-1">
                Quản lý và tra cứu thông tin khoa
            </div>

        </div>

        <a
            href="add.php"
            class="btn btn-primary px-4"
        >
            + Thêm khoa
        </a>

    </div>

    <?php
    if (isset($_GET["success"]) && $_GET["success"] == "1") {
    ?>

        <div class="alert alert-success">
            Thêm khoa thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["updated"]) && $_GET["updated"] == "1") {
    ?>

        <div class="alert alert-success">
            Cập nhật khoa thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["deleted"]) && $_GET["deleted"] == "1") {
    ?>

        <div class="alert alert-success">
            Xóa khoa thành công.
        </div>

    <?php
    }
    ?>

    <div class="table-card mb-4">

        <h5 class="mb-3">
            Tìm kiếm khoa
        </h5>

        <form method="GET">

            <div class="row g-3">

                <div class="col-md-4">

                    <label class="form-label">
                        Mã khoa
                    </label>

                    <input
                        type="text"
                        name="makhoa"
                        value="<?php echo htmlspecialchars($makhoa); ?>"
                        class="form-control"
                        placeholder="Ví dụ: CNTT"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Tên khoa
                    </label>

                    <input
                        type="text"
                        name="tenkhoa"
                        value="<?php echo htmlspecialchars($tenkhoa); ?>"
                        class="form-control"
                        placeholder="Ví dụ: Công nghệ thông tin"
                    >

                </div>

                <div class="col-md-4">

                    <label class="form-label">
                        Điện thoại
                    </label>

                    <input
                        type="text"
                        name="dienthoai"
                        value="<?php echo htmlspecialchars($dienthoai); ?>"
                        class="form-control"
                        placeholder="Nhập số điện thoại"
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
                Danh sách khoa
            </h5>

            <span class="text-secondary">

                Tìm thấy:

                <strong>
                    <?php echo mysqli_num_rows($result); ?>
                </strong>

                khoa

            </span>

        </div>

        <div class="table-wrapper">

            <table
                class="table table-bordered table-hover khoa-table"
            >

                <colgroup>

                    <col class="khoa-makhoa">
                    <col class="khoa-tenkhoa">
                    <col class="khoa-dienthoai">
                    <col class="khoa-action">

                </colgroup>

                <thead>

                    <tr>

                        <th>Mã khoa</th>
                        <th>Tên khoa</th>
                        <th>Điện thoại</th>
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
                            <?php echo htmlspecialchars($row["MAKHOA"]); ?>
                        </td>

                        <td class="text-wrap-cell">
                            <?php echo htmlspecialchars($row["TENKHOA"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["DIENTHOAI"]); ?>
                        </td>

                        <td class="action-cell">

                            <a
                                href="edit.php?makhoa=<?php echo urlencode($row["MAKHOA"]); ?>"
                                class="btn btn-outline-warning btn-sm"
                            >
                                Sửa
                            </a>

                            <a
                                href="delete.php?makhoa=<?php echo urlencode($row["MAKHOA"]); ?>"
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
                            colspan="4"
                            class="text-center py-5 text-secondary"
                        >
                            Không tìm thấy khoa phù hợp.
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