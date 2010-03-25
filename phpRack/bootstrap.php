<?php
/**
 * phpRack: Integration Testing Framework
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt. It is also available 
 * through the world-wide-web at this URL: http://www.phprack.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phprack.com so we can send you a copy immediately.
 *
 * @copyright Copyright (c) phpRack.com
 * @version $Id$
 * @category phpRack
 */

// Here we define a error handler in order to catch all possible
// PHP errors and show them online, no matter what server settings
// exist for error handling...
set_error_handler(
    create_function(
        '$errno, $errstr, $errfile, $errline',
        '
        echo sprintf(
            "phpRack error (%s): %s, in %s [line:%d]\n",
            $errno,
            $errstr,
            $errfile,
            $errline
        );
        '
    )
);

try {
    // This variable ($phpRackConfig) shall be declared and filled with
    // values in your phprack.php file, which calls this bootstraper. For
    // complete reference on this variable see:
    // http://trac.fazend.com/phpRack/wiki/Bootstrap
    global $phpRackConfig;
    if (!isset($phpRackConfig)) {
        throw new Exception('Invalid configuration: $phpRackConfig is missed');
    }
    
    if (!defined('PHPRACK_VERSION')) {
        define('PHPRACK_VERSION', '0.1dev');
    }

    if (!defined('PHPRACK_AJAX_TAG')) {
        define('PHPRACK_AJAX_TAG', 'test');
    }

    if (!defined('PHPRACK_AJAX_TOKEN')) {
        define('PHPRACK_AJAX_TOKEN', 'token');
    }

    if (!defined('PHPRACK_PATH')) {
        define('PHPRACK_PATH', dirname(__FILE__));
    }

    /**
     * @see phpRack_Runner
     */
    require_once PHPRACK_PATH . '/Runner.php';
    $runner = new phpRack_Runner($phpRackConfig);
    
    /**
     * @see phpRack_View
     */
    require_once PHPRACK_PATH . '/View.php';

    // show login form, if the user is not authenticated yet
    if (!$runner->isAuthenticated()) {
        require_once PHPRACK_PATH . '/View.php';
        $view = new phpRack_View();
        $view->assign(array('authResult' => $runner->getAuthResult()));
        throw new Exception($view->render('login.phtml'));
    }
    
    // if it's CLI enviroment - just show a full test report
    if ($runner->isCliEnvironment()) {
        throw new Exception($runner->runSuite());
    }

    // Global layout is required, show the front web page of the report
    if (empty($_GET[PHPRACK_AJAX_TAG])) {
        $view = new phpRack_View(); 
        $view->assign(array('runner' => $runner)); 
        throw new Exception($view->render());
    }
    
    // Execute one individual test and return its result
    // in JSON format. We reach this point only in AJAX calls from
    // already rendered testing page.
    throw new Exception($runner->run($_GET[PHPRACK_AJAX_TAG], $_GET[PHPRACK_AJAX_TOKEN]));

} catch (Exception $e) {
    echo $e->getMessage();
}
