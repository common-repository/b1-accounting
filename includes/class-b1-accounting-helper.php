<?php

/**
 * @since 2.0.0
 * @package B1_Accounting
 * @subpackage B1_Accounting/includes
 * @author B1.lt <info@b1.lt>
 * @link https://www.b1.lt
 */
class B1_Accounting_Helper
{

    public static function sendResponse($data)
    {
        header('Content-type: application/json');
        wp_die(json_encode($data));
    }

    public static function sendErrorResponse($message)
    {
        self::sendResponse([
            'success' => false,
            'message' => $message,
        ]);
    }

    public static function sendSuccessResponse($message = null)
    {
        self::sendResponse([
            'success' => true,
            'message' => $message ? $message : 'OK',
        ]);
    }

    public static function debug($value)
    {
        echo '<pre>';
        print_r($value);
        echo '</pre>';
    }

}
