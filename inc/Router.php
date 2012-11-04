<?php
/**
 * A wrapper around the rewrite api.
 *
 * @since       1.0
 * @author      Christopher Davis <chris@pmg.co>
 * @license     GPLv2
 * @copyright   Performance Media Group 2012
 * @package     PMGCore
 */

namespace PMG\Core;

class Router
{
    /**
     * Name of the parent project.
     *
     * @since   1.0
     * @access  private
     * @var     string
     */
    private $proj;

    /**
     * The rewrite rules to add.
     *
     * @since   1.0
     * @access  private
     * @var     array
     */
    private $rules = array();

    /**
     * Endpoints to add.
     *
     * @since   1.0
     * @access  private
     * @var     array
     */
    private $ep = array();

    /**
     * Query vars to add.
     *
     * @since   1.0
     * @access  private
     * @var     array
     */
    private $vars = array();

    /**
     * Query vars to catch.
     *
     * @since   1.0
     * @access  private
     * @var     array
     */
    private $catches = array();

    /**
     * Constructor.  Just adds a few actions and sets the name of the parent
     * project
     *
     * @since   1.0
     * @access  public
     * @param   string $proj The parent project name.  Useds as as prefix.
     * @return  void
     */
    public function __construct($proj)
    {
        $this->proj = $proj;
        add_action('init', array($this, 'add_rules'), 20);
        add_action('init', array($this, 'add_endpoints'), 20);
        add_action('template_redirect', array($this, 'maybe_catch'), 11);
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_filter('request', array($this, 'set_endpoints'));
    }

    /********** API **********/

    /**
     * Add a rewrite rule.
     *
     * @since   1.0
     * @access  public
     * @param   string $regex The regex.
     * @param   string $rewrite The rewrite URL
     * @param   string $prio (optional) Defaults to 'top', which is probably 
     *          what you want.
     * @return  void
     */
    public function add_rule($regex, $rewrite, $prio='top')
    {
        $this->rules[$regex] = array(
            'rewrite'   => $rewrite,
            'prio'      => $prio,
        );
    }

    /**
     * Add a rewrite endpoint.
     *
     * @since   1.0
     * @access  public
     * @param   string $ep The endpoint name
     * @param   int $mask (optional) The EP mask to use.  Defaults to EP_ALL
     * @return  void
     */
    public function add_endpoint($ep, $mask=EP_ALL)
    {
        $this->ep[$ep] = $mask;
    }

    /**
     * Register a query var. This does not prefix query variables for you. Make
     * sure you use a unique name.
     *
     * @since   1.0
     * @access  public
     * @param   string $var The variable to add.
     * @return  void
     */
    public function add_var($var)
    {
        if(is_array($var))
            $this->vars = array_merge($this->vars, $var);
        else
            $this->vars[] = $var;
    }

    /**
     * Catch a query variable.  Specify a query variable to look for. If that
     * query var is found on the `template_redirect` hook, $callable will
     * be called. If $exit is set to true, we'll call `do_action('shutdown')
     * then exit
     *
     * @since   1.0
     * @access  public
     * @param   string $var The query var to catch.
     * @param   mixed $callable Anything where is_callable($callable) == true
     * @param   bool $exit (optional) Whether or not to exit
     * @return  bool True on success (is_callable passed), false on failure
     */
    public function catch_var($var, $callable, $exit=true)
    {
        if(!is_callable($callable))
            return false;

        $this->catches[$var] = array(
            'callable'  => $callable,
            'exit'      => $exit,
        );

        return true;
    }

    /**
     * If you don't regex you can use this method to add your routes.
     *
     * Use the tags <int> and <str> to specify routes. Each tag must have a
     * variable name as well. The format is always <(int|str):var_name>
     *
     * Eg.
     *      $r->add_route('works/<int:some_var>/<str:some_str>');
     *
     * @since   1.0
     * @access  public
     * @param   string $route See above, the route to add.
     * @return  void
     */
    public function add_route($route)
    {
        $route = trim($route, '^/');

        $vars = array();
        $count = 1;
        $regex = preg_replace_callback(
            '#\<(?P<type>int|str):(?P<var>[a-zA-Z0-9_-]+)\>#u',
            function($m) use(&$vars, &$count) {
                $vars[$m['var']] = '$matches[' . $count .']';
                $count++;
                return 'int' == $m['type'] ? '(\d+)' : '([^/]+)';
            }, 
            $route
        );

        $rewrite = http_build_query($vars);

        $this->add_rule('^' . $regex . '/?$', 'index.php?' . urldecode($rewrite));
        $this->add_var(array_keys($vars));
    }

    /********** Hooks **********/

    /**
     * Hooked into `init`.  Adds rewrite rules.
     *
     * @since   1.0
     * @access  public
     * @uses    add_rewrite_rule
     * @return  void
     */
    public function add_rules()
    {
        foreach($this->rules as $regex => $rule)
        {
            add_rewrite_rule($regex, $rule['rewrite'], $rule['prio']);
        }
    }

    /**
     * Add the rewrite endpoints.
     *
     * @since   1.0
     * @access  public
     * @uses    add_rewrite_endpoint
     * @return  void
     */
    public function add_endpoints()
    {
        foreach($this->ep as $ep => $mask)
        {
            add_rewrite_endpoint($ep, $mask);
        }
    }

    /**
     * Catch rewrite vars on `template_redirect`.
     *
     * @todo    Figure out a way to do this that's not just a foreach loop
     *
     * @since   1.0
     * @access  public
     * @uses    get_query_var
     * @return  void
     */
    public function maybe_catch()
    {
        if(!$this->catches)
            return;

        foreach($this->catches as $var => $catch)
        {
            if($v = get_query_var($var))
            {
                do_action('pmgcore_before_catch', $var);
                call_user_func($catch['callable'], $v);
                do_action('pmgcore_after_catch', $var);

                if($cache['exit'])
                {
                    do_action('shutdown');
                    exit;
                }
            }
        }
    }

    /**
     * Add our custom query variables.
     *
     * @since   1.0
     * @access  public
     * @return  array
     */
    public function add_query_vars($vars)
    {
        return array_merge($vars, $this->vars);
    }

    /**
     * Hooked into request.  Makes sure our endpoint have a value even if the
     * request comes in the form some-thing/{endpoint}/?.
     *
     * @since   1.0
     * @access  public
     * @return  array
     */
    public function set_endpoints($vars)
    {
        if(is_admin())
            return $vars;

        foreach($this->ep as $ep => $mask)
        {
            if(isset($vars[$ep]) && !$vars[$ep])
                $vars[$ep] = 1;
        }

        return $vars;
    }
}
