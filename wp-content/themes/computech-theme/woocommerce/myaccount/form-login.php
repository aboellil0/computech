<?php
/**
 * Custom Computech login/register form.
 *
 * @package Computech
 * @version 7.0.1
 */

defined('ABSPATH') || exit;

$registration_enabled = 'yes' === get_option('woocommerce_enable_myaccount_registration');
$is_register_first = false;

if (is_user_logged_in()) {
    wp_safe_redirect(function_exists('computech_account_url') ? computech_account_url() : wc_get_page_permalink('myaccount'));
    exit;
}

do_action('woocommerce_before_customer_login_form');
?>

<div class="ct-auth-shell <?php echo esc_attr($is_register_first ? 'ct-register-first' : 'ct-login-first'); ?>">
    <div class="ct-auth-panel ct-auth-intro">
        <span class="ct-auth-badge">Computech Secure Account</span>
        <h2><?php echo esc_html($is_register_first ? 'ابدأ حسابك في كمبيوتك' : 'ادخل لحسابك في كمبيوتك'); ?></h2>
        <p>الحساب يساعدك تتابع الطلبات، تحفظ بياناتك، وتدير مشترياتك من مكان واحد بتجربة مناسبة لتصميم الموقع.</p>
        <div class="ct-auth-benefits">
            <span>◇ متابعة الطلبات</span>
            <span>◇ حفظ بيانات الشحن</span>
            <span>◇ إدارة الحساب</span>
        </div>
    </div>

    <div class="ct-auth-panel ct-auth-card ct-auth-login-card">
        <div class="ct-auth-card-head">
            <span>01</span>
            <div>
                <h2><?php esc_html_e('Login', 'woocommerce'); ?></h2>
                <p>لديك حساب بالفعل؟ ادخل من هنا.</p>
            </div>
        </div>

        <form class="woocommerce-form woocommerce-form-login login ct-auth-form" method="post">
            <?php do_action('woocommerce_login_form_start'); ?>

            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="username"><?php esc_html_e('Username or email address', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" placeholder="name@example.com" /><?php // @codingStandardsIgnoreLine ?>
            </p>
            <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                <label for="password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                <input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" placeholder="••••••••" />
            </p>

            <?php do_action('woocommerce_login_form'); ?>

            <div class="ct-auth-form-footer">
                <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme ct-remember">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" /> <span><?php esc_html_e('Remember me', 'woocommerce'); ?></span>
                </label>
                <a class="ct-lost-password" href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Lost your password?', 'woocommerce'); ?></a>
            </div>

            <p class="form-row ct-auth-submit-row">
                <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
                <button type="submit" class="woocommerce-button button woocommerce-form-login__submit ct-auth-submit" name="login" value="<?php esc_attr_e('Login', 'woocommerce'); ?>"><?php esc_html_e('Login', 'woocommerce'); ?></button>
            </p>

            <?php do_action('woocommerce_login_form_end'); ?>
        </form>
    </div>

    <?php if ($registration_enabled) : ?>
        <div class="ct-auth-panel ct-auth-card ct-auth-register-card">
            <div class="ct-auth-card-head">
                <span>02</span>
                <div>
                    <h2><?php esc_html_e('Register', 'woocommerce'); ?></h2>
                    <p>ليس لديك حساب؟ أنشئ حساب جديد بسرعة.</p>
                </div>
            </div>

            <form method="post" class="woocommerce-form woocommerce-form-register register ct-auth-form" <?php do_action('woocommerce_register_form_tag'); ?>>
                <?php do_action('woocommerce_register_form_start'); ?>

                <?php if ('no' === get_option('woocommerce_registration_generate_username')) : ?>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="reg_username"><?php esc_html_e('Username', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" required aria-required="true" placeholder="computech_user" /><?php // @codingStandardsIgnoreLine ?>
                    </p>
                <?php endif; ?>

                <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                    <label for="reg_email"><?php esc_html_e('Email address', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                    <input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" required aria-required="true" placeholder="name@example.com" /><?php // @codingStandardsIgnoreLine ?>
                </p>

                <?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="reg_password"><?php esc_html_e('Password', 'woocommerce'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
                        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" placeholder="••••••••" />
                    </p>
                <?php else : ?>
                    <p class="ct-auth-note"><?php esc_html_e('A link to set a new password will be sent to your email address.', 'woocommerce'); ?></p>
                <?php endif; ?>

                <?php do_action('woocommerce_register_form'); ?>

                <p class="woocommerce-form-row form-row ct-auth-submit-row">
                    <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
                    <button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit ct-auth-submit" name="register" value="<?php esc_attr_e('Register', 'woocommerce'); ?>"><?php esc_html_e('Register', 'woocommerce'); ?></button>
                </p>

                <?php do_action('woocommerce_register_form_end'); ?>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php do_action('woocommerce_after_customer_login_form'); ?>
