<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Services_Akismet2 is a package to use Akismet spam-filtering from PHP
 *
 * This package provides an object-oriented interface to the Akismet REST
 * API. Akismet is used to detect and to filter spam comments posted on
 * weblogs. Though the use of Akismet is not specific to Wordpress, you will
 * need a Wordpress API key from {@link http://wordpress.com} to use this
 * package.
 *
 * Akismet is free for personal use and a license may be purchased for
 * commercial or high-volume applications.
 *
 * This package is derived from the miPHP Akismet class written by Bret Kuhns
 * for use in PHP 4. This package requires PHP 5.2.1.
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2007-2008 Bret Kuhns, silverorange
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Bret Kuhns
 * @copyright 2007-2008 Bret Kuhns, 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @version   CVS: $Id$
 * @link      http://pear.php.net/package/Services_Akismet2
 * @link      http://akismet.com/
 * @link      http://akismet.com/development/api/
 * @link      http://www.miphp.net/blog/view/php4_akismet_class
 */

/**
 * Comment class definition.
 */
require_once 'Services/Akismet2/Comment.php';

/**
 * Exception thrown when an invalid API key is used.
 */
require_once 'Services/Akismet2/InvalidApiKeyException.php';

/**
 * Exception thrown when an invalid API key is used.
 */
require_once 'Services/Akismet2/HttpException.php';

/**
 * HTTP request object
 */
require_once 'HTTP/Request2.php';

// {{{ class Services_Akismet2

/**
 * Class to use Akismet API from PHP
 *
 * Example usage:
 * <code>
 *
 * /**
 *  * Handling user-posted comments
 *  {@*}
 *
 * $comment = new Services_Akismet2_Comment(array(
 *     'author'      => 'Test Author',
 *     'authorEmail' => 'test@example.com',
 *     'authorUri'   => 'http://example.com/',
 *     'content'     => 'Hello, World!'
 * ));
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet2('http://blog.example.com/', $apiKey);
 *     if ($akismet->isSpam($comment)) {
 *         // rather than simply ignoring the spam comment, it is recommended
 *         // to save the comment and mark it as spam in case the comment is a
 *         // false positive.
 *     } else {
 *         // save comment as normal comment
 *     }
 * } catch (Services_Akismet2_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet2_HttpException $httpException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $httpException->getMessage();
 * } catch (Services_Akismet2_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * /**
 *  * Submitting a comment as known spam
 *  {@*}
 *
 * $comment = new Services_Akismet2_Comment(array(
 *     'author'      => 'Test Author',
 *     'authorEmail' => 'test@example.com',
 *     'authorUri'   => 'http://example.com/',
 *     'content'     => 'Hello, World!'
 * ));
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet2('http://blog.example.com/', $apiKey);
 *     $akismet->submitSpam($comment);
 * } catch (Services_Akismet2_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet2_HttpException $httpException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $httpException->getMessage();
 * } catch (Services_Akismet2_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * /**
 *  * Submitting a comment as a false positive
 *  {@*}
 *
 * $comment = new Services_Akismet2_Comment(array(
 *     'author'      => 'Test Author',
 *     'authorEmail' => 'test@example.com',
 *     'authorUri'   => 'http://example.com/',
 *     'content'     => 'Hello, World!'
 * ));
 *
 * try {
 *     $apiKey = 'AABBCCDDEEFF';
 *     $akismet = new Services_Akismet2('http://blog.example.com/', $apiKey);
 *     $akismet->submitFalsePositive($comment);
 * } catch (Services_Akismet2_InvalidApiKeyException $keyException) {
 *     echo 'Invalid API key!';
 * } catch (Services_Akismet2_HttpException $httpException) {
 *     echo 'Error communicating with Akismet API server: ' .
 *         $httpException->getMessage();
 * } catch (Services_Akismet2_InvalidCommentException $commentException) {
 *     echo 'Specified comment is missing one or more required fields.' .
 *         $commentException->getMessage();
 * }
 *
 * </code>
 *
 * @category  Services
 * @package   Services_Akismet2
 * @author    Michael Gauthier <mike@silverorange.com>
 * @author    Bret Kuhns
 * @copyright 2007-2008 Bret Kuhns, 2008 silverorange
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 * @link      http://pear.php.net/package/Services_Akismet2
 */
class Services_Akismet2
{
    // {{{ private properties

    /**
     * The port to use to connect to the Akismet API server
     *
     * Defaults to 80.
     *
     * @var integer
     */
    private $_apiPort    = 80;

    /**
     * The Akismet API server name
     *
     * Defaults to 'rest.akismet.com'.
     *
     * @var string
     */
    private $_apiServer  = 'rest.akismet.com';

    /**
     * The Akismet API version to use
     *
     * Defaults to '1.1'.
     *
     * @var string
     */
    private $_apiVersion = '1.1';

    /**
     * The URI of the webblog for which Akismet services will be used
     *
     * @var string
     *
     * @see Services_Akismet2::__construct()
     */
    private $_blogUri = '';

    /**
     * The API key to use to access Akismet services
     *
     * @var string
     *
     * @see Services_Akismet2::__construct()
     */
    private $_apiKey  = '';

    // }}}

    protected $request;

    // {{{ __construct()

    /**
     * Creates a new Akismet object
     *
     * @param string        $blogUri the URI of the webblog homepage.
     * @param string        $apiKey  the API key to use for Akismet services.
     * @param HTTP_Request2 $request optional. The HTTP request object to use.
     *                               If not specified, a HTTP request object is
     *                               created automatically.
     *
     * @throws Services_Akismet2_InvalidApiKeyException if the provided
     *         API key is not valid.
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws PEAR_Exception if the specified HTTP client implementation may
     *         not be used with this PHP installation or if the specified HTTP
     *         client implementation does not exist.
     */
    public function __construct($blogUri, $apiKey,
        HTTP_Request2 $request = null)
    {
        $this->_blogUri = $blogUri;
        $this->_apiKey  = $apiKey;

        // set http request object
        if ($request === null) {
            $request = new HTTP_Request2();
        }

        $this->setRequest($request);

        // make sure the API key is valid
        if (!$this->isApiKeyValid($this->_apiKey)) {
            throw new Services_Akismet2_InvalidApiKeyException('The specified ' .
                'API key is not valid. Key used was: "' .
                $this->_apiKey . '".', 0, $this->_apiKey);
        }
    }

    // }}}
    // {{{ isSpam()

    /**
     * Checks whether or not a comment is spam
     *
     * @param Services_Akismet2_Comment $comment the comment to check.
     *
     * @return boolean true if the comment is spam and false if it is not.
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws Services_Akismet2_InvalidCommentException if the specified
     *         comment is missing required fields.
     */
    public function isSpam(Services_Akismet2_Comment $comment)
    {
        $params         = $comment->getPostParameters();
        $params['blog'] = $this->_blogUri;

        $response = $this->sendRequest('comment-check', $params);

        return ($response == 'true');
    }

    // }}}
    // {{{ submitSpam()

    /**
     * Submits a comment as an unchecked spam to the Akismet server
     *
     * Use this method to submit comments that are spam but are not detected
     * by Akismet.
     *
     * @param Services_Akismet2_Comment $comment the comment to submit as spam.
     *
     * @return void
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws Services_Akismet2_InvalidCommentException if the specified
     *         comment is missing required fields.
     */
    public function submitSpam(Services_Akismet2_Comment $comment)
    {
        $params         = $comment->getPostParameters();
        $params['blog'] = $this->_blogUri;

        $this->sendRequest('submit-spam', $params);
    }

    // }}}
    // {{{ submitFalsePositive()

    /**
     * Submits a false-positive comment to the Akismet server
     *
     * Use this method to submit comments that are detected as spam but are not
     * actually spam.
     *
     * @param Services_Akismet2_Comment $comment the comment that is
     *                                          <em>not</em> spam.
     *
     * @return void
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     *
     * @throws Services_Akismet2_InvalidCommentException if the specified
     *         comment is missing required fields.
     */
    public function submitFalsePositive(Services_Akismet2_Comment $comment)
    {
        $params         = $comment->getPostParameters();
        $params['blog'] = $this->_blogUri;

        $this->sendRequest('submit-ham', $params);
    }

    // }}}
    // {{{ setRequest()

    /**
     * Sets the HTTP request object to use
     *
     * @param HTTP_Request2 $request the HTTP request object to use.
     *
     * @return void
     */
    public function setRequest(HTTP_Request2 $request)
    {
        $this->request = $request;
    }

    // }}}
    // {{{ sendRequest()

    /**
     * Calls a method on the Akismet API server using a HTTP POST request
     *
     * @param string $methodName the name of the Akismet method to call.
     * @param array  $params     optional. Array of request parameters for the
     *                           Akismet call.
     *
     * @return string the HTTP response content.
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     */
    protected function sendRequest($methodName, array $params = array())
    {
        if (strlen($this->_apiKey) > 0) {
            $host = $this->_apiKey . '.' . $this->_apiServer;
        } else {
            $host = $this->_apiServer;
        }

        $url = sprintf('http://%s:%s/%s/%s',
            $host,
            $this->_apiPort,
            $this->_apiVersion,
            $methodName);

        try {
            $this->request->setUrl($url);
            $this->request->setHeader('User-Agent', $this->_getUserAgent());
            $this->request->setMethod(HTTP_Request2::METHOD_POST);
            $this->request->addPostParameter($params);

            $response = $this->request->send();
        } catch (HTTP_Request2_Exception $e) {
            $message = 'Error in request to Akismet: ' . $e->getMessage();
            throw new Services_Akismet2_HttpException($message, $e->getCode());
        }

        return $response->getBody();
    }

    // }}}
    // {{{ isApiKeyValid()

    /**
     * Checks with the Akismet server to determine if an API key is
     * valid
     *
     * @param string $key the API key to check.
     *
     * @return boolean true if the key is valid and false if it is not valid.
     *
     * @throws Services_Akismet2_HttpException if there is an error
     *         communicating with the Akismet API server.
     */
    protected function isApiKeyValid($key)
    {
        $params = array(
            'key'  => $key,
            'blog' => $this->_blogUri
        );

        $response = $this->sendRequest('verify-key', $params);
        return ($response == 'valid');
    }

    // }}}
    // {{{ _getUserAgent()

    /**
     * Gets the HTTP user-agent used to make Akismet requests
     *
     * @return string the HTTP user-agent used to make Akismet request.
     */
    private function _getUserAgent()
    {
        return sprintf('@name@/@api-version@ | Akismet/%s',
            $this->_apiVersion);
    }

    // }}}
}

// }}}

?>