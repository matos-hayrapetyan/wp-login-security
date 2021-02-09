<?php
/**
 * Class ValidatorTest
 *
 * @package Wp_Login_Security
 */

/**
 * Sample test case.
 */
class ValidatorTest extends WP_UnitTestCase
{

    /**
     * A single example test.
     */
    public function testMaybeSendWarningEmailToAdmin()
    {
        $uid = wp_create_user('test_user1', '1234', 'test@wp.loc');
        if (is_wp_error($uid))
            $this->fail('could not create test_user1');

        $validator = new \WPLoginSecurity\Validator();

        $this->assertFalse($validator->maybeSendWarningEmailToAdmin($uid));

        update_user_meta($uid, 'login_last_failed_attempt', time());
        update_user_meta($uid, 'login_failed_attempt_count', 4);

        $this->assertTrue($validator->maybeSendWarningEmailToAdmin($uid));

        wp_delete_user($uid);
    }
}
