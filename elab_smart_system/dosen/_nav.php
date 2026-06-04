<nav class="bottom-nav-modern bottom-nav-dosen">
    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
        Dashboard
    </a>

    <a href="jadwal.php" class="<?= basename($_SERVER['PHP_SELF']) === 'jadwal.php' ? 'active' : '' ?>">
        Jadwal
    </a>

    <a href="verifikasi.php" class="<?= basename($_SERVER['PHP_SELF']) === 'verifikasi.php' ? 'active' : '' ?>">
        Verifikasi
    </a>

    <a href="../logout.php">
        Logout
    </a>
</nav>