<?php
require_once '../config.php';
$session = new SessionManager();
$session->sessionStart();
$token= $session->getSessionToken();
output_add_rewrite_var('token', $token);
if (!isset($_POST['token']) || !$session->verifySession($_POST['token'])) {
    $session->sessionDestroy();
    header('location:' . SITE_URL);
    exit;
}
if ( isset($_POST['submit_email']) ) {
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
        if ( !$email ) {
            $_SESSION['forgot_password_message'] = "You must be joking. Please enter your email address.";
            header('location:forgot.php');
            exit;    
        }
    if ( !$email = filter_var($email, FILTER_VALIDATE_EMAIL) ) {
        $_SESSION['forgot_password_message'] = 'Invalid email.';
        header('location:forgot.php');
        exit;    
    }
    $_SESSION['email'] = $email;
    $user = new User();
    $user_exist = $user->checkIfUserExists($email);
    if ( $user_exist === 'error' ) {
        $_SESSION['forgot_password_message'] = 'An error has occurred. The application is not available. Try later.';
        header('location:forgot.php');
        exit;                
    } elseif ( !$user_exist ) {
            $_SESSION['forgot_password_message'] = 'We could not find the email address you entered in our system.';
            header('location:forgot.php');
            exit;
        }
    if ( !$user->sendForgotPasswordCode($email) ) { //!$user->sendForgotPasswordCode($email) ) {
        $_SESSION['forgot_password_message'] = 'An error has occurred. We were unable to send e mail to the designated address.';
        header('location:forgot.php');
        exit;
    } else {
        $forgot_password_message = 'In the appropriate field, enter the code sent to you via email and enter a new password.';
    }
}
if ( isset($_POST['change_password']) ) {
    $forgot_password_code = trim(filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING));
    $new_password = trim(filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING));
    $confirm_new_password = trim(filter_input(INPUT_POST, 'confirm_new_password', FILTER_SANITIZE_STRING));
    if ( !$forgot_password_code) {
        $forgot_password_message = 'Enter the code sent to you via email.';
    }
    elseif ( !$new_password ) {
        $forgot_password_message = 'Enter your password.';
    }
    elseif ( !$confirm_new_password ) {
        $forgot_password_message = 'Confirm new password.';
    } else {
        $user = new User();
        if ( !$user->changeForgottenPassword($_SESSION['email'], $forgot_password_code, $new_password, $confirm_new_password) ) {
            $forgot_password_message = $user->getUserStatusMessage();
        } else {
            $_SESSION['login_message'] = 'The password is changed. You can sign up.';
            header('location:../index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE HTML>
<html lang="sr">
	<head>
		<title>SP Bookmarks change password</title>
		<link href="../css/bootstrap.min.css" type="text/css" rel="stylesheet" />
		<link href="../css/verify.css" type="text/css" rel="stylesheet" />
        <link href="../css/recovery.css" type="text/css" rel="stylesheet" />
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
	</head>
	<body>
		<div id="layout">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
							<div id="div_change_password">
                            <h2 class="text-center">Change password</h2>
								<p class="text-center text-success">In the appropriate field, enter the code sent to you via email and enter a new password.</p>
								<br />
                                 <?php
                                    if ( isset($forgot_password_message) ) {
                                        echo "<div id='forgot_password_message' class='alert alert-danger'>{$forgot_password_message}</div>";
                                    }
                                ?>
								<form method="post" action="change-password.php" id="form_change_password" name="form_change_password" autocomplete="off">
                                    <div class="form-group">
										<label for="code">Enter your code to change the password:</label>
										<input type="text" id="code" name="code" class="form-control"
                                        <?php
                                            if ( isset($forgot_password_code)) {
                                                echo "value='{$forgot_password_code}'";
                                                //unset($_SESSION['forgot_password_code'], $forgot_password_code);
                                            }
                                        ?>
                                         />
									</div>
									<div class="form-group">
										<label for="new_password">New Password:</label>
										<input type="password" id="new_password" name="new_password" class="form-control"
                                        <?php
                                            if ( isset($new_password) ) {
                                                echo "value='{$new_password}'";
                                            }
                                        ?>
                                        />
									</div>
                                    <div class="form-group">
										<label for="confirm_new_password">Confirm new password:</label>
										<input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" />
									</div>
									<div class="form-group">
										<input type="submit" class="btn btn-danger" id="change_password" name="change_password" value="Change password" />
									</div>
								</form>
							</div>
							
					</div>
				</div>
			</div>
		</div>
</body>
</html>
