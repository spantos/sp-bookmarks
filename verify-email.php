<?php
require_once 'config.php';
$verify_token=filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
if (!$verify_token) {
    header('location:' . SITE_URL);
    exit();
}
$session=new SessionManager();
$session->sessionStart();
$token= $session->getSessionToken();
output_add_rewrite_var('token', $token);
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title>Bookmarks</title>
        <link href="css/bootstrap.min.css" type="text/css" rel="stylesheet" />
        <link href="css/verify.css" type="text/css" rel="stylesheet" />
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
    </head>
    <body>
        <div id="layout">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 col-md-offset-3">
                        <?php $verify_email=new EmailVerification();
                        if ($verify_email->verifyEmailAddress($verify_token)) :
                        ?>
                            <div id="div_login">
                                <p class="text-center text-success">You have successfully verified your account.</p>
                                <br />
                                <form method="post" action="login.php" id="form_login" name="form_login" autocomplete="off">
                                    <div class="form-group">
                                        <label for="login_email">E-mail:</label>
                                        <input type="text" id="login_email" name="login_email" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <label for="login_password">Password:</label>
                                        <input type="password" autocomplete="off" id="login_password" name="login_password" class="form-control" />
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" class="btn btn-primary" id="submit_login" name="submit_login" value="Log In" />
                                    </div>
                                    <?php echo "<a href='" . SITE_URL . "/forgot/forgot.php'>Forgot your password?</a>" ?>
                                </form>
                                <div id="login_message"></div>
                            </div>
                        <?php
                        else :
                            $_SESSION['register_message']="You are not a registered user or you have already verified your account.";
                            header('location:' . SITE_URL);
                        endif;
                            ?>
                    </div>
                </div>
            </div>
        </div>
<?php
    require_once 'footer.php';
?>