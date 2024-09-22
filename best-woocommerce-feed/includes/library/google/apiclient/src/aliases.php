<?php

namespace RexFeed;

if (\class_exists('RexFeed\\Google_Client', \false)) {
    // Prevent error with preloading in PHP 7.4
    // @see https://github.com/googleapis/google-api-php-client/issues/1976
    return;
}
$classMap = ['RexFeed\\Google\\Client' => 'Google_Client', 'RexFeed\\Google\\Service' => 'Google_Service', 'RexFeed\\Google\\AccessToken\\Revoke' => 'Google_AccessToken_Revoke', 'RexFeed\\Google\\AccessToken\\Verify' => 'Google_AccessToken_Verify', 'RexFeed\\Google\\Model' => 'Google_Model', 'RexFeed\\Google\\Utils\\UriTemplate' => 'Google_Utils_UriTemplate', 'RexFeed\\Google\\AuthHandler\\Guzzle6AuthHandler' => 'Google_AuthHandler_Guzzle6AuthHandler', 'RexFeed\\Google\\AuthHandler\\Guzzle7AuthHandler' => 'Google_AuthHandler_Guzzle7AuthHandler', 'RexFeed\\Google\\AuthHandler\\Guzzle5AuthHandler' => 'Google_AuthHandler_Guzzle5AuthHandler', 'RexFeed\\Google\\AuthHandler\\AuthHandlerFactory' => 'Google_AuthHandler_AuthHandlerFactory', 'RexFeed\\Google\\Http\\Batch' => 'Google_Http_Batch', 'RexFeed\\Google\\Http\\MediaFileUpload' => 'Google_Http_MediaFileUpload', 'RexFeed\\Google\\Http\\REST' => 'Google_Http_REST', 'RexFeed\\Google\\Task\\Retryable' => 'Google_Task_Retryable', 'RexFeed\\Google\\Task\\Exception' => 'Google_Task_Exception', 'RexFeed\\Google\\Task\\Runner' => 'Google_Task_Runner', 'RexFeed\\Google\\Collection' => 'Google_Collection', 'RexFeed\\Google\\Service\\Exception' => 'Google_Service_Exception', 'RexFeed\\Google\\Service\\Resource' => 'Google_Service_Resource', 'RexFeed\\Google\\Exception' => 'Google_Exception'];
foreach ($classMap as $class => $alias) {
    \class_alias($class, $alias);
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
class Google_Task_Composer extends \RexFeed\Google\Task\Composer
{
}
/**
 * This class needs to be defined explicitly as scripts must be recognized by
 * the autoloader.
 */
\class_alias('RexFeed\\Google_Task_Composer', 'Google_Task_Composer', \false);
if (\false) {
    class Google_AccessToken_Revoke extends \RexFeed\Google\AccessToken\Revoke
    {
    }
    class Google_AccessToken_Verify extends \RexFeed\Google\AccessToken\Verify
    {
    }
    class Google_AuthHandler_AuthHandlerFactory extends \RexFeed\Google\AuthHandler\AuthHandlerFactory
    {
    }
    class Google_AuthHandler_Guzzle5AuthHandler extends \RexFeed\Google\AuthHandler\Guzzle5AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle6AuthHandler extends \RexFeed\Google\AuthHandler\Guzzle6AuthHandler
    {
    }
    class Google_AuthHandler_Guzzle7AuthHandler extends \RexFeed\Google\AuthHandler\Guzzle7AuthHandler
    {
    }
    class Google_Client extends \RexFeed\Google\Client
    {
    }
    class Google_Collection extends \RexFeed\Google\Collection
    {
    }
    class Google_Exception extends \RexFeed\Google\Exception
    {
    }
    class Google_Http_Batch extends \RexFeed\Google\Http\Batch
    {
    }
    class Google_Http_MediaFileUpload extends \RexFeed\Google\Http\MediaFileUpload
    {
    }
    class Google_Http_REST extends \RexFeed\Google\Http\REST
    {
    }
    class Google_Model extends \RexFeed\Google\Model
    {
    }
    class Google_Service extends \RexFeed\Google\Service
    {
    }
    class Google_Service_Exception extends \RexFeed\Google\Service\Exception
    {
    }
    class Google_Service_Resource extends \RexFeed\Google\Service\Resource
    {
    }
    class Google_Task_Exception extends \RexFeed\Google\Task\Exception
    {
    }
    interface Google_Task_Retryable extends \RexFeed\Google\Task\Retryable
    {
    }
    class Google_Task_Runner extends \RexFeed\Google\Task\Runner
    {
    }
    class Google_Utils_UriTemplate extends \RexFeed\Google\Utils\UriTemplate
    {
    }
}
