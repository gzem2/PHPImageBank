<?php declare(strict_types=1);

namespace PHPImageBank\App;

/**
 * Class to route requests.
 * 
 * Application entry point uses it to resolve request to controller action.
 */
class Router
{
    private static $routes = array(); /**< array of accepted routes */
    private static $pathNotFound = null; /**< function to execute when path not found */
    private static $methodNotAllowed = null; /**< function to execute when method not allowed */

    /**
     * Add a route for method GET.
     * @param string $expression URL expression
     * @param $function array consisting of name of class and method, or closure to be executed
     */
    public static function get(string $expression, callable $function)
    {
        self::add($expression, $function, 'get');
    }

    /**
     * Add a route for method POST.
     * @param string $expression URL expression
     * @param $function array consisting of name of class and method, or closure to be executed
     */
    public static function post(string $expression, callable $function)
    {
        self::add($expression, $function, 'post');
    }

    /**
     * Add a route for method PUT.
     * @param string $expression URL expression
     * @param $function array consisting of name of class and method, or closure to be executed
     */
    public static function put(string $expression, callable $function)
    {
        self::add($expression, $function, 'put');
    }

    /**
     * Add a route for method DELETE.
     * @param string $expression URL expression
     * @param $function array consisting of name of class and method, or closure to be executed
     */
    public static function delete(string $expression, callable $function)
    {
        self::add($expression, $function, 'delete');
    }

    /**
     * Add a route with specific method
     * @param string $expression URL expression
     * @param $function array consisting of name of class and method, or closure to be executed
     * @param string $method http method
     */
    public static function add(string $expression, callable $function, string $method = 'get')
    {
        array_push(self::$routes, array(
            'expression' => $expression,
            'function' => $function,
            'method' => $method
        ));
    }

    /**
     * Function to call when path matches no routes
     * @param $function
     */
    public static function pathNotFound(callable $function)
    {
        self::$pathNotFound = $function;
    }

    /**
     * Function to call when path has a match, but no matching method
     * @param $function
     */
    public static function methodNotAllowed(callable $function)
    {
        self::$methodNotAllowed = $function;
    }

    /**
     * Redirect browser to specific path
     * @param string $path location to redirect
     */
    public static function redirect(string $path)
    {
        header('Location: '.$path);
    }

    /**
     * Check whenever path matches route expression
     * @param array $route consist of expression, controller method or closure, and http method
     * @param string $path request path
     * @param string $basepath app basepath
     */
    public static function checkRoute(array $route, string $path, string $basepath = "/")
    {
        if ($basepath != '' && $basepath != '/') {
            $route['expression'] = '(' . $basepath . ')' . $route['expression'];
        }

        $path_match_found = false;
        $route_match_found = false;

        if (preg_match('#^' . $route['expression'] . '$#', $path, $matches)) {

            $path_match_found = true;

            if (strtolower($_SERVER['REQUEST_METHOD']) == $route['method']) {

                $params = array();
                if ($route['method'] == 'post') {
                    array_unshift($params, $_POST);
                } elseif ($route['method'] == 'put') {
                    parse_str(file_get_contents('php://input'), $_PUT);
                    array_unshift($params, $_PUT);
                }

                array_shift($matches); 

                if ($basepath != '' && $basepath != '/') {
                    array_shift($matches);
                }

                if (is_array($route['function'])) {
                    $controller = (new \ReflectionClass($route['function'][0]))->newInstance();
                    $route['function'] = [$controller, $route['function'][1]];

                    $ref_method = new \ReflectionMethod($route['function'][0], $route['function'][1]);
                    $ref_params = $ref_method->getParameters();

                    foreach ($ref_params as $p) {
                        if ($p->getType() != 'array') {
                            array_push($params, array_shift($matches));
                        }
                    }
                }
                call_user_func_array($route['function'], $params);

                $route_match_found = true;
            }
        }
        return array($path_match_found, $route_match_found);
    }

    /**
     * Check whenever request URI matches one in the routes list
     * @see checkRoute()
     */
    public static function run(string $basepath = '/')
    {
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);

        if (isset($parsed_url['path'])) {
            $path = $parsed_url['path'];
            if (strlen($path) > 1 && substr($path, -1) == "/") {
                $path = substr($path, 0, -1);
                self::redirect($path);
            }
        } else {
            $path = '/';
        }

        $path_match_found = false;
        $route_match_found = false;

        foreach (self::$routes as $route) {
            list($path_match_found, $route_match_found) = self::checkRoute($route, $path, $basepath);
            if ($path_match_found && $route_match_found) {
                break;
            }
        }

        if (!$route_match_found) {

            if ($path_match_found) {
                header("HTTP/1.0 405 Method Not Allowed");
                if (self::$methodNotAllowed) {
                    call_user_func_array(self::$methodNotAllowed, array($path, $_SERVER['REQUEST_METHOD']));
                }
            } else {
                header("HTTP/1.0 404 Not Found");
                if (self::$pathNotFound) {
                    call_user_func_array(self::$pathNotFound, array($path));
                }
            }
        }
    }
}
