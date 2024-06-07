<?php
require 'vendor/autoload.php'; // Include PHPMailer autoload file

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('config/dbconn.php');

// Check if the form is submitted for password reset request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if ($_POST['action'] == 'forgot_password') {
    // Validate email
    if (!empty($_POST["email"])) {
      $email = $_POST["email"];

      // Generate a random reset token
      $resetToken = bin2hex(random_bytes(32));

      // Check if the email exists in the database
      $query = "SELECT * FROM tbluser WHERE Email = :email";
      $stmt = $dbh->prepare($query);
      $stmt->bindParam(':email', $email);
      $stmt->execute();
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user) {
        // Store the reset token in the database
        $query = "INSERT INTO password_reset_tokens (user_id, token) VALUES (:user_id, :token)";
        $stmt = $dbh->prepare($query);
        $stmt->bindParam(':user_id', $user['id']);
        $stmt->bindParam(':token', $resetToken);
        $stmt->execute();

        // Send reset password email with the reset token
        $resetLink = 'http://localhost/test/Leaves/reset-password.php?token=' . $resetToken;

        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
          //Server settings
          $mail->isSMTP();                                            // Send using SMTP
          $mail->Host       = 'smtp.gmail.com';                       // Set the SMTP server to Gmail
          $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $mail->Username   = 'pothhchamreun@gmail.com';                       // SMTP username (your Gmail address)
          $mail->Password   = 'kyph nvwd ncpa gyzi';              // SMTP password (use your App Password)
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;          // Enable TLS encryption
          $mail->Port       = 587;                                    // TCP port to connect to

          //Recipients
          $mail->setFrom('your@gmail.com', 'NO REPLY');
          $mail->addAddress($email);                                     // Add a recipient

          // Content
          $mail->isHTML(true);                                        // Set email format to HTML
          $mail->Subject = 'Password Reset Request';
          $mail->Body    = 'Please click the following link to reset your password: ' . $resetLink;

          $mail->send();
          $msg = "ប្រព័ន្ធបានផ្ញើរលីងក្នុងការផ្លាស់ប្តូរពាក្យសម្ងាត់របស់អ្នកទៅកាន់អ៊ីមែលរួចរាល់ហើយ។";
        } catch (Exception $e) {
          sleep(1);
          $error = "មិនអាចផ្ញើរទៅកាន់អ៊ីមែលបានទេ។ សូមព្យាយាមម្តងទៀត។";
        }
      } else {
        sleep(1);
        $error = "មិនមានគណនីដែលប្រើប្រាស់អ៊ីមែលនេះទេ";
      }
    } else {
      sleep(1);
      $error = "សូមបញ្ចូលអាសយដ្ឋានអ៊ីមែល";
    }
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
<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr" data-theme="theme-default"
    data-assets-path="assets/" data-template="horizontal-menu-template">

<?php
$pageTitle = "ភ្លេចពាក្យសម្ងាត់";
include('includes/header-login-page.php');
include('includes/alert.php');
?>


<body>
    <div class="authentication-wrapper authentication-cover">
        <div class="authentication-inner row m-0">

            <!-- /Left Text -->
            <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
                <div class="w-100 d-flex justify-content-center">
                    <img src="../../assets/img/illustrations/girl-unlock-password-light.png" class="img-fluid"
                        alt="Login image" width="600" data-app-dark-img="illustrations/girl-unlock-password-dark.png"
                        data-app-light-img="illustrations/girl-unlock-password-light.png">
                </div>
            </div>
            <!-- /Left Text -->

            <!-- Forgot Password -->
            <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg p-sm-5 p-4">
                <div class="w-px-400 mx-auto">
                    <!-- Logo -->
                    <div class="app-brand mb-5 d-flex align-items-center justify-content-center">
                        <a href="index.php" class="app-brand-link gap-2">
                            <span class="app-brand-log demo">
                                <img src="<?php echo htmlspecialchars($icon_path); ?>" class="avatar avatar-xl" alt="">
                            </span>
                        </a>
                    </div>
                    <!-- Forgot Password Form -->
                    <h4 class="mb-3 mx-0 mef2" data-i18n="Forgot Password">Forgot Password? 🔒</h4>
                    <form id="formAuthentication" class="mb-3" method="POST">
                        <input type="hidden" name="action" value="forgot_password">
                        <div class="mb-3">
                            <label for="email" class="form-label" data-i18n="Email">អ៊ីមែល</label>
                            <span class="text-danger fw-bold">*</span>
                            <input type="text" class="form-control" id="email" name="email"
                                placeholder="សូមបញ្ចូលអ៊ីមែល" autofocus required />
                        </div>
                        <button type="submit" data-i18n="Sent To Email" class="btn btn-primary d-grid w-100 mt-4">
                            Sent To Email
                        </button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="index.php">
                            <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm me-0"></i>
                            <small data-i18n="Back to login">Back to login</small>
                        </a>
                    </div>
                    <!-- /Forgot Password Form -->
                </div>
            </div>
            <!-- /Forgot Password -->
        </div>
    </div>

</body>
<?php include('includes/scripts-login-page.php'); ?>

</html>