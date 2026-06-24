<?php
// part of orsee. see orsee.org
ob_start();
$title = "admin_login_page_2fa";
include("header.php");

if ($proceed) {
    if (!isset($_SESSION['temp_expadmindata']) || !$_SESSION['temp_expadmindata']) {
        redirect("admin/admin_login.php");
        $proceed = false;
    }
}

if ($proceed) {
    $temp_admin = $_SESSION['temp_expadmindata'];
    $admin_id = $temp_admin['admin_id'];
    $admin = orsee_db_load_array("admin", $admin_id, "admin_id");

    if (isset($_REQUEST['verify_2fa'])) {
        if (!csrf__validate_request_message()) {
            redirect("admin/admin_login_2fa.php");
            $proceed = false;
        }

        if ($proceed) {
            $code = isset($_REQUEST['twofa_code']) ? trim($_REQUEST['twofa_code']) : '';
            $valid = false;
            $used_backup = false;

            // 1. Verify TOTP code
            if (strlen($code) == 6 && is_numeric($code)) {
                $valid = admin__twofa_verify($admin['twofa_secret'], $code);
            }

            // 2. Verify Backup Code
            if (!$valid && strlen($code) == 8 && is_numeric($code)) {
                $backup_codes = explode(' ', $admin['twofa_backup_codes']);
                $key = array_search($code, $backup_codes);
                if ($key !== false) {
                    $valid = true;
                    $used_backup = true;
                    // Remove the used code
                    unset($backup_codes[$key]);
                    $new_backup_codes = implode(' ', $backup_codes);
                    
                    $pars = array(':admin_id' => $admin_id, ':backup_codes' => $new_backup_codes);
                    $query = "UPDATE " . table('admin') . " SET twofa_backup_codes = :backup_codes WHERE admin_id = :admin_id";
                    or_query($query, $pars);
                }
            }

            if ($valid) {
                // Restore session
                $_SESSION['expadmindata'] = $_SESSION['temp_expadmindata'];
                unset($_SESSION['temp_expadmindata']);

                log__admin("login", "2fa_authenticated" . ($used_backup ? "_using_backup_code" : ""));
                
                $done = admin__track_successful_login($admin);

                if (isset($_SESSION['temp_requested_url']) && $_SESSION['temp_requested_url']) {
                    $url = $_SESSION['temp_requested_url'];
                    unset($_SESSION['temp_requested_url']);
                    redirect(urldecode($url));
                } else {
                    redirect("admin/index.php");
                }
                $proceed = false;
            } else {
                admin__track_unsuccessful_login($admin);
                log__admin("login_2fa_failed", "username:".$admin['adminname']);
                message('Invalid verification code or backup code. Please try again.', 'error');
                redirect("admin/admin_login_2fa.php");
                $proceed = false;
            }
        }
    }
}

if ($proceed) {
    echo '  <div class="orsee-panel orsee-login-panel">
                <div class="orsee-panel-title">Two-Factor Authentication</div>
                <div class="orsee-content" style="padding: 2rem 1.5rem;">
                    <form method="post" action="admin_login_2fa.php" class="orsee-login-form">
                        ' . csrf__field() . '
                        <div class="field">
                            <p style="margin-bottom: 1.5rem; text-align: center;">Enter the 6-digit code from your authenticator app, or an 8-digit backup code.</p>
                            <label class="label" style="text-align: center;">Security Code / Backup Code:</label>
                            <div class="control" style="text-align: center;">
                                <input class="input is-primary orsee-input" type="text" name="twofa_code" maxlength="8" placeholder="123456" autocomplete="off" autofocus style="text-align: center; font-size: 1.5rem; letter-spacing: 0.2rem; max-width: 250px; display: inline-block;">
                            </div>
                        </div>
                        <div class="orsee-form-actions orsee-login-actions" style="margin-top: 2rem;">
                            <button class="button orsee-btn" type="submit" name="verify_2fa" value="1" style="width: 100%; max-width: 250px;">Verify and Log In</button>
                        </div>
                        <div class="orsee-login-forgot" style="margin-top: 1.5rem; text-align: center;">
                            <a href="admin_login.php?logout=1" style="color: #ff3860;">Cancel and Go Back</a>
                        </div>
                    </form>
                </div>
            </div>';
}

include("footer.php");
?>
