<?php
session_start();

if (!isset($_SESSION["ID"]) || $_SESSION["ROLE"] != "admin") {
    header("Location: ../../index.php");
    exit;
}

require_once "../../config/database.php";

$error = "";
$success = "";

$mamh = "";
$tenmh = "";
$sotinchi = "3";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mamh = trim($_POST["mamh"] ?? "");
    $tenmh = trim($_POST["tenmh"] ?? "");
    $sotinchi = trim($_POST["sotinchi"] ?? "");

    if (
        $mamh === "" ||
        $tenmh === "" ||
        $sotinchi === ""
    ) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    }

    elseif (!preg_match('/^[A-Za-z0-9]+$/', $mamh)) {
        $error = "Mã môn học chỉ được chứa chữ và số.";
    }

    elseif (!preg_match('/^[\p{L}\p{N}\s\-\(\)]+$/u', $tenmh)) {
        $error = "Tên môn học chứa ký tự không hợp lệ.";
    }

    elseif (!ctype_digit($sotinchi)) {
        $error = "Số tín chỉ phải là số nguyên.";
    }

    elseif ((int)$sotinchi < 1 || (int)$sotinchi > 10) {
        $error = "Số tín chỉ phải từ 1 đến 10.";
    }

    else {

        $sqlCheck = "
            SELECT MAMH
            FROM monhoc
            WHERE MAMH = ?
            LIMIT 1
        ";

        $stmtCheck = mysqli_prepare($conn, $sqlCheck);

        mysqli_stmt_bind_param(
            $stmtCheck,
            "s",
            $mamh
        );

        mysqli_stmt_execute($stmtCheck);

        $resultCheck = mysqli_stmt_get_result($stmtCheck);

        if (mysqli_num_rows($resultCheck) > 0) {

            $error = "Mã môn học đã tồn tại.";

        } else {

            $sqlInsert = "
                INSERT INTO monhoc
                (
                    MAMH,
                    TENMH,
                    SOTINCHI
                )
                VALUES (?, ?, ?)
            ";

            $stmtInsert = mysqli_prepare(
                $conn,
                $sqlInsert
            );

            $sotinchiInt = (int)$sotinchi;

            mysqli_stmt_bind_param(
                $stmtInsert,
                "ssi",
                $mamh,
                $tenmh,
                $sotinchiInt
            );

            if (mysqli_stmt_execute($stmtInsert)) {

                header("Location: index.php?success=1");
                exit;

            } else {

                $error = "Không thể thêm môn học: " . mysqli_error($conn);
            }
        }
    }
}

$keyword = trim($_GET["keyword"] ?? "");

if ($keyword !== "") {

    $search = "%" . $keyword . "%";

    $sql = "
        SELECT *
        FROM monhoc
        WHERE MAMH LIKE ?
           OR TENMH LIKE ?
        ORDER BY MAMH ASC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $search,
        $search
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $sql = "
        SELECT *
        FROM monhoc
        ORDER BY MAMH ASC
    ";

    $result = mysqli_query($conn, $sql);
}

if (!$result) {
    die("Lỗi truy vấn: " . mysqli_error($conn));
}

$activePage = "monhoc";
?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Quản lý môn học</title>

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
            Quản lý môn học
        </h2>

        <div class="text-secondary mt-1">
            Quản lý thông tin môn học và tín chỉ
        </div>

    </div>

    <?php
    if (isset($_GET["success"]) && $_GET["success"] == "1") {
    ?>

        <div class="alert alert-success">
            Thêm môn học thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["updated"]) && $_GET["updated"] == "1") {
    ?>

        <div class="alert alert-success">
            Cập nhật môn học thành công.
        </div>

    <?php
    }
    ?>

    <?php
    if (isset($_GET["deleted"]) && $_GET["deleted"] == "1") {
    ?>

        <div class="alert alert-success">
            Xóa môn học thành công.
        </div>

    <?php
    }
    ?>

    <?php if ($error !== "") { ?>

        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php } ?>

    <div class="row g-4">

        <div class="col-lg-4">

            <div class="form-card">

                <h5 class="mb-4">
                    Thêm môn học mới
                </h5>

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label">
                            Mã môn học
                        </label>

                        <input
                            type="text"
                            name="mamh"
                            maxlength="10"
                            pattern="[A-Za-z0-9]+"
                            value="<?php echo htmlspecialchars($mamh); ?>"
                            class="form-control"
                            placeholder="Ví dụ: TH01"
                            required
                        >

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Tên môn học
                        </label>

                        <input
                            type="text"
                            name="tenmh"
                            maxlength="100"
                            value="<?php echo htmlspecialchars($tenmh); ?>"
                            class="form-control"
                            placeholder="Ví dụ: Lập trình PHP"
                            required
                        >

                    </div>

                    <div class="mb-4">

                        <label class="form-label">
                            Số tín chỉ
                        </label>

                        <input
                            type="number"
                            name="sotinchi"
                            min="1"
                            max="10"
                            step="1"
                            value="<?php echo htmlspecialchars($sotinchi); ?>"
                            class="form-control"
                            required
                        >

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Thêm mới
                    </button>

                </form>

            </div>

        </div>

        <div class="col-lg-8">

            <form
                method="GET"
                class="table-card mb-3"
            >

                <div class="row g-2">

                    <div class="col-md-8">

                        <input
                            type="text"
                            name="keyword"
                            value="<?php echo htmlspecialchars($keyword); ?>"
                            class="form-control"
                            placeholder="Tìm mã hoặc tên môn..."
                        >

                    </div>

                    <div class="col-md-4">

                        <button
                            type="submit"
                            class="btn btn-secondary w-100"
                        >
                            Tìm
                        </button>

                    </div>

                </div>

            </form>

            <div class="table-card p-0">

                <div class="table-wrapper">

                    <table
                        class="table table-bordered table-hover align-middle mb-0"
                        style="table-layout: fixed;"
                    >

                        <colgroup>

                            <col style="width: 70px;">

                            <col style="width: 150px;">

                            <col>

                            <col style="width: 130px;">

                            <col style="width: 180px;">

                        </colgroup>

                        <thead>

                            <tr>

                                <th>STT</th>

                                <th>Mã môn</th>

                                <th>Tên môn</th>

                                <th>Tín chỉ</th>

                                <th>Thao tác</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php
                        if (mysqli_num_rows($result) > 0) {

                            $stt = 1;

                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>

                            <tr>

                                <td>
                                    <?php echo $stt++; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["MAMH"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["TENMH"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["SOTINCHI"]); ?>
                                </td>

                                <td class="action-cell">

                                    <a
                                        href="edit.php?mamh=<?php echo urlencode($row["MAMH"]); ?>"
                                        class="btn btn-outline-warning btn-sm"
                                    >
                                        Sửa
                                    </a>

                                    <a
                                        href="delete.php?mamh=<?php echo urlencode($row["MAMH"]); ?>"
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
                                    class="text-center py-4 text-secondary"
                                >
                                    Chưa có dữ liệu môn học.
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

    </div>

</div>

</body>

</html>