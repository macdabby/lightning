<?php

namespace Lightning\View;

use Exception;
use Lightning\Model\Blacklist;
use Lightning\Model\Blog;
use Lightning\Tools\Configuration;
use Lightning\Tools\Language;
use Lightning\Tools\Messenger;
use Lightning\Tools\Navigation;
use Lightning\Tools\Output;
use Lightning\Tools\Request;
use Lightning\Tools\Session;
use Lightning\Tools\Template;
use Lightning\Model\Page as PageModel;
use Lightning\Model\Tracker;

/**
 * The basic html page handler.
 *
 * @package Lightning\View
 * @todo: Should be abstract
 */
class PageOverridable {

    const MODULE = null;

    /**
     * The template file.
     *
     * @var string|array
     */
    protected $template;

    /**
     * Whether to ignore missing or invalid tokens on post requests.
     *
     * @var boolean
     */
    protected $ignoreToken = false;

    /**
     * The current highlighted nav item.
     *
     * @var string
     */
    protected $nav = '';

    /**
     * A list of properties to be used as parameters.
     *
     * @var array
     */
    protected $params = [];

    /**
     * A template for the content within the page template.
     *
     * @var string|array
     */
    protected $page;

    /**
     * Whether to display the right column.
     *
     * Passed to, and depends on template.
     *
     * @var boolean
     */
    protected $rightColumn = true;

    /**
     * Whether to allow the page to use the full page width (true) or
     * whether it should be contained within a div.column (false)
     *
     * Passed to, and depends on template.
     *
     * @var boolean
     */
    protected $fullWidth = false;

    protected $hideHeader = false;
    protected $hideMenu = false;
    protected $hideFooter = false;
    protected $share = true;

    /**
     * Which menu should be marked as 'active'.
     *
     * Passed to, and depends on template.
     *
     * @var string
     */
    protected $menuContext = '';

    /**
     * An array of meta data for the rendered page.
     *
     * @var array
     */
    protected $meta = [];

    /**
     * Run any global initialization functions.
     */
    public function __construct() {
        // Load module settings if present.
        if (!empty(static::MODULE)) {
            $this->initModule();
        }

        // Load messages and errors from the query string.
        Messenger::loadFromQuery();
        Messenger::loadFromSession();
        Tracker::loadFromSession();
        JS::add('/js/lightning.min.js');
        JS::startup('lightning.startup.init()');
        CSS::add('/css/lightning.css');
        CSS::add('/css/font-awesome.min.css');
        CSS::add('/css/site.css');
        if (!empty($this->css)) {
            CSS::add($this->css);
        }
        if (!empty($this->js)) {
            JS::add($this->js);
        }
    }

    /**
     * Prepare the output and tell the template to render.
     */
    public function output() {
        try {
            // Send globals to the template.
            $template = Template::getInstance();

            if (!empty($this->page)) {
                $template->set('content', $this->page);
            }

            // Lightning JS will handle these trackers.
            JS::set('google_analytics_id', Configuration::get('google_analytics_id'));
            JS::set('facebook_pixel_id', Configuration::get('facebook_pixel_id'));
            JS::set('google_adwords', Configuration::get('google_adwords', []));
            if (Configuration::get('debug')) {
                JS::set('debug', true);
            }

            // @deprecated
            $template->set('google_analytics_id', Configuration::get('google_analytics_id'));

            // TODO: Remove these, they should be called directly from the template.
            $template->set('errors', Messenger::getErrors());
            $template->set('messages', Messenger::getMessages());

            $template->set('site_name', Configuration::get('site.name'));
            $template->set('blog', Blog::getInstance());
            $template->set('full_width', $this->fullWidth);
            $template->set('right_column', $this->rightColumn);
            $template->set('hide_header', $this->hideHeader);
            $template->set('hide_menu', $this->hideMenu);
            $template->set('hide_footer', $this->hideFooter);
            $template->set('share', $this->share);

            // Include the site title into the page title for meta data.
            if (!empty($this->meta['title']) && $site_title = Configuration::get('meta_data.title')) {
                $this->meta['title'] .= ' | ' . $site_title;
            }

            // Load default metadata.
            $this->meta += Configuration::get('meta_data', []);
            if ($twitter = Configuration::get('social.twitter.url')) {
                $this->meta['twitter_site'] = $twitter;
                $this->meta['twitter_creator'] = $twitter;
            }
            $template->set('meta', $this->meta);

            JS::set('menu_context', $this->menuContext);
            $template->render($this->template);
        } catch (Exception $e) {
            echo 'Error rendering template: ' . $this->template . '<br>';
            throw $e;
        }
    }

    /**
     * Build a 404 page.
     */
    public function output404() {
        $this->page = 'page';
        if ($this->fullPage = PageModel::loadByUrl('404')) {
            http_response_code(404);
        } else {
            Output::http(404);
        }
    }

    /**
     * Determine if the current use has access to the page.
     */
    protected function hasAccess() {
        return false;
    }

    /**
     * Determine which handler in the page to run. This will automatically
     * determine if there is a form based on the submitted action variable.
     * If no action variable, it will call get() or post() or any other
     * rest method.
     */
    public function execute() {
        try {
            $request_type = strtolower(Request::type());

            if (!$this->hasAccess()) {
                Output::accessDenied();
            }

            // Outputs an error if this is a POST request without a valid token.
            $this->requireToken();

            // If there is a requested action.
            if ($action = Request::get('action')) {
                $method = Request::convertFunctionName($request_type, $action);
            } else {
                $method = $request_type;
            }

            if (method_exists($this, $method)) {
                if ($method != 'get') {
                    Blacklist::checkBlacklist();
                }
                $this->{$method}();
            } else {
                // TODO: show 302
                throw new Exception('Method not available');
            }
        } catch (Exception $e) {
            Output::error($e->getMessage());
        }
        $this->output();
    }

    public function requireToken() {
        if (!$this->validateToken()) {
            Output::error(Language::translate('invalid_token'));
        }
    }

    /**
     * Make sure a valid token has been received.
     *
     * @return boolean
     *   Whether the token is valid.
     */
    public function validateToken() {
        // If this is a post request, there must be a valid token.
        if (!$this->ignoreToken && strtolower(Request::type()) == 'post') {
            $token = Request::post('token', Request::TYPE_BASE64);
            return !empty($token) && $token == Session::getInstance()->getToken();
        } else {
            // This is not a POST request so it's not required.
            return true;
        }
    }

    /**
     * Redirect the page to the same current page with the current query string.
     *
     * @param array
     *   Additional query string parameters to add to the current url.
     */
    public function redirect($params = []) {
        Navigation::redirect('/' . Request::getLocation(), $params + $this->params);
    }

    public function setMeta($field, $value) {
        $this->meta[$field] = $value;
    }

    protected function initModule() {
        $settings = Configuration::get('modules.' . static::MODULE);
        $this->updateSettings($settings);
    }

    protected function updateSettings($settings) {
        if (!empty($settings['menu_context'])) {
            $this->menuContext = $settings['menu_context'];
        }
        if (!empty($settings['template'])) {
            $this->template = $settings['template'];
        }
        if (!empty($settings['meta_data'])) {
            $this->meta += $settings['meta_data'];
        }
        if (isset($settings['right_column'])) {
            $this->rightColumn = $settings['right_column'];
        }
        if (isset($settings['full_width'])) {
            $this->fullWidth = $settings['full_width'];
        }
    }
}
