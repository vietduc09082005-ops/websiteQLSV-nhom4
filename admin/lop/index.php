<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$malop = trim($_GET["malop"] ?? "");
$tenlop = trim($_GET["tenlop"] ?? "");
$makhoa = trim($_GET["makhoa"] ?? "");
$sosi = trim($_GET["sosi"] ?? "");

$malopLike = "%" . $malop . "%";
$tenlopLike = "%" . $tenlop . "%";
$makhoaLike = "%" . $makhoa . "%";

$sql = "
    SELECT *
    FROM lop
    WHERE
        (? = '' OR MALOP LIKE ?)
        AND
        (? = '' OR TENLOP LIKE ?)
        AND
        (? = '' OR MAKHOA LIKE ?)
        AND
        (? = '' OR SOSI = ?)
    ORDER BY MALOP ASC
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssssssss",
    $malop,
    $malopLike,
    $tenlop,
    $tenlopLike,
    $makhoa,
    $makhoaLike,
    $sosi,
    $sosi
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}

$activePage = "lop";
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Quản lý lớp</title>

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
                Quản lý lớp
            </h2>

            <div class="text-secondary mt-1">
                Quản lý và tra cứu thông tin lớp
            </div>

        </div>

        <a
            href="add.php"
            class="btn btn-primary px-4"
        >
            + Thêm lớp
        </a>

    </div>

    <?php
    if (isset($_GET["success"]) && $_GET["success"] == "1") {
    ?>

        <div class="alert alert-success">
            Thêm lớp thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["updated"]) && $_GET["updated"] == "1") {
    ?>

        <div class="alert alert-success">
            Cập nhật lớp thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["deleted"]) && $_GET["deleted"] == "1") {
    ?>

        <div class="alert alert-success">
            Xóa lớp thành công.
        </div>

    <?php
    }
    ?>

    <div class="table-card mb-4">

        <h5 class="mb-3">
            Tìm kiếm lớp
        </h5>

        <form method="GET">

            <div class="row g-3">

                <div class="col-md-3">

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

                <div class="col-md-3">

                    <label class="form-label">
                        Tên lớp
                    </label>

                    <input
                        type="text"
                        name="tenlop"
                        value="<?php echo htmlspecialchars($tenlop); ?>"
                        class="form-control"
                        placeholder="Nhập tên lớp"
                    >

                </div>

                <div class="col-md-3">

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

                <div class="col-md-3">

                    <label class="form-label">
                        Sĩ số
                    </label>

                    <input
                        type="number"
                        name="sosi"
                        min="0"
                        value="<?php echo htmlspecialchars($sosi); ?>"
                        class="form-control"
                        placeholder="Ví dụ: 40"
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
                Danh sách lớp
            </h5>

            <span class="text-secondary">

                Tìm thấy:

                <strong>
                    <?php echo mysqli_num_rows($result); ?>
                </strong>

                lớp

            </span>

        </div>

        <div class="table-wrapper">

            <table
                class="table table-bordered table-hover align-middle"
                style="table-layout: fixed;"
            >

                <colgroup>

                    <col style="width: 150px;">
                    <col>
                    <col style="width: 150px;">
                    <col style="width: 120px;">
                    <col style="width: 180px;">

                </colgroup>

                <thead>

                    <tr>

                        <th>Mã lớp</th>
                        <th>Tên lớp</th>
                        <th>Mã khoa</th>
                        <th>Sĩ số</th>
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
                            <?php echo htmlspecialchars($row["MALOP"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["TENLOP"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["MAKHOA"]); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["SOSI"]); ?>
                        </td>

                        <td class="action-cell">

                            <a
                                href="edit.php?malop=<?php echo urlencode($row["MALOP"]); ?>"
                                class="btn btn-outline-warning btn-sm"
                            >
                                Sửa
                            </a>

                            <a
                                href="delete.php?malop=<?php echo urlencode($row["MALOP"]); ?>"
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
                            colspan="5"
                            class="text-center py-5 text-secondary"
                        >
                            Không tìm thấy lớp phù hợp.
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