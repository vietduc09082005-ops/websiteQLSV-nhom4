<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$sqlSinhVien = "
    SELECT COUNT(*) AS total
    FROM sinhvien
";

$resultSinhVien = mysqli_query(
    $conn,
    $sqlSinhVien
);

$rowSinhVien = mysqli_fetch_assoc(
    $resultSinhVien
);

$totalSinhVien = $rowSinhVien["total"];


$sqlLop = "
    SELECT COUNT(*) AS total
    FROM lop
";

$resultLop = mysqli_query(
    $conn,
    $sqlLop
);

$rowLop = mysqli_fetch_assoc(
    $resultLop
);

$totalLop = $rowLop["total"];


$sqlKhoa = "
    SELECT COUNT(*) AS total
    FROM khoa
";

$resultKhoa = mysqli_query(
    $conn,
    $sqlKhoa
);

$rowKhoa = mysqli_fetch_assoc(
    $resultKhoa
);

$totalKhoa = $rowKhoa["total"];


$sqlMonHoc = "
    SELECT COUNT(*) AS total
    FROM monhoc
";

$resultMonHoc = mysqli_query(
    $conn,
    $sqlMonHoc
);

$rowMonHoc = mysqli_fetch_assoc(
    $resultMonHoc
);

$totalMonHoc = $rowMonHoc["total"];


$masv = trim($_GET["masv"] ?? "");
$hoten = trim($_GET["hoten"] ?? "");
$malop = trim($_GET["malop"] ?? "");
$tenlop = trim($_GET["tenlop"] ?? "");
$makhoa = trim($_GET["makhoa"] ?? "");
$tenkhoa = trim($_GET["tenkhoa"] ?? "");

$showSearchResult = isset($_GET["search"]);

$searchResult = null;

if ($showSearchResult) {

    $masvLike = "%" . $masv . "%";
    $hotenLike = "%" . $hoten . "%";
    $malopLike = "%" . $malop . "%";
    $tenlopLike = "%" . $tenlop . "%";
    $makhoaLike = "%" . $makhoa . "%";
    $tenkhoaLike = "%" . $tenkhoa . "%";

    $sqlSearch = "
        SELECT
            sinhvien.MASV,
            sinhvien.HODEM,
            sinhvien.TEN,
            sinhvien.EMAIL,
            sinhvien.SDT,
            lop.MALOP,
            lop.TENLOP,
            khoa.MAKHOA,
            khoa.TENKHOA
        FROM sinhvien

        INNER JOIN lop
            ON sinhvien.MALOP = lop.MALOP

        INNER JOIN khoa
            ON lop.MAKHOA = khoa.MAKHOA

        WHERE
            (? = '' OR sinhvien.MASV LIKE ?)

            AND

            (
                ? = ''
                OR CONCAT(
                    sinhvien.HODEM,
                    ' ',
                    sinhvien.TEN
                ) LIKE ?
            )

            AND

            (? = '' OR lop.MALOP LIKE ?)

            AND

            (? = '' OR lop.TENLOP LIKE ?)

            AND

            (? = '' OR khoa.MAKHOA LIKE ?)

            AND

            (? = '' OR khoa.TENKHOA LIKE ?)

        ORDER BY sinhvien.MASV ASC
    ";

    $stmtSearch = mysqli_prepare(
        $conn,
        $sqlSearch
    );

    mysqli_stmt_bind_param(
        $stmtSearch,
        "ssssssssssss",
        $masv,
        $masvLike,
        $hoten,
        $hotenLike,
        $malop,
        $malopLike,
        $tenlop,
        $tenlopLike,
        $makhoa,
        $makhoaLike,
        $tenkhoa,
        $tenkhoaLike
    );

    mysqli_stmt_execute(
        $stmtSearch
    );

    $searchResult =
        mysqli_stmt_get_result(
            $stmtSearch
        );
}

$activePage = "dashboard";
?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

</head>

<body>

<?php
require_once "includes/sidebar.php";
?>

<div class="main-content">

    <div class="mb-4">

        <h2 class="page-title">
            Dashboard
        </h2>

        <div class="text-secondary mt-1">
            Tổng quan hệ thống quản lý sinh viên
        </div>

    </div>


    <div class="row g-4 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-secondary mb-2">
                        Sinh viên
                    </div>

                    <div class="fs-2 fw-bold">
                        <?php echo $totalSinhVien; ?>
                    </div>

                    <a
                        href="sinhvien/index.php"
                        class="btn btn-outline-primary btn-sm mt-3"
                    >
                        Xem sinh viên
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-secondary mb-2">
                        Lớp
                    </div>

                    <div class="fs-2 fw-bold">
                        <?php echo $totalLop; ?>
                    </div>

                    <a
                        href="lop/index.php"
                        class="btn btn-outline-primary btn-sm mt-3"
                    >
                        Xem lớp
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-secondary mb-2">
                        Khoa
                    </div>

                    <div class="fs-2 fw-bold">
                        <?php echo $totalKhoa; ?>
                    </div>

                    <a
                        href="khoa/index.php"
                        class="btn btn-outline-primary btn-sm mt-3"
                    >
                        Xem khoa
                    </a>

                </div>

            </div>

        </div>


        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <div class="text-secondary mb-2">
                        Môn học
                    </div>

                    <div class="fs-2 fw-bold">
                        <?php echo $totalMonHoc; ?>
                    </div>

                    <a
                        href="monhoc/index.php"
                        class="btn btn-outline-primary btn-sm mt-3"
                    >
                        Xem môn học
                    </a>

                </div>

            </div>

        </div>

    </div>


    <div class="table-card mb-4">

        <h5 class="mb-3">
            Tra cứu nhanh
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

            </div>


            <div class="d-flex gap-2 mt-4">

                <button
                    type="submit"
                    name="search"
                    value="1"
                    class="btn btn-primary px-4"
                >
                    Tìm kiếm
                </button>

                <a
                    href="dashboard.php"
                    class="btn btn-outline-secondary px-4"
                >
                    Xóa bộ lọc
                </a>

            </div>

        </form>

    </div>


    <?php if ($showSearchResult) { ?>

        <div class="table-card">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    Kết quả tìm kiếm
                </h5>

                <span class="text-secondary">

                    Tìm thấy:

                    <strong>
                        <?php echo mysqli_num_rows($searchResult); ?>
                    </strong>

                    sinh viên

                </span>

            </div>

            <div class="table-wrapper">

                <table
                    class="table table-bordered table-hover align-middle"
                    style="min-width: 1200px; table-layout: fixed;"
                >

                    <colgroup>

                        <col style="width: 110px;">
                        <col style="width: 200px;">
                        <col style="width: 130px;">
                        <col style="width: 200px;">
                        <col style="width: 120px;">
                        <col style="width: 160px;">
                        <col style="width: 220px;">
                        <col style="width: 140px;">

                    </colgroup>

                    <thead>

                        <tr>

                            <th>Mã SV</th>
                            <th>Họ tên</th>
                            <th>Mã lớp</th>
                            <th>Tên lớp</th>
                            <th>Mã khoa</th>
                            <th>Tên khoa</th>
                            <th>Email</th>
                            <th>SĐT</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php
                    if (mysqli_num_rows($searchResult) > 0) {

                        while (
                            $row = mysqli_fetch_assoc(
                                $searchResult
                            )
                        ) {
                    ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["MASV"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["HODEM"]
                                    . " "
                                    . $row["TEN"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["MALOP"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["TENLOP"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["MAKHOA"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["TENKHOA"]
                                );
                                ?>
                            </td>

                            <td class="text-wrap-cell">
                                <?php
                                echo htmlspecialchars(
                                    $row["EMAIL"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $row["SDT"]
                                );
                                ?>
                            </td>

                        </tr>

                    <?php
                        }

                    } else {
                    ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center py-5 text-secondary"
                            >
                                Không tìm thấy dữ liệu phù hợp.
                            </td>

                        </tr>

                    <?php
                    }
                    ?>

                    </tbody>

                </table>

            </div>

        </div>

    <?php } ?>

</div>

</body>

</html>