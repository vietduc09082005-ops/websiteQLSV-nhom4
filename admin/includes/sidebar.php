<?php

if (!isset($activePage)) {
    $activePage = "";
}

$baseUrl = "/Websitequanlysinhvien";

?>

<div class="sidebar text-white">

    <div class="sidebar-title">
        QL Sinh Viên
    </div>

    <nav class="nav flex-column">

        <a
            class="nav-link <?php echo $activePage === "dashboard" ? "active" : ""; ?>"
            href="<?php echo $baseUrl; ?>/admin/dashboard.php"
        >
            Dashboard
        </a>

        <a
            class="nav-link <?php echo $activePage === "sinhvien" ? "active" : ""; ?>"
            href="<?php echo $baseUrl; ?>/admin/sinhvien/index.php"
        >
            Sinh viên
        </a>

        <a
            class="nav-link <?php echo $activePage === "lop" ? "active" : ""; ?>"
            href="<?php echo $baseUrl; ?>/admin/lop/index.php"
        >
            Lớp
        </a>

        <a
            class="nav-link <?php echo $activePage === "khoa" ? "active" : ""; ?>"
            href="<?php echo $baseUrl; ?>/admin/khoa/index.php"
        >
            Khoa
        </a>

        <a
            class="nav-link <?php echo $activePage === "monhoc" ? "active" : ""; ?>"
            href="<?php echo $baseUrl; ?>/admin/monhoc/index.php"
        >
            Môn học
        </a>

        <a
    class="nav-link <?php echo $activePage === "diem" ? "active" : ""; ?>"
    href="<?php echo $baseUrl; ?>/admin/diem/index.php"
>
    Điểm
</a>

        <a
    class="nav-link <?php echo $activePage === "chuyencan" ? "active" : ""; ?>"
    href="<?php echo $baseUrl; ?>/admin/chuyencan/index.php"
>
    Chuyên cần
</a>

        <a
            class="nav-link <?php echo $activePage === "taikhoan" ? "active" : ""; ?>"
            href="<?php echo $baseUrl; ?>/admin/taikhoan/index.php"
        >
            Tài khoản
        </a>

        <a
            class="nav-link text-danger mt-4"
            href="<?php echo $baseUrl; ?>/logout.php"
        >
            Đăng xuất
        </a>

    </nav>

</div>