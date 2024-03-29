<?php
/**
 * Class for settings panel
 * 
 * @package    wp-ulike
 * @author     TechnoWich 2024
 * @link       https://wpulike.com
 */

// no direct access allowed
if ( ! defined('ABSPATH') ) {
    die();
}

if ( !class_exists( 'wp_ulike_settings' ) ) {

    class wp_ulike_settings {

        private $page, $title, $menu, $admin_screen, $settings = array(), $empty = true, $notices = array();

        public function __construct( $page = 'custom_settings', $title = null, $menu = array(), $settings = array(), $args = array() ) {
            $this->page  = $page;
            $this->title = $title ? $title : __( 'Custom Settings', WP_ULIKE_SLUG );
            $this->menu  = is_array( $menu ) ? array_merge( array(
                 'parent' => 'themes.php',
                'title' => $this->title,
                'capability' => 'manage_options',
                'icon_url' => null,
                'position' => null
            ), $menu ) : false;
            $this->apply_settings( $settings );
            $this->args = array_merge( array(
                 'description' => null,
                'submit' => __( 'Save Settings', WP_ULIKE_SLUG ),
                'reset' => __( 'Reset Settings', WP_ULIKE_SLUG ),
                'tabs' => false,
                'updated' => null
            ), $args );
            add_action( 'admin_menu', array(
                 $this,
                'admin_menu'
            ) );
            add_action( 'admin_init', array(
                 $this,
                'admin_init'
            ) );

            add_action( 'activated_plugin', array(
                 $this,
                'plugin_priority'
            ) );
        }


        public function create_help_screen() {
            $current_screen     = get_current_screen();
            $this->admin_screen = WP_Screen::get( $current_screen );
            $this->admin_screen->add_help_tab( array(
                 'title' => __( 'Similar Settings', WP_ULIKE_SLUG ),
                'id' => 'overview_tab',
                'content' => '<p>' . __( 'WP ULike plugin allows to integrate a beautiful Ajax Like Button into your wordPress website to allow your visitors to like and unlike pages, posts, comments AND buddypress activities. Its very simple to use and supports many options.', WP_ULIKE_SLUG ) . '</p>' . '<p>' . '<strong>' . __( 'Logging Method', WP_ULIKE_SLUG ) . ' : </strong></p>' . '<ul>' . '<li>' . __( 'If you select <strong>"Do Not Log"</strong> method: Any data logs can\'t save, There is no limitation in the like/dislike, unlike/undislike capacity do not work', WP_ULIKE_SLUG ) . '</li>' . '<li>' . __( 'If you select <strong>"Logged By Cookie"</strong> method: Any data logs can\'t save, The like/dislike condition will be limited by SetCookie, unlike/undislike capacity do not work', WP_ULIKE_SLUG ) . '</li>' . '<li>' . __( 'If you select <strong>"Logged By IP"</strong> method: Data logs will save for all users, the convey of like/dislike condition will check by user IP', WP_ULIKE_SLUG ) . '</li>' . '<li>' . __( 'If you select <strong>"Logged By Username"</strong> method: data logs only is saved for registered users, the convey of like/dislike condition will check by username, There is no permission for guest users to unlike/undislike', WP_ULIKE_SLUG ) . '</li>
      </ul>' . '<p>' . '<strong>' . __( 'Template Variables', WP_ULIKE_SLUG ) . ' : </strong></p>' . '<ul>' . '<li>' . '<code>%START_WHILE%</code> : ' . __( 'Start the loop of logs', WP_ULIKE_SLUG ) . ' <span style="color:red">(' . __( 'required', WP_ULIKE_SLUG ) . ')</span></li>' . '<li>' . '<code>%END_WHILE%</code> : ' . __( 'End of the while loop', WP_ULIKE_SLUG ) . ' <span style="color:red">(' . __( 'required', WP_ULIKE_SLUG ) . ')</span></li>' . '<li>' . '<code>%USER_NAME%</code> : ' . __( 'Display the liker name', WP_ULIKE_SLUG ) . '</li>' . '<li>' . '<code>%USER_AVATAR%</code> : ' . __( 'Display the liker avatar (By Gravatar)', WP_ULIKE_SLUG ) . '</li>' . '<li>' . '<code>%BP_PROFILE_URL%</code> : ' . __( 'Display the BuddyPress user profile url', WP_ULIKE_SLUG ) . '</li>' . '<li>' . '<code>%UM_PROFILE_URL%</code> : ' . __( 'Display the UltimateMemebr user profile url', WP_ULIKE_SLUG ) . '</li><hr>' . '<li>' . '<code>%POST_LIKER%</code> : ' . __( 'Display the liker name', WP_ULIKE_SLUG ) . '</li>' . '<li>' . '<code>%POST_PERMALINK%</code> : ' . __( 'Display the permalink', WP_ULIKE_SLUG ) . '</li>' . '<li>' . '<code>%POST_COUNT%</code> : ' . __( 'Display the likes count number', WP_ULIKE_SLUG ) . '</li>' . '<li>' . '<code>%POST_TITLE%</code> : ' . __( 'Display the post title', WP_ULIKE_SLUG ) . '</li><hr>' . '<li>' . '<code>%COMMENT_LIKER%</code> : ' . __( 'Display the liker name', WP_ULIKE_SLUG ) . '</li>' . '<li>' . '<code>%COMMENT_PERMALINK%</code> : ' . __( 'Display the permalink', WP_ULIKE_SLUG ) . '</li>' . '<li>' . '<code>%COMMENT_AUTHOR%</code> : ' . __( 'Display the comment author name', WP_ULIKE_SLUG ) . '</li>' . '<li>' . '<code>%COMMENT_COUNT%</code> : ' . __( 'Display the likes count number', WP_ULIKE_SLUG ) . '</li>' . '</ul>',
                'callback' => false
            ) );
            $this->admin_screen->add_help_tab( array(
                 'title' => __( 'Posts', WP_ULIKE_SLUG ),
                'id' => 'posts_tab',
                'content' => '<p>' . '<strong>' . __( 'Automatic display', WP_ULIKE_SLUG ) . ' : </strong></p><ul><li>' . __( 'If you disable this option, you have to put manually this code on wordpress while loop', WP_ULIKE_SLUG ) . '<br /><code dir="ltr">&lt;?php if(function_exists(\'wp_ulike\')) wp_ulike(\'get\'); ?&gt;</code>' . '</li></ul>',

                'callback' => false
            ) );
            $this->admin_screen->add_help_tab( array(
                 'title' => __( 'Comments', WP_ULIKE_SLUG ),
                'id' => 'comments_tab',
                'content' => '<p>' . '<strong>' . __( 'Automatic display', WP_ULIKE_SLUG ) . ' : </strong></p><ul><li>' . __( 'If you disable this option, you have to put manually this code on comments text', WP_ULIKE_SLUG ) . '<br /><code dir="ltr">&lt;?php if(function_exists(\'wp_ulike_comments\')) wp_ulike_comments(\'get\'); ?&gt;</code>' . '</li></ul>',
                'callback' => false
            ) );
            $this->admin_screen->add_help_tab( array(
                 'title' => __( 'BuddyPress', WP_ULIKE_SLUG ),
                'id' => 'bp_tab',
                'content' => '<p>' . '<strong>' . __( 'Automatic display', WP_ULIKE_SLUG ) . ' : </strong></p><ul><li>' . __( 'If you disable this option, you have to put manually this code on buddypres activities content', WP_ULIKE_SLUG ) . '<br /><code dir="ltr">&lt;?php if(function_exists(\'wp_ulike_buddypress\')) wp_ulike_buddypress(\'get\'); ?&gt;</code>' . '</li></ul>',
                'callback' => false
            ) );
            $this->admin_screen->add_help_tab( array(
                 'title' => __( 'bbPress', WP_ULIKE_SLUG ),
                'id' => 'bb_tab',
                'content' => '<p>' . '<strong>' . __( 'Automatic display', WP_ULIKE_SLUG ) . ' : </strong></p><ul><li>' . __( 'If you disable this option, you have to put manually this code on buddypres activities content', WP_ULIKE_SLUG ) . '<br /><code dir="ltr">&lt;?php if(function_exists(\'wp_ulike_bbpress\')) wp_ulike_bbpress(\'get\'); ?&gt;</code>' . '</li></ul>',
                'callback' => false
            ) );
            $this->admin_screen->set_help_sidebar( '<p><strong>' . __( 'For more information:' ) . '</strong></p><p><a href="https://wordpress.org/plugins/wp-ulike/faq/" target="_blank">' . __( 'FAQ', WP_ULIKE_SLUG ) . '</a></p><p><a href="https://wordpress.org/support/plugin/wp-ulike" target="_blank">' . __( 'Support', WP_ULIKE_SLUG ) . '</a></p>' );
        }

        public function apply_settings( $settings ) {
            if ( is_array( $settings ) ) {
                foreach ( $settings as $setting => $section ) {
                    $section = array_merge( array(
                         'title' => null,
                        'description' => null,
                        'fields' => array ()
                    ), $section );
                    foreach ( $section['fields'] as $name => $field ) {
                        $field                    = array_merge( array(
                             'type'          => 'text',
                             'label'         => null,
                             'title'         => null,
                             'notice_type'   => null,
                             'icon'          => null,
                             'message'       => null,
                             'checkboxlabel' => null,
                             'description'   => null,
                             'default'       => null,
                             'sanitize'      => null,
                             'attributes'    => array(),
                             'options'       => null,
                             'action'        => null,
                             'license'       => null
                        ), $field );
                        $section['fields'][$name] = $field;
                    } //$section['fields'] as $name => $field
                    $this->settings[$setting] = $section;
                    if ( !get_option( $setting ) ) {
                        add_option( $setting, $this->get_defaults( $setting ) );
                    } //!get_option( $setting )
                } //$settings as $setting => $section
            } //is_array( $settings )
        }

        public function add_notice( $message, $type = 'info' ) {
            $this->notices[] = array(
                 'message' => $message,
                'type' => $type
            );
        }

        private function get_defaults( $setting ) {
            $defaults = array();
            foreach ( $this->settings[$setting]['fields'] as $name => $field ) {
                if ( $field['default'] !== null ) {
                    $defaults[$name] = $field['default'];
                } //$field['default'] !== null
            } //$this->settings[$setting]['fields'] as $name => $field
            return $defaults;
        }

        private function reset() {
            foreach ( $this->settings as $setting => $section ) {
                $_POST[$setting] = array_merge( $_POST[$setting], $this->get_defaults( $setting ) );
            } //$this->settings as $setting => $section
            add_settings_error( $this->page, 'settings_reset', __( 'Default settings have been reset.', WP_ULIKE_SLUG ), 'updated' );
        }

        public function admin_menu() {
            if ( $this->menu ) {
                if ( $this->menu['parent'] ) {
                    $page = add_submenu_page( $this->menu['parent'], $this->title, $this->menu['title'], $this->menu['capability'], $this->page, array(
                         $this,
                        'do_page'
                    ) );
                } //$this->menu['parent']
                else {
                    $page = add_menu_page( $this->title, $this->menu['title'], $this->menu['capability'], $this->page, array(
                         $this,
                        'do_page'
                    ), $this->menu['icon_url'], $this->menu['position'] );
                    if ( $this->title !== $this->menu['title'] ) {
                        add_submenu_page( $this->page, $this->title, $this->title, $this->menu['capability'], $this->page );
                    } //$this->title !== $this->menu['title']
                }
                add_action( 'load-' . $page, array(
                     $this,
                    'load_page'
                ) );
                add_action( 'load-' . $page, array(
                     &$this,
                    'create_help_screen'
                ) );
            } //$this->menu
        }

        public function load_page() {
            global $wp_settings_errors;
            foreach ( $this->notices as $notice ) {
                $wp_settings_errors[] = array_merge( $notice, array(
                     'setting' => $this->page,
                    'code' => $notice['type'] . '_notice'
                ) );
            } //$this->notices as $notice
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
                if ( $this->args['updated'] !== null && $notices = get_transient( 'settings_errors' ) ) {
                    delete_transient( 'settings_errors' );
                    foreach ( $notices as $i => $notice ) {
                        if ( $notice['setting'] === 'general' && $notice['code'] === 'settings_updated' ) {
                            if ( $this->args['updated'] ) {
                                $notice['message'] = (string) $this->args['updated'];
                            } //$this->args['updated']
                            else {
                                continue;
                            }
                        } //$notice['setting'] === 'general' && $notice['code'] === 'settings_updated'
                        $wp_settings_errors[] = $notice;
                    } //$notices as $i => $notice
                } //$this->args['updated'] !== null && $notices = get_transient( 'settings_errors' )
                do_action( "{$this->page}_settings_updated" );
            } //isset( $_GET['settings-updated'] ) && $_GET['settings-updated']
            add_action( 'admin_enqueue_scripts', array(
                 __CLASS__,
                'admin_enqueue_scripts'
            ) );
        }

        public static function admin_enqueue_scripts() {
            wp_enqueue_media();
        }

        public function do_page() {
?>
    <form action="options.php" method="POST" enctype="multipart/form-data" class="wrap wp-ulike">
      <h2><?php
            echo $this->title;
?></h2>
      <?php
            settings_errors();
            if ( $text = $this->args['description'] ) {
                echo wpautop( $text );
            } //$text = $this->args['description']
            do_settings_sections( $this->page );
            if ( !$this->empty ) {
                settings_fields( $this->page );
                if ( $this->args['tabs'] && count( $this->settings ) > 1 ) {
?>
            <div class="wp-ulike-settings-tabs"></div>
          <?php
                } //$this->args['tabs'] && count( $this->settings ) > 1
                submit_button( $this->args['submit'], 'large primary' );
                if ( $this->args['reset'] ) {
                    submit_button( $this->args['reset'], 'small', "{$this->page}_reset", true, array(
                         'onclick' => "return confirm('" . __( 'Do you really want to reset all these settings to their default values ?', WP_ULIKE_SLUG ) . "');"
                    ) );
                } //$this->args['reset']
            } //!$this->empty
?>
    </form>
  <?php
        }

        public function admin_init() {
            foreach ( $this->settings as $setting => $section ) {
                register_setting( $this->page, $setting, array(
                     $this,
                    'sanitize_setting'
                ) );
                add_settings_section( $setting, $section['title'], array(
                     $this,
                    'do_section'
                ), $this->page );
                if ( !empty( $section['fields'] ) ) {
                    $this->empty = false;
                    $values      = wp_ulike_get_setting( $setting );
                    foreach ( $section['fields'] as $name => $field ) {
                        $id    = $setting . '_' . $name;
                        $field = array_merge( array(
                            'id'        => $id,
                            'name'      => $setting . '[' . $name . ']',
                            'value'     => isset( $values[$name] ) ? $values[$name] : null,
                            'class'     => $id,
                            'label_for' => $field['label'] === false ? 'hidden' : $id
                        ), $field );
                        add_settings_field( $name, $field['label'], array(
                             __CLASS__,
                            'do_field'
                        ), $this->page, $setting, $field );
                        if ( $field['type'] === 'action' && is_callable( $field['action'] ) ) {
                            add_action( "wp_ajax_{$setting}_{$name}", $field['action'] );
                        }
                    }
                }
            }
            if ( isset( $_POST["{$this->page}_reset"] ) ) {
                $this->reset();
            }
        }

        public function do_section( $args ) {
            extract( $args );
            echo "<input name='{$id}[{$this->page}_setting]' type='hidden' value='{$id}' class='wp-ulike-settings-section' />";
            if ( $text = $this->settings[$id]['description'] ) {
                echo wpautop( $text );
            } //$text = $this->settings[$id]['description']
        }

        public static function do_field( $args ) {
            extract( $args );
            $attrs = "name='{$name}'";
            foreach ( $attributes as $k => $v ) {
                $k = sanitize_key( $k );
                $v = esc_attr( $v );
                $attrs .= " {$k}='{$v}'";
            } //$attributes as $k => $v
            $desc = $description ? "<p class='description'>{$description}</p>" : '';
            switch ( $type ) {
                case 'checkbox':
                    $check = checked( 1, $value, false );
                    echo "<div class='toggle-switch'><input {$attrs} id='{$id}' type='checkbox' value='1' {$check} /><label for='{$id}'>";
                    if ( $checkboxlabel ) {
                        echo " {$checkboxlabel}";
                    } //$checkboxlabel
                    echo "</label></div>";
                    if ( $desc ) {
                        echo " {$desc}";
                    } //$desc
                    break;

                case 'radio':
                    if ( !$options ) {
                        _e( 'No options defined.', WP_ULIKE_SLUG );
                    } //!$options
                    echo "<fieldset id='{$id}'>";
                    foreach ( $options as $v => $label ) {
                        $check       = checked( $v, $value, false );
                        $options[$v] = "<label><input {$attrs} class='radio wp_ulike_check_{$v}' type='radio' value='{$v}' {$check} /> {$label}</label>";
                    } //$options as $v => $label
                    echo implode( '<br />', $options );
                    echo "{$desc}</fieldset>";
                    break;

                case 'select':
                    if ( !$options ) {
                        _e( 'No options defined.', WP_ULIKE_SLUG );
                    } //!$options
                    echo "<select {$attrs} id='{$id}'>";
                    foreach ( $options as $v => $label ) {
                        if ( is_array( $label ) ) {
                            $label = $label['name'];
                        } //is_array( $label )
                        $select = selected( $v, $value, false );
                        echo "<option value='{$v}' {$select} />{$label}</option>";
                    } //$options as $v => $label
                    echo "</select>{$desc}";
                    break;

                case 'visual-select':
                    if ( !$options ) {
                        _e( 'No options defined.', WP_ULIKE_SLUG );
                    } //!$options
                    echo "<fieldset class='wp-ulike-visual-select' id='{$id}'>";
                    foreach ( $options as $v => $label ) {
                        $name        = $label['name'];
                        $symbol      = $label['symbol'];
                        $check       = checked( $v, $value, false );
                        $options[$v] = "<input {$attrs} class='wp_ulike_check_{$v}' data-image='{$symbol}' type='radio' value='{$v}' {$check} />";
                    } //$options as $v => $label
                    echo implode( '', $options );
                    echo "{$desc}</fieldset>";
                    break;

                case 'media':
                    echo "<fieldset class='wp-ulike-settings-media' id='{$id}'><input {$attrs} type='hidden' value='{$value}' />";
                    echo "<p><a class='button button-large wp-ulike-select-media' title='{$label}'>" . sprintf( __( 'Select %s', WP_ULIKE_SLUG ), $label ) . "</a> ";
                    echo "<a class='button button-small wp-ulike-remove-media' title='{$label}'>" . sprintf( __( 'Remove %s', WP_ULIKE_SLUG ), $label ) . "</a></p>";
                    if ( $value ) {
                        echo wpautop( wp_get_attachment_image( $value, 'medium' ) );
                    } //$value
                    echo "{$desc}</fieldset>";
                    break;

                case 'textarea':
                    echo "<textarea {$attrs} id='{$id}' class='large-text'>{$value}</textarea>{$desc}";
                    break;

                case 'multi':
                    if ( !$options ) {
                        _e( 'No options defined.', WP_ULIKE_SLUG );
                    } //!$options
                    echo "<fieldset id='{$id}'>";
                    foreach ( $options as $n => $label ) {
                        $a           = preg_replace( "/name\=\'(.+)\'/", "name='$1[{$n}]'", $attrs );
                        $check       = checked( 1, $value[$n], false );
                        $options[$n] = "<label><input {$a} type='checkbox' value='1' {$check} /> {$label}</label>";
                    } //$options as $n => $label
                    echo implode( '<br />', $options );
                    echo "{$desc}</fieldset>";
                    break;

                case 'action':
                    if ( !$action ) {
                        _e( 'No action defined.', WP_ULIKE_SLUG );
                    } //!$action
                    echo "<p class='wp-ulike-settings-action'><input {$attrs} id='{$id}' type='button' class='button button-large' value='{$label}' /></p>{$desc}";
                    break;

                case 'license':
                    if ( !$action ) {
                        _e( 'No license defined.', WP_ULIKE_SLUG );
                    }
                    $v = esc_attr( $value );
                    $n = wp_nonce_field( 'wp_ulike_activate_license', 'wp_ulike_activate_license' );
                    $b = __( 'Submit', WP_ULIKE_SLUG );
                    echo "<p class='wp-ulike-settings-license-activation'><input {$attrs} type='text' value='{$v}' class='regular-text license-info' /><input id='{$id}' type='button' class='button button-large' value='{$b}' />{$n}</p>{$desc}";
                    break;

                case 'notice':
                    echo sprintf( '
                    <div class="wp-ulike-settings-notice wp-ulike-notice-control wp-ulike-notice-skin-%s">
                        <div class="wp-ulike-notice-image">%s</div>
                        <div class="wp-ulike-notice-info">
                            <h3 class="wp-ulike-notice-title">%s</h3>
                            <p class="wp-ulike-notice-description">%s</p>
                        </div>
                    </div>',
                    $notice_type, $icon, $title, $message );
                    break;

                case 'color':
                    $v = esc_attr( $value );
                    echo "<input {$attrs} id='{$id}' type='text' value='{$v}' class='wp-ulike-settings-color' />{$desc}";
                    break;

                default:
                    $v = esc_attr( $value );
                    echo "<input {$attrs} id='{$id}' type='{$type}' value='{$v}' class='regular-text' />{$desc}";
                    break;
            } //$type
        }

        public function sanitize_setting( $inputs ) {
            $values = array();
            if ( !empty( $inputs["{$this->page}_setting"] ) ) {
                $setting = $inputs["{$this->page}_setting"];
                foreach ( $this->settings[$setting]['fields'] as $name => $field ) {
                    $input = array_key_exists( $name, $inputs ) ? $inputs[$name] : null;
                    if ( $field['sanitize'] ) {
                        $values[$name] = call_user_func( $field['sanitize'], $input, $name );
                    } //$field['sanitize']
                    else {
                        switch ( $field['type'] ) {
                            case 'checkbox':
                                $values[$name] = $input ? 1 : 0;
                                break;

                            case 'radio':
                            case 'select':
                                $values[$name] = sanitize_key( $input );
                                break;

                            case 'media':
                                $values[$name] = absint( $input );
                                break;

                            case 'color':
                                $values[$name] = $input;
                                break;

                            case 'textarea':
                                $text  = '';
                                $nl    = "WP-ULIKE-SETTINGS-NEW-LINE";
                                $tb    = "WP-ULIKE-SETTINGS-TABULATION";
                                $lines = explode( $nl, str_replace( "\t", $tb, str_replace( "\n", $nl, $input ) ) );
                                foreach ( $lines as $line ) {
                                    $text .= str_replace( $tb, "\t", trim( $line ) ) . "\n";
                                } //$lines as $line
                                $values[$name] = trim( $text );
                                break;

                            case 'multi':
                                if ( !$input || empty( $field['options'] ) ) {
                                    break;
                                } //!$input || empty( $field['options'] )
                                foreach ( $field['options'] as $n => $opt ) {
                                    $input[$n] = empty( $input[$n] ) ? 0 : 1;
                                } //$field['options'] as $n => $opt
                                $values[$name] = json_encode( $input );
                                break;

                            case 'action':
                                break;

                            case 'email':
                                $values[$name] = sanitize_email( $input );
                                break;

                            case 'url':
                                $values[$name] = esc_url_raw( $input );
                                break;

                            case 'number':
                                $values[$name] = floatval( $input );
                                break;

                            default:
                                $values[$name] = html_entity_decode( $input );
                                break;
                        } //$field['type']
                    }
                } //$this->settings[$setting]['fields'] as $name => $field
                return $values;
            } //!empty( $inputs["{$this->page}_setting"] )
            return $inputs;
        }

        public static function parse_multi( $result ) {
            // Check if the result was recorded as JSON, and if so, returns an array instead
            return ( is_string( $result ) && $array = json_decode( $result, true ) ) ? $array : $result;
        }

        public static function plugin_priority() {
            $wp_ulike_settings = plugin_basename( __FILE__ );
            $active_plugins    = get_option( 'active_plugins' );
            if ( $order = array_search( $wp_ulike_settings, $active_plugins ) ) {
                array_splice( $active_plugins, $order, 1 );
                array_unshift( $active_plugins, $wp_ulike_settings );
                update_option( 'active_plugins', $active_plugins );
            } //$order = array_search( $wp_ulike_settings, $active_plugins )
        }
    }

} //!class_exists( 'wp_ulike_settings' )
