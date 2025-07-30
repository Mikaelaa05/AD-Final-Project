<?php
/**
 * Centralized Error Handler Utility
 * Handles all error responses throughout the application
 */

class ErrorHandler
{
    /**
     * Redirect to error page with proper HTTP status code
     * 
     * @param int $code HTTP status code
     * @param string $message Error message
     * @param string $type Error type (unauthorized, database, badrequest, notfound, server)
     * @param string $redirect_url URL to redirect back to
     */
    public static function redirectToError($code, $message, $type = 'server', $redirect_url = '/')
    {
        $query_params = http_build_query([
            'code' => $code,
            'message' => $message,
            'redirect' => $redirect_url
        ]);
        
        $error_file = self::getErrorFile($type);
        
        header("Location: /errors/{$error_file}?{$query_params}");
        exit;
    }
    
    /**
     * Include error page directly (for immediate display)
     * 
     * @param int $code HTTP status code
     * @param string $message Error message
     * @param string $type Error type
     * @param string $redirect_url URL to redirect back to
     */
    public static function showError($code, $message, $type = 'server', $redirect_url = '/')
    {
        $_GET['code'] = $code;
        $_GET['message'] = $message;
        $_GET['redirect'] = $redirect_url;
        
        $error_file = self::getErrorFile($type);
        $error_path = ERRORS_PATH . '/' . $error_file;
        
        if (file_exists($error_path)) {
            include $error_path;
        } else {
            // Fallback to basic error display
            http_response_code($code);
            echo "<h1>Error {$code}</h1><p>" . htmlspecialchars($message) . "</p>";
        }
        exit;
    }
    
    /**
     * Return JSON error response (for AJAX requests)
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param array $additional_data Additional data to include
     */
    public static function jsonError($message, $code = 400, $additional_data = [])
    {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = array_merge([
            'success' => false,
            'message' => $message,
            'code' => $code
        ], $additional_data);
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Handle database connection errors
     * 
     * @param string $message Custom error message
     * @param string $redirect_url URL to redirect back to
     */
    public static function databaseError($message = 'Database connection failed', $redirect_url = '/')
    {
        error_log("Database Error: " . $message);
        self::redirectToError(500, $message, 'database', $redirect_url);
    }
    
    /**
     * Handle unauthorized access
     * 
     * @param string $message Custom error message
     * @param string $redirect_url URL to redirect back to
     */
    public static function unauthorizedError($message = 'You must be logged in to view this page', $redirect_url = '/pages/Login/index.php')
    {
        self::redirectToError(401, $message, 'unauthorized', $redirect_url);
    }
    
    /**
     * Handle bad requests
     * 
     * @param string $message Custom error message
     * @param string $redirect_url URL to redirect back to
     */
    public static function badRequestError($message = 'Invalid request', $redirect_url = '/')
    {
        self::redirectToError(400, $message, 'badrequest', $redirect_url);
    }
    
    /**
     * Handle not found errors
     * 
     * @param string $message Custom error message
     * @param string $redirect_url URL to redirect back to
     */
    public static function notFoundError($message = 'Resource not found', $redirect_url = '/')
    {
        self::redirectToError(404, $message, 'notfound', $redirect_url);
    }
    
    /**
     * Handle server errors
     * 
     * @param string $message Custom error message
     * @param string $redirect_url URL to redirect back to
     */
    public static function serverError($message = 'An internal server error occurred', $redirect_url = '/')
    {
        error_log("Server Error: " . $message);
        self::redirectToError(500, $message, 'server', $redirect_url);
    }
    
    /**
     * Get error file name based on type
     * 
     * @param string $type Error type
     * @return string Error file name
     */
    private static function getErrorFile($type)
    {
        $error_files = [
            'unauthorized' => 'unauthorized.error.php',
            'database' => 'database.error.php',
            'badrequest' => 'badrequest.error.php',
            'notfound' => 'notfound.error.php',
            'server' => 'server.error.php'
        ];
        
        return $error_files[$type] ?? 'server.error.php';
    }
    
    /**
     * Check if request is AJAX
     * 
     * @return bool
     */
    public static function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Handle errors appropriately based on request type
     * 
     * @param string $message Error message
     * @param int $code HTTP status code
     * @param string $type Error type for page redirects
     * @param string $redirect_url URL to redirect back to
     */
    public static function handleError($message, $code = 500, $type = 'server', $redirect_url = '/')
    {
        if (self::isAjax()) {
            self::jsonError($message, $code);
        } else {
            self::redirectToError($code, $message, $type, $redirect_url);
        }
    }
}
