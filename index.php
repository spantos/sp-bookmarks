<?php
require_once 'config.php';
$session = new SessionManager();
$session->sessionStart();
$token= $session->getSessionToken();
$_SESSION['token'] = $token;
output_add_rewrite_var('token', $token);
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title>Bookmarks</title>
        <link href="css/bootstrap.min.css" type="text/css" rel="stylesheet" />
        <link href="css/login.css" type="text/css" rel="stylesheet" />
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
    </head>
 <body>
    <div>
        <div id="div_login" class="container-fluid">
            <form class="form-inline" method="post" action="login.php" id="form_login" name="form_login" autocomplete="off">
                <div class="form-group">
                    <label for="login_email">E-mail:</label>
                    <input type="text" id="login_email" name="login_email" class="form-control"
                        <?php
                        if (isset($_SESSION['login_email'])) {
                            echo "value='" . $_SESSION['login_email'] . "'";
                            unset($_SESSION['login_email']);
                        }
                        ?>
                    />
                </div>
                <div class="form-group">
                <label for="login_password">Password:</label>
                    <input type="password" autocomplete="off" id="login_password" name="login_password" class="form-control" />
                    <input type="submit" class="btn btn-primary" id="submit_login" name="submit_login" value="Log In" />
                </div>
            </form>
            <?php echo "<a href='" . SITE_URL . "/forgot/forgot.php'>Forgot your password?</a>"; ?>
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
    <?php if (REGISTER_FORM) : ?>
<div class="container">
            <div class="row">
            <div class="col-centered" id="div_reg"> <!-- id="div_reg" -->
                <form method="POST" action="register.php" id="reg" name="reg" autocomplete="off">
                    <label for="reg_email">E-mail:</label>
                    <br />
                    <input class="form-control" type="text" id="reg_email" name="reg_email"
                        <?php
                        if (isset($_SESSION['reg_email'])) {
                            echo "value='" . $_SESSION['reg_email'] . "'";
                            unset($_SESSION['reg_email']);
                        }
                        ?>
                    />
                    <br />
                    <label for="reg_password">Password:</label>
                    <br />
                    <input class="form-control" type="password" autocomplete="off" id="reg_password" name="reg_password"
                        <?php
                        if (isset($_SESSION['reg_password'])) {
                            echo "value='" . $_SESSION['reg_password'] . "'";
                            unset($_SESSION['reg_password']);
                        }
                        ?>
                    />
                    <br />
                    <label for="reg_password2">Repeat password</label>
                    <br />
                    <input class="form-control" type="password" autocomplete="off" id="reg_password2" name="reg_password2"
                        <?php
                        if (isset($_SESSION['reg_password2'])) {
                            echo "value='" . $_SESSION['reg_password2'] . "'";
                            unset($_SESSION['reg_password2']);
                        }
                        ?>
                    />
                    <br />
                    <input type="checkbox" id="show_password" name="show_password" />
                    <span>Show password.</span>
                    <br /><br />
                    <input class="btn btn-primary" type="submit" value="Register" name="reg_submit" />
                    <div id="register_message">
                        <span>
                            <?php
                            if (isset($_SESSION['register_message'])) {
                                echo $_SESSION['register_message'];
                                unset($_SESSION['register_message']);
                            }
                            ?>
                        </span>
                    </div>
                </form>
            </div>
            </div>
    </div> <!-- Container -->
<?php
endif;
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
<script src="js/login.js"></script>
</body>
</html>
