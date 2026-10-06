<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$keyword = trim($_GET["keyword"] ?? "");

if ($keyword !== "") {

    $search = "%" . $keyword . "%";

    $sql = "
        SELECT
            diem.*,
            sinhvien.HODEM,
            sinhvien.TEN,
            monhoc.TENMH
        FROM diem
        INNER JOIN sinhvien
            ON diem.MASV = sinhvien.MASV
        INNER JOIN monhoc
            ON diem.MAMH = monhoc.MAMH
        WHERE diem.MASV LIKE ?
           OR diem.MAMH LIKE ?
           OR sinhvien.HODEM LIKE ?
           OR sinhvien.TEN LIKE ?
           OR monhoc.TENMH LIKE ?
        ORDER BY diem.MADIEM DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $search,
        $search,
        $search,
        $search,
        $search
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $sql = "
        SELECT
            diem.*,
            sinhvien.HODEM,
            sinhvien.TEN,
            monhoc.TENMH
        FROM diem
        INNER JOIN sinhvien
            ON diem.MASV = sinhvien.MASV
        INNER JOIN monhoc
            ON diem.MAMH = monhoc.MAMH
        ORDER BY diem.MADIEM DESC
    ";

    $result = mysqli_query($conn, $sql);
}

if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
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

    <title>Quản lý điểm</title>

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
                Quản lý điểm
            </h2>

            <div class="text-secondary mt-1">
                Quản lý kết quả học tập của sinh viên
            </div>

        </div>

        <a
            href="add.php"
            class="btn btn-primary px-4"
        >
            + Thêm điểm
        </a>

    </div>

    <?php
    if (isset($_GET["success"]) && $_GET["success"] == "1") {
    ?>

        <div class="alert alert-success">
            Thêm điểm thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["updated"]) && $_GET["updated"] == "1") {
    ?>

        <div class="alert alert-success">
            Cập nhật điểm thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["deleted"]) && $_GET["deleted"] == "1") {
    ?>

        <div class="alert alert-success">
            Xóa điểm thành công.
        </div>

    <?php
    }
    ?>

    <div class="table-card mb-3">

        <form method="GET">

            <div class="row g-2">

                <div class="col-md-9">

                    <input
                        type="text"
                        name="keyword"
                        value="<?php echo htmlspecialchars($keyword); ?>"
                        class="form-control"
                        placeholder="Tìm sinh viên hoặc môn học..."
                    >

                </div>

                <div class="col-md-3">

                    <button
                        type="submit"
                        class="btn btn-secondary w-100"
                    >
                        Tìm
                    </button>

                </div>

            </div>

        </form>

    </div>

    <div class="table-card">

        <div class="table-wrapper">

            <table
                class="table table-bordered table-hover align-middle"
                style="min-width: 1650px; table-layout: fixed;"
            >

                <colgroup>

                    <col style="width: 80px;">
                    <col style="width: 220px;">
                    <col style="width: 220px;">

                    <col style="width: 95px;">
                    <col style="width: 95px;">
                    <col style="width: 95px;">

                    <col style="width: 95px;">
                    <col style="width: 95px;">

                    <col style="width: 95px;">
                    <col style="width: 95px;">

                    <col style="width: 110px;">
                    <col style="width: 170px;">

                </colgroup>

                <thead>

                    <tr>

                        <th>Mã</th>

                        <th>Sinh viên</th>

                        <th>Môn học</th>

                        <th>1 tiết 1</th>

                        <th>1 tiết 2</th>

                        <th>1 tiết 3</th>

                        <th>Giữa kỳ 1</th>

                        <th>Giữa kỳ 2</th>

                        <th>Cuối kỳ 1</th>

                        <th>Cuối kỳ 2</th>

                        <th>Tổng kết</th>

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
                            <?php echo htmlspecialchars($row["MADIEM"]); ?>
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
                            <?php echo $row["DIEM1TIET1"] ?? "-"; ?>
                        </td>

                        <td>
                            <?php echo $row["DIEM1TIET2"] ?? "-"; ?>
                        </td>

                        <td>
                            <?php echo $row["DIEM1TIET3"] ?? "-"; ?>
                        </td>

                        <td>
                            <?php echo $row["DIEMGIUAKY1"] ?? "-"; ?>
                        </td>

                        <td>
                            <?php echo $row["DIEMGIUAKY2"] ?? "-"; ?>
                        </td>

                        <td>
                            <?php echo $row["DIEMCUOIKY1"] ?? "-"; ?>
                        </td>

                        <td>
                            <?php echo $row["DIEMCUOIKY2"] ?? "-"; ?>
                        </td>

                        <td>

                            <strong>
                                <?php
                                echo $row["DIEMTONGKET"] !== null
                                    ? htmlspecialchars($row["DIEMTONGKET"])
                                    : "-";
                                ?>
                            </strong>

                        </td>

                        <td class="action-cell">

                            <a
                                href="edit.php?id=<?php echo urlencode($row["MADIEM"]); ?>"
                                class="btn btn-outline-warning btn-sm"
                            >
                                Sửa
                            </a>

                            <a
                                href="delete.php?id=<?php echo urlencode($row["MADIEM"]); ?>"
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
                            colspan="12"
                            class="text-center py-5 text-secondary"
                        >
                            Chưa có dữ liệu điểm.
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