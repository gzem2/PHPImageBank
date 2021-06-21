<?php

declare(strict_types=1);

namespace PHPImageBank\App;

/**
 * Generic controller class.
 */
class Controller
{
    /**
     * Display view with specified data
     * @param string $viewName name of view to be displayed
     * @param array $params array of parameters to be used by view
     * @return view
     */
    public function view(string $viewName, array $params = [])
    {
        if(!empty($params)) {
            extract($params);
        }
        include(__DIR__ . '../../views/' . $viewName . ".php");
    }

    /**
     * Display view with error message and http error code
     * @param string $message error message
     * @param int $code http error code
     * @return error view
     */
    public function error(string $message, int $code)
    {
        switch ($code) {
            case 400: $text = 'Bad Request'; break;
            case 401: $text = 'Unauthorized'; break;
            case 402: $text = 'Payment Required'; break;
            case 403: $text = 'Forbidden'; break;
            case 404: $text = 'Not Found'; break;
            case 405: $text = 'Method Not Allowed'; break;
            case 406: $text = 'Not Acceptable'; break;
            case 407: $text = 'Proxy Authentication Required'; break;
            case 408: $text = 'Request Time-out'; break;
            case 409: $text = 'Conflict'; break;
            case 410: $text = 'Gone'; break;
            case 411: $text = 'Length Required'; break;
            case 412: $text = 'Precondition Failed'; break;
            case 413: $text = 'Request Entity Too Large'; break;
            case 414: $text = 'Request-URI Too Large'; break;
            case 415: $text = 'Unsupported Media Type'; break;
            case 500: $text = 'Internal Server Error'; break;
            case 501: $text = 'Not Implemented'; break;
            case 502: $text = 'Bad Gateway'; break;
            case 503: $text = 'Service Unavailable'; break;
            case 504: $text = 'Gateway Time-out'; break;
            case 505: $text = 'HTTP Version not supported'; break;
            default:
                exit('Unknown http status code "' . htmlentities(strval($code)) . '"');
            break;
        }
        header('HTTP/1.0 ' . $code . ' ' . $text);
        return $this->view("error", [
            'data' => ["message" => $message],
            'title' => $message,
            'code' => $code
        ]);
    }
}
