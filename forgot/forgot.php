<?php
require_once '../config.php';
$session=new SessionManager();
$session->sessionStart();
$token= $session->getSessionToken();
output_add_rewrite_var('token', $token);
if (isset($_SESSION['forgot_password_message'])) {
    $forgot_password_message = $_SESSION['forgot_password_message'];
} else {
    $forgot_password_message = null;
}
?>
<!DOCTYPE HTML>
<html lang="sr">
	<head>
		<title>Bookmarks</title>
		<link href="../css/bootstrap.min.css" type="text/css" rel="stylesheet" />
		<link href="../css/recovery.css" type="text/css" rel="stylesheet" />
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
	</head>
	<body>
		<div id="layout">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-md-offset-3">
							<div id="div_email">
                            <h2 class="text-center">Forgot your password?</h2>
                            <br />
								<p class="text-center text-success">Enter your email address below and we'll send you the data to reset your password.</p>
								<br />
                                <?php
                                    if (isset($forgot_password_message)) {
                                        echo "<div id='forgot_password_message' class='alert alert-danger'>{$forgot_password_message}</div>";
                                        unset($forgot_password_message, $_SESSION['forgot_password_message']);
                                    }
                                ?>
								<form method="post" action="change-password.php" id="form_forgot" name="form_forgot" autocomplete="off">
									<div class="form-group">
										<label for="email">E-mail:</label>
										<input type="text" id="email" name="email" class="form-control" />
									</div>
									<div class="form-group">
										<input type="submit" class="btn btn-danger center-block" id="submit_email" name="submit_email" value="Send email" />
									</div>
								</form>
							</div>
					</div>
				</div>
			</div>
		</div>
</body>
</html>