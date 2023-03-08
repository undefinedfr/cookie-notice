<?php
/*
  Plugin Name: Cookie Notice
  Plugin URI: https://undefined.fr
  Description: Plugin de conformité à la réforme RGPD
  Version: 1.1.0
  Author: Undefined (RIVIERE Nicolas)
  Author URI: https://undefined.fr
 */


define('UNDFNDPLUGIN_URL', plugins_url('', __FILE__));
define('UNDFNDPLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Class CookieNotice
 */
class CookieNotice {

    /**
     * @var string Page title & Menu label
     */
    private $_pageTitle             = 'Cookie Notice';

    /**
     * @var string Settings page slug
     */
    private $_menuSlug              = 'cookie-notice';

    /**
     * @var string Default removed cookies
     */
    private $_defaultCookieNames    = [
        ['cookie_name' => 'hasConsent', 'cookie_function' => 'Ce cookie permet de conserver votre consentement concernant le dépot de cookies', 'cookie_force' => true, 'cookie_type' => 'fonctionnal'],
        ['cookie_name' => '_ga','cookie_function' => 'Cookie utilisé par Google Analytics', 'cookie_type' => 'stats'],
        ['cookie_name' => '_gat','cookie_function' => 'Cookie utilisé par Google Analytics', 'cookie_type' => 'stats'],
        ['cookie_name' => '_gid','cookie_function' => 'Cookie utilisé par Google Analytics', 'cookie_type' => 'stats']
    ];

    /**
     * @var string Default removed cookies
     */
    private $_choices    = [
        'fonctionnal' => 'Functional',
        'marketing' => 'Marketing',
        'settings' => 'Preferences',
        'stats' => 'Statistics',
    ];

    /**
     * @var string cookies
     */
    private $_cookiesNames          = [];

    /**
     * @var string Default banned scripts before cookie validation
     */
    private $_bannedScripts         = [];


    public function __construct(){
        add_filter( 'site_transient_update_plugins', [ $this, 'removeUpdateNotifications'] );

        if(!$this->_addPage()){
            add_action( 'admin_notices', [ $this, 'noticeNoAcf' ] );
        } else {
            register_activation_hook( __FILE__, array($this, 'undfndCookieNoticeInstall') );
            add_action( 'acf/init', [ $this, 'addAcfFields' ] );
            add_shortcode( 'cookies_list', [ $this, 'getCookiesList' ] );
            add_filter( 'plugin_action_links', array( $this, 'addSettingsLink' ), 10, 2 );
            add_action( 'wp_enqueue_scripts', [ $this, 'undfndPluginEnqueue' ] );
            add_action( 'wp_print_scripts', [ $this, 'dequeueScript' ], 100 );
            add_action( 'plugins_loaded', [$this, 'loadPluginTextDomain'] );
            add_action( 'wp_footer', [ $this, 'addCookieBarInFooter' ] );
        }
    }

    /**
     *  Hook plugin install -> add default cookies
     *
     * @return void;
     */
    public function undfndCookieNoticeInstall() {
        if($this->_defaultCookieNames){
            update_option( '_options_cookie_names', 'field_5b0c14a85afd0', 'options' );
            update_option( 'options_cookie_names', count($this->_defaultCookieNames), 'options' );
            foreach ($this->_defaultCookieNames as $k => $cookie){
                update_option('_options_cookie_names_' . $k . '_cookie_name', 'field_5b0c14ae5afd1');
                update_option('options_cookie_names_' . $k . '_cookie_name', $cookie['cookie_name']);

                update_option('_options_cookie_names_' . $k . '_cookie_function', 'field_5b0c14b15afd2');
                update_option('options_cookie_names_' . $k . '_cookie_function', !empty($cookie['cookie_function']) ? $cookie['cookie_function'] : null);

                update_option('_options_cookie_names_' . $k . '_cookie_force', 'field_56324a8491337');
                update_option('options_cookie_names_' . $k . '_cookie_force', !empty($cookie['cookie_force']) ? $cookie['cookie_force'] : null);

                update_option('_options_cookie_names_' . $k . '_cookie_type', 'field_5b2141c7b6b7d');
                update_option('options_cookie_names_' . $k . '_cookie_type', !empty($cookie['cookie_type']) ? $cookie['cookie_type'] : null);
            }
        }
    }

    public function removeUpdateNotifications( $value ) {
        unset( $value->response['cookie-notice/cookie-notice.php'] );
        return $value;
    }

    /**
     * Enqueue scripts with parameters
     *
     * @return void;
     */
    public function undfndPluginEnqueue() {
        $this->_cookiesNames = $this->_getCookiesArray();

        wp_enqueue_script( 'undfnd_cookie_notice', UNDFNDPLUGIN_URL . '/assets/dist/undfnd-cookie-notice.js' );
        wp_localize_script( 'undfnd_cookie_notice', 'cookieArgs', [
            "cookiesNames"          => array_values($this->_cookiesNames),
            "cookieDuration"        => get_field('cookie_duration', 'option'),
            "cookieDocumentClick"   => get_field('cookie_document_click', 'option'),
            "cookieForceScroll"     => get_field('cookie_force_scroll', 'option'),
            "cookieDelay"           => get_field('cookie_delay', 'option'),
        ] );
        wp_enqueue_style( 'undfnd_cookie_notice', UNDFNDPLUGIN_URL . '/assets/css/undfd-cookie-notice.css' );
    }

    /**
     * Display cookie bar
     *
     * @return void;
     */
    public function addCookieBarInFooter() {
        $template   = apply_filters('undfnd_cookie_bar_template_path', UNDFNDPLUGIN_DIR . 'views/cookie-bar.php');
        $html       = file_get_contents($template);
        $html       = str_ireplace('{% cookie-title %}', apply_filters('undfnd_label_cookie_bar_title',get_field('cookie_bar_title', 'option')), $html);
        $html       = str_ireplace('{% cookie-text %}', apply_filters('undfnd_label_cookie_bar_text',get_field('cookie_bar_text', 'option')), $html);
        $html       = str_ireplace('{% cookie-page %}', apply_filters('undfnd_label_cookie_page',get_field('cookie_page', 'option')), $html);
        $html       = str_ireplace('{% cookie-functions-names %}', get_field('cookie_functions_on_accept', 'option'), $html);
        $html       = str_ireplace('{% cookie-accept %}', apply_filters('undfnd_label_cookie_accept', __('I accept all cookies', 'undfd-cookie-notice')), $html);
        $html       = str_ireplace('{% cookie-more %}', apply_filters('undfnd_label_cookie_decline', __('Learn more or refuse', 'undfd-cookie-notice')), $html);

        $js = "";
        if (!empty($this->_bannedScripts)) {
            global $wp_scripts;
            foreach ($this->_bannedScripts as $k => $script) {
                if(array_key_exists($script['handle'], $wp_scripts->registered)) {
                    $scriptHandle = $wp_scripts->registered[$script['handle']];
                    $js .= ';' . $this->_getScriptByUrl($scriptHandle->src);
                }
            }
        }

        $html .= '<script type=\'text/javascript\'>var cookie_banned_scripts = ' . json_encode($js) . '</script>';

        echo $html;
    }

    /**
     * Error message when ACF not exists
     *
     * @return void;
     */
    public function noticeNoAcf(){
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p>Le plugin ACF est manquant pour utiliser <strong>' . $this->_pageTitle . '</strong></p>';
        echo '</div>';
    }

    /**
     * Display cookies lists
     *
     * @return string;
     */
    public function getCookiesList(){
        $template               = apply_filters('undfnd_cookie_state_template_path', UNDFNDPLUGIN_DIR . 'views/cookie-state.php');
        $cookie_state           = file_get_contents($template);
        $cookie_text_accept     = apply_filters('undfnd_label_cookie_state_text_accept', __('You have accepted all cookies', 'undfd-cookie-notice'));
        $cookie_text_refuse     = apply_filters('undfnd_label_cookie_state_text_refuse', __('You have refused all cookies', 'undfd-cookie-notice'));
        $cookie_state_text      = (!empty($_COOKIE['hasConsent']) && $_COOKIE['hasConsent'] == 'true') ? $cookie_text_accept : $cookie_text_refuse;
        $cookie_state_class     = (!empty($_COOKIE['hasConsent']) && $_COOKIE['hasConsent'] == 'true') ? 'accept' : 'refuse';
        $cookie_state           = str_ireplace('{% cookie-state-text %}', $cookie_state_text, $cookie_state);


        $template_list          = apply_filters('undfnd_cookie_list_template_path', UNDFNDPLUGIN_DIR . 'views/cookie-list.php');
        $html                   = file_get_contents($template_list);
        $list                   = "";
        $this->_cookiesNames    = $this->_getCookiesArray(true);

        $cookiesRefused = !empty($_COOKIE['unwantedCookies']) ? explode(',', $_COOKIE['unwantedCookies']) : [];

        foreach($this->_cookiesNames as $cookie){
            $list .= '<tr>';
            $list .= '<td>' . $cookie['cookie_name'] . '</td>';
            $list .= '<td>' . $cookie['cookie_function'] . '</td>';
            $list .= '<td>' . __($this->_choices[$cookie['cookie_type']], 'undfd-cookie-notice') . '</td>';
            $list .= '<td class="cookie-authorize"><input type="checkbox" class="cookie-authorize-input checkbox" id="cookie_name_' . $cookie['cookie_name'] . '" name="' . $cookie['cookie_name'] . '" ' . (!in_array($cookie['cookie_name'], $cookiesRefused) ? 'checked' : '') . ' ' . ((bool)$cookie['cookie_force'] ? 'disabled' : '') . '><label for="cookie_name_' . $cookie['cookie_name'] . '"></label></td>';
            $list .= '<tr>';
        }
        $html = str_ireplace('{% cookies-list %}', $list, $html);
        $html = str_ireplace('{% cookie-name %}', apply_filters('undfd_label_cookie_name', __('Cookie\'s Name', 'undfd-cookie-notice')), $html);
        $html = str_ireplace('{% cookie-function %}', apply_filters('undfd_label_cookie_function', __('Cookie\'s Function', 'undfd-cookie-notice')), $html);
        $html = str_ireplace('{% cookie-type %}', apply_filters('undfd_label_cookie_type', __('Type', 'undfd-cookie-notice')), $html);
        $html = str_ireplace('{% cookie-more %}', apply_filters('undfd_label_cookie_decline_list', __('Refuse all cookies', 'undfd-cookie-notice')), $html);
        $html = str_ireplace('{% cookie-accept %}', apply_filters('undfd_label_cookie_accept_list', __('Accept all cookies', 'undfd-cookie-notice')), $html);
        $html = str_ireplace('{% cookie-authorize %}', apply_filters('undfd_label_cookie_authorize', '✔'), $html);
        $html = str_ireplace('{% cookie-page %}', get_field('cookie_page', 'option'), $html);
        $html = str_ireplace('{% cookie-state %}', $cookie_state, $html);
        $html = str_ireplace('{% cookie-state-class %}', $cookie_state_class, $html);

        return $html;
    }

    /**
     * Remove specific scripts
     *
     * @return void;
     */
    public function dequeueScript($handle) {
        if(is_admin())
            return;

        $bS = get_field('cookie_banned_scripts', 'options');
        $this->_bannedScripts = apply_filters('undfd_cookie_banned_scripts', $this->_bannedScripts);
        if(!empty($bS) && is_array($bS))
            $this->_bannedScripts = array_merge($bS, $this->_bannedScripts);

        if(empty($this->_bannedScripts))
            return;

        foreach($this->_bannedScripts as $k => $script){
            wp_dequeue_script($script['handle']);
        }
    }

    /**
     * Cookie Accepted ?
     *
     * @return bool;
     */
    public function hasCookiesConsent() {
        return !empty($_COOKIE['hasConsent']) && $_COOKIE['hasConsent'] == 'true';
    }

    /**
     * Add settings link on plugin row
     *
     * @return array
     *
     */
    public function addSettingsLink( $links, $file ) {
        if ( $file === $this->_menuSlug . '/' . $this->_menuSlug . '.php' && current_user_can( 'manage_options' ) ) {
            $settings_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=' . $this->_menuSlug ), __( 'Settings', 'undfd-cookie-notice' ) );
            array_unshift( $links, $settings_link );
        }

        return $links;
    }

    /**
     * Load plugin textdomain
     *
     * @return void;
     */
    public function loadPluginTextDomain()
    {
        load_plugin_textdomain( 'undfd-cookie-notice', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Return file content from script url
     *
     * @param string $url Script Url
     *
     * @return string;
     */
    private function _getScriptByUrl($url) {
        $parse_url = wp_parse_url($url);
        $path = ABSPATH . $parse_url['path'];
        $js = file_get_contents(preg_replace('#/+#','/',$path));

        return $js;
    }


    /**
     * Return cookie's names array
     *
     * @return array;
     */
    private function _getCookiesArray($display = false) {
        $cN = get_field('cookie_names', 'options');
        if(!$display){
            foreach($cN as $key => $cookie){
                if($cookie['cookie_force']){
                    unset($cN[$key]);
                }
            }
        }
        $this->_cookiesNames = apply_filters('undfd_cookie_default_cookies_names', $cN);

        return $this->_cookiesNames;
    }

    /**
     * Add Admin Page with ACF Option Page
     *
     * @return int|bool;
     */
    private function _addPage(){
        if( function_exists('acf_add_options_page') ) {
            return $option_page = acf_add_options_page([
                'page_title' 	=> $this->_pageTitle,
                'menu_title' 	=> $this->_pageTitle,
                'menu_slug' 	=> $this->_menuSlug,
                'capability' 	=> 'manage_options',
                'icon_url'      => 'dashicons-shield',
                'redirect' 	    => false
            ]);
        }

        return false;
    }

    /**
     * Add local ACF Fields
     *
     * @return void;
     */
    public function addAcfFields(){
        if( function_exists('acf_add_local_field_group') ):
            acf_add_local_field_group(array (
                'key' => 'group_5b0835bd91f1a',
                'title' => 'RGPD',
                'fields' => array (
                    array (
                        'key' => 'field_5b0835c1adf14',
                        'label' => 'Titre du bandeau cookie',
                        'name' => 'cookie_bar_title',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array (
                        'key' => 'field_5b0835c1adf15',
                        'label' => 'Texte du bandeau cookie',
                        'name' => 'cookie_bar_text',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array (
                        'key' => 'field_5b0835e1adf16',
                        'label' => 'Page d\'informations relatives aux cookies',
                        'name' => 'cookie_page',
                        'type' => 'page_link',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array (
                        ),
                        'taxonomy' => array (
                        ),
                        'allow_null' => 0,
                        'allow_archives' => 1,
                        'multiple' => 0,
                    ),
                    array (
                        'key' => 'field_5b0835c1adf90',
                        'label' => 'Afficher la barre au bout de X secondes',
                        'name' => 'cookie_delay',
                        'type' => 'text',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => 0,
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array (
                        'key' => 'field_5b0c14a85afd0',
                        'label' => 'Nom des cookies à gérer',
                        'name' => 'cookie_names',
                        'type' => 'repeater',
                        'instructions' => '[cookies_list] dans la page Cookies retourne les cookies remplis dans cette liste',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'collapsed' => '',
                        'layout' => 'table',
                        'button_label' => '',
                        'sub_fields' => array (
                            array (
                                'key' => 'field_5b0c14ae5afd1',
                                'label' => 'Nom du cookie',
                                'name' => 'cookie_name',
                                'type' => 'text',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => array (
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'default_value' => '',
                                'placeholder' => '',
                                'prepend' => '',
                                'append' => '',
                                'maxlength' => '',
                            ),
                            array (
                                'key' => 'field_5b0c14b15afd2',
                                'label' => 'Fonction du cookie',
                                'name' => 'cookie_function',
                                'type' => 'text',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => array (
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'default_value' => '',
                                'placeholder' => '',
                                'prepend' => '',
                                'append' => '',
                                'maxlength' => '',
                            ),
                            array (
                                'key' => 'field_5b2141c7b6b7d',
                                'label' => 'Type',
                                'name' => 'cookie_type',
                                'type' => 'select',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => array (
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'choices' => $this->_choices,
                                'default_value' => array (
                                ),
                                'allow_null' => 0,
                                'multiple' => 0,
                                'ui' => 0,
                                'ajax' => 0,
                                'placeholder' => '',
                                'disabled' => 0,
                                'readonly' => 0,
                            ),
                            array (
                                'key' => 'field_56324a8491337',
                                'label' => 'Forcer ?',
                                'name' => 'cookie_force',
                                'type' => 'true_false',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => array (
                                    'width' => '6',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'default_value' => '',
                                'placeholder' => '',
                                'prepend' => '',
                                'append' => '',
                                'maxlength' => '',
                            ),
                        ),
                    ),
                    array (
                        'key' => 'field_5b0bd7af1706e',
                        'label' => 'Durée du cookie de consentement',
                        'name' => 'cookie_duration',
                        'type' => 'text',
                        'instructions' => 'Durée en jours. Maximum légal : 395 (13mois)',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => 395,
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array (
                        'key' => 'field_5b0bff18a6f03',
                        'label' => 'JS à exécuter à l\'acceptation',
                        'name' => 'cookie_functions_on_accept',
                        'type' => 'textarea',
                        'instructions' => 'JavaScript à éxécuter à l’acceptation des cookies.

                        Ce champs est ignoré si la fonction "initCookieFunctions" existe dans le code.',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'new_lines' => '',
                        'maxlength' => '',
                        'placeholder' => '',
                        'rows' => '',
                    ),
                    array (
                        'key' => 'field_5b0d4aa964346',
                        'label' => 'Scripts à bloquer avant l\'acceptation',
                        'name' => 'cookie_banned_scripts',
                        'type' => 'repeater',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'collapsed' => '',
                        'min' => 0,
                        'max' => 0,
                        'layout' => 'table',
                        'button_label' => '',
                        'sub_fields' => array (
                            array (
                                'key' => 'field_5b0d4b3564347',
                                'label' => 'Handle de la fonction',
                                'name' => 'handle',
                                'type' => 'text',
                                'instructions' => '',
                                'required' => 0,
                                'conditional_logic' => 0,
                                'wrapper' => array (
                                    'width' => '',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'default_value' => '',
                                'placeholder' => '',
                                'prepend' => '',
                                'append' => '',
                                'maxlength' => '',
                            ),
                        ),
                    ),
                    array (
                        'key' => 'field_56324a8490568',
                        'label' => 'Supprimer la barre au clic en dehors',
                        'name' => 'cookie_document_click',
                        'type' => 'true_false',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                    array (
                        'key' => 'field_56324a8490409',
                        'label' => 'Accepter le cookies au scroll ?',
                        'name' => 'cookie_force_scroll',
                        'type' => 'true_false',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array (
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    )
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'options_page',
                            'operator' => '==',
                            'value' => 'cookie-notice',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'normal',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => 1,
                'description' => '',
            ));
        endif;
    }
}

$cookieNotice = new cookieNotice();

define('COOKIE_CONSENT', $cookieNotice->hasCookiesConsent());
