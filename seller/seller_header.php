<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand fw-bold fs-3" href="seller-dashboard.php">Seller Dashboard</a>

        <!-- Hamburger Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Nav Menu -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto gap-lg-3">

                <li class="nav-item"><a class="nav-link" href="../public/seller-dashboard.php">Home</a></li>

                <!-- GEM DROPDOWN -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Gems</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../seller/add-gem.php">Add New Gem</a></li>
                        <li><a class="dropdown-item" href="../seller/manage_gems.php">Manage Gems</a></li>
                    </ul>
                </li>

                <!-- BLOG DROPDOWN -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Blogs</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../seller/blog_add.php">Add New Blog</a></li>
                        <li><a class="dropdown-item" href="../seller/manage_blogs.php">Manage Blogs</a></li>
                    </ul>
                </li>

                <!-- AUTH -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <?php endif; ?>

            </ul>
        </div>

    </div>
</nav>

<style>
/* NAVBAR BASE */
.navbar {
    background: #fff !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* LOGO */
.navbar-brand {
    color: #DB2777 !important;
    font-weight: 800 !important;
    font-size: 28px !important;
    text-decoration: none;
}

/* NAV LINKS */
.nav-link {
    color: #111 !important;
    font-weight: 600 !important;
    transition: 0.3s ease;
}
.nav-link:hover {
    color: #EC4899 !important;
}

/* Desktop spacing */
@media (min-width: 992px) {
    .navbar-nav .nav-item {
        margin-left: 12px;
        margin-right: 12px;
    }
}

/* TOGGLER BUTTON */
.navbar-toggler {
    border: none !important;
}
.navbar-toggler:focus {
    outline: none !important;
    box-shadow: none !important;
}

/* MOBILE MENU */
@media (max-width: 991px) {
    .navbar-collapse {
        background: #fff;
        padding: 25px 0 !important;   
        text-align: center;           
        border-top: 1px solid #eee;
    }

    .navbar-nav {
        width: 100%;
        align-items: center !important; 
    }

    .navbar-nav .nav-item {
        width: 100%;
        padding: 12px 0;
    }

    .navbar-nav .nav-link {
        font-size: 18px !important;
        padding: 15px 0 !important;      
        width: 100%;
        text-align: center;               
        border-bottom: 1px solid #f0f0f0;
    }

    .navbar-nav .nav-item:last-child .nav-link {
        border-bottom: none;
    }
}

/* DROPDOWN ITEMS - HIDDEN BY DEFAULT */
.dropdown-menu {
    background: #fff;
    border-radius: 6px;
    border: 1px solid #eee;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    display: none; /* HIDDEN */
}

/* Show dropdown on hover (desktop) */
@media (min-width: 992px) {
    .nav-item.dropdown:hover .dropdown-menu {
        display: block;
    }
}

/* DROPDOWN ITEM STYLE */
.dropdown-item {
    font-weight: 500;
    color: #111 !important;
    padding: 8px 20px;
    transition: 0.3s ease;
}
.dropdown-item:hover {
    color: #EC4899 !important;
    background-color: #f8f0f7;
}

/* MOBILE DROPDOWN - toggled by Bootstrap */
@media (max-width: 991px) {
    .dropdown-menu {
        position: static;
        float: none;
        width: 100%;
        border: none;
        box-shadow: none;
        background: transparent;
    }

    .dropdown-item {
        text-align: center;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        width: 100%;
        background: transparent;
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }
}

/* Remove default arrow */
.dropdown-toggle::after {
    display: inline-block;
    margin-left: 5px;
    vertical-align: middle;
    border-top: 4px solid;
    border-right: 4px solid transparent;
    border-left: 4px solid transparent;
    content: "";
}

/* Desktop spacing */
@media (min-width: 992px) {
    .navbar-nav .nav-item {
        margin-left: 12px;
        margin-right: 12px;
    }
}


.navbar-toggler {
    border: none !important;
}
.navbar-toggler:focus {
    outline: none !important;
    box-shadow: none !important;
}



@media (max-width: 991px) {

    /* Larger dropdown container */
    .navbar-collapse {
        background: #fff;
        padding: 25px 0 !important;   
        text-align: center;           
        border-top: 1px solid #eee;
    }

    /* Center nav list */
    .navbar-nav {
        width: 100%;
        align-items: center !important; 
    }

   
    .navbar-nav .nav-item {
        width: 100%;
        padding: 12px 0;
    }

    .navbar-nav .nav-link {
        font-size: 18px !important;
        padding: 15px 0 !important;      
        width: 100%;
        text-align: center;               
        border-bottom: 1px solid #f0f0f0;
    }

    .navbar-nav .nav-item:last-child .nav-link {
        border-bottom: none;
    }
}

</style>