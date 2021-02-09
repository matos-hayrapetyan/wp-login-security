<?php


namespace WPLoginSecurity;


use WP_Error;
use WP_User;
use ZxcvbnPhp\Zxcvbn;

class Validator
{
    const LIMIT = 3;

    /**
     * @param $user
     * @param $username
     * @param $password
     * @return WP_Error|WP_User
     */
    public function validatePassword($user, $username, $password)
    {
        if (!($user instanceof WP_User)) {
            return $user;
        }

        $error = false;

        /**
         * (?!.*\s) - negative lookahead for whitespace characters
         * (?=.*[0-9]) - positive lookahead for number
         * (?=.*[a-z]) - positive lookahead for lowercase letter
         * (?=.*[A-Z]) - positive lookahead for uppercase letter
         * (?=.*[!"#$%&'()*+,\-.\/:;<=>?@[\]^_`{|}~]) - positive lookahead for special characters
         * .{16,} - 16 or more characters
         */
        $rules_met = preg_match('/^(?!.*\s)(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[!"#$%&\'()*+,\-.\/:;<=>?@[\]^_`{|}~]).{16,}$/', $password);

        if (1 !== $rules_met) {
            $error = new WP_Error();
            $error->add('invalid_password', __('<strong>Error</strong>: The password must have at least:<ul style="padding-left:15px;">
<li>1 uppercase letter</li>
<li>1 lowercase letter</li>
<li>1 number</li>
<li>1 special character from !"#$%&\'()*+,\-./:;<=>?@[\]^_`{|}~</li>
<li>Spaces, tabs and any other whitespace characters not allowed</li></ul>', 'wp-login-test'));
        }

        $zxcvbn = new Zxcvbn();
        $pass_strength = $zxcvbn->passwordStrength($password);

        /**
         * strong password is required
         */
        if ($pass_strength < 4) {
            $error = new WP_Error();
            $error->add('weak_password', __(sprintf('<strong>Error</strong>: Your password is not strong enough, it has a score of %d. Please change it to be allowed to log in again.', $pass_strength), 'wp-login-test'));
        }

        if (!$error) {
            return $user;
        }

        $this->addFailedAttempt($user->ID);

        $this->maybeSendWarningEmailToAdmin($user->ID);

        return $error;
    }

    /**
     * @param $user_id
     */
    private function addFailedAttempt($user_id)
    {
        $last_failed_attempt = get_user_meta($user_id, 'login_last_failed_attempt', true);
        $count = get_user_meta($user_id, 'login_failed_attempt_count', true);
        if (empty($count)) {
            $count = 0;
        } else {
            $count = intval($count) + 1;
        }
        if (!empty($last_failed_attempt)) {
            $time_diff = time() - intval($last_failed_attempt);
            // if 24 hours have passed, reset the counter
            if ($time_diff > 86400) {
                $count = 1;
            }
        }

        update_user_meta($user_id, 'login_last_failed_attempt', time());
        update_user_meta($user_id, 'login_failed_attempt_count', $count);
    }

    /**
     * @param int $user_id
     * @return bool
     */
    public function maybeSendWarningEmailToAdmin($user_id)
    {
        $count = (int)get_user_meta($user_id, 'login_failed_attempt_count', true);
        // send email only if 4 or more failed attempts
        if ($count <= Validator::LIMIT) {
            return false;
        }

        $user = get_userdata($user_id);
        $siteurl = get_option('siteurl');
        $to = get_option('admin_email');
        $subject = 'Login Security Warning';
        $body = __(sprintf('User %s has tried to log in with an unsuitable password too many times to your site %s', $user->display_name, $siteurl), 'wp-login-security');
        $body .= '<br /><br/>';
        $body .= sprintf(_n('The system has identified %d unsuccessful login attempt', 'The system has identified %d unsuccessful login attempts', $count, 'wp-login-security'), number_format_i18n($count));
        $headers = array('Content-Type: text/html; charset=UTF-8');

        return wp_mail($to, $subject, $body, $headers);
    }


}