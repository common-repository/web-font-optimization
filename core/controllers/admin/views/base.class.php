<?php
namespace O10n;

/**
 * Admin View Base Controller
 *
 * @package    optimization
 * @subpackage optimization/controllers/admin
 * @author     Optimization.Team <info@optimization.team>
 */
if (!defined('ABSPATH')) {
    exit;
}

class AdminViewBase extends Controller implements AdminView_Controller_Interface
{
    protected $active_tab; // active sub tab

    // related module
    protected $module_key;
    public $module; // module controller

    // tab menu
    protected $wpo_tabs = array(
        'intro' => array(
            'title' => '<span class="dashicons dashicons-admin-home"></span>',
            'title_attr' => 'Intro'
        ),
        'settings' => array(
            'title' => 'JSON Settings',
            'title_attr' => 'JSON configuration profile editor',
            'pagekey' => 'settings'
        )
    );

    // tab menu
    protected $wpo_module_tabs = array(
        'html' => array(
            'title' => 'HTML',
            'title_attr' => 'HTML Optimization'
        ),
        'css' => array(
            'title' => 'CSS',
            'title_attr' => 'CSS Optimization'
        ),
        'javascript' => array(
            'title' => 'Javascript',
            'title_attr' => 'Javascript Optimization'
        ),
        'pwa' => array(
            'title' => 'PWA / Service Worker',
            'title_attr' => 'Progressive Web App Optimization'
        ),
        'fonts' => array(
            'title' => 'Fonts',
            'title_attr' => 'Webfonts Optimization'
        ),
        'http2' => array(
            'title' => 'HTTP/2',
            'title_attr' => 'HTTP/2 Optimization'
        ),
        'security' => array(
            'title' => 'Security',
            'title_attr' => 'Security Optimization'
        )
    );

    //$modules = array('html','css','javascript','pwa','webfonts','http2','security');

    /**
     * Load controller
     *
     * @param  Core       $Core   Core controller instance.
     * @param  false      $module Module parameter not used for core view controllers
     * @return Controller Controller instance.
     */
    public static function &load(Core $Core)
    {
        // instantiate controller
        return parent::construct($Core, array(
            'AdminView',
            'AdminScreen',
            'file'
        ));
    }

    /**
     * Setup controller
     */
    protected function setup()
    {
        if (empty(static::$view_key)) {
            throw new Exception('View controller did not define a reference key.', 'admin');
        }

        // view is part of module
        if (!empty($this->module_key)) {
            $this->module = & $this->core->modules($this->module_key);
        }

        $multiple_installed = (count($this->core->modules()) > 1);

        foreach ($this->wpo_module_tabs as $module => $settings) {
            if ($this->core->module_loaded($module)) {
                $settings['base'] = ($multiple_installed) ? 'admin.php' : $this->core->modules($module)->admin_base();
                $settings['pagekey'] = $module;
            } else {
                $settings['href'] = add_query_arg(array('s' => 'o10n', 'tab' => 'search', 'type' => 'author'), admin_url('plugin-install.php'));
            }
            $this->wpo_tabs[$module] = $settings;
        }
    }

    /**
     * Setup view
     */
    public function setup_view()
    {
    }

    /**
     * Return view key
     *
     * @return string key
     */
    public static function view_key()
    {
        return static::$view_key;
    }

    /**
     * Return admin base
     *
     * @return string Admin base
     */
    public function admin_base()
    {

        // WPO plugin
        if (!empty($this->module_key)) {
            return $this->core->modules($this->module_key)->admin_base();
        } else {
            return 'admin.php';
        }
    }

    /**
     * Return navigation tabs for view
     *
     * @return array Navigation tabs
     */
    public function tabs()
    {

        // WPO plugin
        if (!empty($this->module_key)) {
            return $this->core->modules($this->module_key)->admin_tabs();
        } else {
            return $this->wpo_tabs;
        }
    }

    /**
     * Return active navigation tab
     *
     * @return array Navigation tabs
     */
    public function active_tab()
    {

        // WPO plugin
        // verify plugin origin
        if (!isset($_GET['page']) || strpos($_GET['page'], 'o10n') !== 0) {
            return array(false, false);
        }

        // WPO plugin
        if (empty($this->module_key)) {

            // extract tab from page
            $base = 'o10n';
            $active_tab = (isset($_GET['tab']))? $_GET['tab'] : false;
            $active_subtab = (isset($_GET['subtab']))? $_GET['subtab'] : false;
        } else {
            $base = 'o10n-' . $this->module_key;
            $active_tab = (isset($_GET['tab']))? $_GET['tab'] : 'intro';
            $active_subtab = (isset($_GET['subtab']))? $_GET['subtab'] : false;
        }

        return array($base, $active_tab, $active_subtab);
    }

    /**
     * Return view template
     */
    public function template($view_key = false)
    {
        if (!$view_key) {
            $view_key = static::$view_key;
        }

        // template path
        $template_path = 'admin/'.$view_key.'.inc.php';

        // module template
        if ($this->module && file_exists($this->module->dir_path() . $template_path)) {
            return $this->module->dir_path() . $template_path;
        }
        $template = O10N_CORE_PATH . $template_path;
        if (file_exists($template)) {
            return $template;
        }

        throw new Exception('View template does not exist: '.$this->file->safe_path($template), 'admin');
    }

    /**
     * Return header template
     */
    public function header_template()
    {
        // template path
        $template_path = 'admin/header.inc.php';

        // WPO parent plugin
        if (defined('O10N_WPO_VERSION')) {
            return O10N_CORE_PATH . $template_path;
        } else {

            // module template
            if ($this->module && file_exists($this->module->dir_path() . $template_path)) {
                return $this->module->dir_path() . $template_path;
            }

            $template = O10N_CORE_PATH . $template_path;
            if (file_exists($template)) {
                return $template;
            }
        }
        
        throw new Exception('Header template does not exist: '.$this->file->safe_path($template), 'admin');
    }

    /**
     * Return footer template
     */
    public function footer_template()
    {
        // template path
        $template_path = 'admin/footer.inc.php';

        // WPO parent plugin
        if (defined('O10N_WPO_VERSION')) {
            return O10N_CORE_PATH . 'admin/footer.inc.php';
        } else {

            // module template
            if ($this->module && file_exists($this->module->dir_path() . $template_path)) {
                return $this->module->dir_path() . $template_path;
            }

            $template = O10N_CORE_PATH . $template_path;
            if (file_exists($template)) {
                return $template;
            }
        }
        
        throw new Exception('Footer template does not exist: '.$this->file->safe_path($template), 'admin');
    }
}

/**
 * Admin View Controller interface
 */
interface AdminView_Controller_Interface
{
    public static function load(Core $Core); // the method to instantiate the controller
    public function setup_view(); // setup the view
}
