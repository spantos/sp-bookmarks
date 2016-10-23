<?php
require_once 'config.php';
$session = new SessionManager();
$session->sessionStart();
if (!isset($_SESSION['captcha-token'])) {
    $session->sessionDestroy();
    header('location:index.php');
    exit;
}
if (!$session->verifySession($_SESSION['captcha-token'])) {
    $session->sessionDestroy();
    header('location:index.php');
    exit;
}
$token= $session->getSessionToken();
?>
<!DOCTYPE HTML>
<html lang="sr">
    <head>
        <title>Bookmarks</title>
        <link href="css/bootstrap.min.css" type="text/css" rel="stylesheet" />
        <link href="css/logincaptcha.css" type="text/css" rel="stylesheet" />
        <meta charset="utf8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
    </head>
    <body>
        <div class="container">
            <div class="row">
            <div id="div_login" class="col-centered" >
                <form method="post" action="login.php" id="form_login" name="form_login" autocomplete="off">
                <input type="hidden" name="token" value="<?php echo $token ?>" />
                    <div class="form-group">
                        <label for="login_email">E-mail:</label>
                        <input type="text" id="login_email" name="login_email" class="form-control"
                            <?php
                            if (isset($_SESSION['login_email'])) {
                                echo 'value="' . $_SESSION['login_email'] . '"';
                                unset($_SESSION['login_email']);
                            }
                            ?>
                        />
                    </div>
                    <div class="form-group">
                        <label for="login_password">Password:</label>
                        <input type="password" autocomplete="off" id="login_password" name="login_password" class="form-control" />
                    <div class="captcha">
                        <img id="captcha" src="library/securimage/securimage_show.php" alt="CAPTCHA Image" width="130px" height="auto"/>
                        <input type="text" name="captcha_code" size="10" maxlength="6" />
                        <a type="button" onclick="document.getElementById('captcha').src = 'library/securimage/securimage_show.php?' + Math.random(); return false" class='glyphicon glyphicon-repeat reload-image'></a>
                    </div>
                        <input type="submit" class="btn btn-primary" id="submit_login" name="submit_login" value="Log In" />
                    </div>
                </form>
            <div id="login_message">
                <span>
                    <?php
                    if (isset($_SESSION['login_message'])) {
                        echo $_SESSION['login_message'];
                        unset($_SESSION['login_message']);
                    }
                    ?>
                </span>
            </div>
        </div>
  </div>
  </div>
  </body>
  </html>