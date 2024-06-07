<?php
session_start();
require 'vendor/autoload.php';

include('config/dbconn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $loginType = $_POST['login_type'];
  if ($loginType == 'login') {
    if (!empty($_POST["username"]) && !empty($_POST["password"])) {
      $username = $_POST["username"];
      $password = $_POST["password"];

      try {
        // Database connection should be initialized here, assumed $dbh is the PDO object
        // Check against tbluser table
        $query = "SELECT u.*, r.RoleName FROM tbluser u
                            INNER JOIN tblrole r ON u.RoleId = r.id
                            WHERE u.UserName = :username";
        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check against admin table
        $query = "SELECT * FROM admin WHERE UserName = :username";
        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        $account = $user ?: $admin;
        $accountType = $user ? 'user' : ($admin ? 'supperadmin' : null);

        if ($account) {
          if ($account['Status'] == 'locked') {
            sleep(1);
            header('Location: account-locked.php');
          } elseif (password_verify($password, $account['Password'])) {
            if (isset($account['authenticator_enabled']) && $account['authenticator_enabled'] == 1) {
              // Redirect to 2FA verification
              $_SESSION['temp_userid'] = $account['id'];
              $_SESSION['temp_username'] = $account['UserName'];
              $_SESSION['temp_role'] = $account['RoleName'];
              $_SESSION['temp_usertype'] = $accountType;
              $_SESSION['temp_secret'] = $account['TwoFASecret'];
              sleep(1);
              header('Location: 2fa.php');
              exit; // Make sure to exit after redirection
            } else {
              // Set session variables for user or admin
              $_SESSION['userid'] = $account['id'];
              $_SESSION['role'] = $account['RoleName'];
              $_SESSION['username'] = $account['Honorific'] . " " . $account['FirstName'] . " " . $account['LastName'];

              // Define role to dashboard mapping
              $roleToDashboard = [
                'ប្រធានអង្គភាព' => 'pages/admin/dashboard.php',
                'អនុប្រធានអង្គភាព' => 'pages/admin/dashboard.php',
                'ប្រធាននាយកដ្ឋាន' => 'pages/manager/dashboard.php',
                'អនុប្រធាននាយកដ្ឋាន' => 'pages/manager/dashboard.php',
                'ប្រធានការិយាល័យ' => 'pages/office_manager/dashboard.php',
                'អនុប្រធានការិយាល័យ' => 'pages/office_manager/dashboard.php',
                'supperadmin' => 'pages/supperadmin/dashboard.php'
              ];

              // Redirect to appropriate dashboard
              $role = $_SESSION['role'];
              if (isset($roleToDashboard[$role])) {
                header('Location: ' . $roleToDashboard[$role]);
              } else {
                header('Location: pages/user/dashboard.php');
              }
            }
          } else {
            $error = 'Invalid username or password';
          }
        } else {
          $error = 'Invalid username or password';
        }
      } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
      }
    } else {
      sleep(1);
      $error = 'Please enter both username and password';
    }
  } else {
    $error = "Invalid login type.";
  }
}

try {
  // Retrieve existing data if available
  $sql = "SELECT * FROM tblsystemsettings";
  $result = $dbh->query($sql);

  if ($result->rowCount() > 0) {
    // Fetch data and pre-fill the form fields
    $row = $result->fetch(PDO::FETCH_ASSOC);
    $system_name = $row["system_name"];
    // Assuming icon and cover paths are stored in the database with ../../
    $icon_path_relative = $row["icon_path"];
    $cover_path_relative = $row["cover_path"];

    // Remove ../../ from the paths
    $icon_path = str_replace('../../', '', $icon_path_relative);
    $cover_path = str_replace('../../', '', $cover_path_relative);
  } else {
    // If no data available, set default values
    $system_name = "";
    $icon_path = "assets/img/avatars/no-image.jpg";
    $cover_path = "assets/img/pages/profile-banner.png";
  }
} catch (PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="assets/" data-template="horizontal-menu-template">


<?php
$pageTitle = "ចូលប្រើប្រាស់ប្រព័ន្ធ";
include('includes/header-login-page.php');
include('includes/alert.php');
?>


  <body>
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme position-fixed w-100 shadow-none px-3 px-md-5" id="layout-navbar">
      <div class="container">
        <div class="navbar-brand app-brand demo d-xl-flex py-0 me-4">
          <a href="index.html" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">
              <img src="<?php echo htmlspecialchars($icon_path); ?>" class="avatar avat" alt="">
            </span>
            <span class="app-brand-text demo menu-text fw-bold mef2 d-xl-block d-none d-sm-none" style="font-size: 1.2rem"><?php echo htmlspecialchars($system_name); ?></span>
          </a>
        </div>
      </div>
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- Language -->
        <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class="bx bx-globe bx-sm"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-language="kh" data-text-direction="ltr">
                <span class="align-middle">
                  ភាសាខែ្មរ
                </span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-language="en" data-text-direction="ltr">
                <span class="align-middle">English</span>
              </a>
            </li>
          </ul>
        </li>
        <!-- /Language -->
        <li class="nav-item dropdown-style-switcher dropdown me-4 me-xl-0">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class="bx bx-sm"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                <span class="align-middle"><i class="bx bx-sun me-2"></i>Light</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                <span class="align-middle"><i class="bx bx-moon me-2"></i>Dark</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                <span class="align-middle"><i class="bx bx-desktop me-2"></i>System</span>
              </a>
            </li>
          </ul>
        </li>
      </ul>
    </nav>
    <!-- Content -->
    <div class="authentication-wrapper authentication-cover content">
      <div class="authentication-inner row m-0">
        <!-- Left Text -->
        <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
          <div class="w-100 d-flex justify-content-center mt-5">
            <div>
              <img src="<?php echo htmlspecialchars($cover_path); ?>" style="width: 100%;height: 70vh; object-fit: cover;" alt="">
            </div>
          </div>
        </div>
        <!-- /Left Text -->

        <!-- Login -->
        <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4 shadow-none">
          <div class="w-px-400 mx-auto">
            <!-- Logo -->
            <div class="app-brand mb-5 d-flex align-items-center justify-content-center">
              <a href="index.php" class="app-brand-link gap-2">
                <span class="app-brand-log demo">
                  <img src="<?php echo htmlspecialchars($icon_path); ?>" class="avatar avatar-xl" alt="">
                </span>
              </a>
            </div>
            <form id="formAuthentication" class="mb-3" method="POST">
              <input type="hidden" name="login_type" value="login">
              <div class="mb-3">
                <label for="email" class="form-label" data-i18n="Username">ឈ្មោះមន្ត្រី </label>
                <span class="text-danger fw-bold">*</span>
                <input type="text" class="form-control" id="email" name="username" placeholder="សូមបញ្ចូលឈ្មោះមន្ត្រី" autofocus required />
              </div>
              <div class="mb-3 form-password-toggle">
                <div class="d-flex">
                  <label class="form-label" for="password" data-i18n="Password">ពាក្យសម្ងាត់ </label>
                  <span class="text-danger fw-bold mx-1">*</span>
                </div>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>
              <button class="btn btn-primary d-grid w-100 mt-4" data-i18n="Login">ចូលប្រើប្រាស់ប្រព័ន្ធ</button>
            </form>

            <div class="divider my-4">
              <div class="divider-text" data-i18n="OR">ឬ</div>
            </div>

            <div class="d-flex justify-content-center mb-3">
              <a href="forgot-password.php" data-i18n="Forgot Password" class="btn btn-label-secondary w-100">Forgot Password</a>
            </div>

            <div class="d-flex justify-content-center">
              <a href="" class="btn btn-label-primary w-100" data-i18n="Back">ត្រឡប់ទៅកាន់ប្រព័ន្ធឌីជីថល</a>
            </div>
          </div>
        </div>
        <!-- /Login -->
      </div>
    </div>
  </body>
  <?php include('includes/scripts-login-page.php'); ?>

</html>
