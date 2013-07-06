<?php

/**
 * spamrule/options.php
 * 
 * Options page for the SquirrelMail "spamrule" plugin,
 * which provides users with simple management of email filter groups.
 * 
 * @copyright Copyright (c) 2002-2007 O'Shaughnessy Evans <shaug-sqml @ wumpus.org>
 * @version $Id: options.php,v 1.9 2007/09/13 19:59:21 shaug Exp $
 * @license http://opensource.org/licenses/artistic-license-2.0.php
 * @package plugins
 * @subpackage spamrule
 */


// load init scripts for SquirrelMail 1.5 or 1.4
if (file_exists('../../include/init.php')) {
    require('../../include/init.php');
}
else if (file_exists('../../include/validate.php')) {
    if (!defined('SM_PATH')) {
        define('SM_PATH', '../../');
    }
    include_once(SM_PATH . 'include/validate.php');
} 
else if (file_exists('../../src/validate.php')) {
    chdir('..');
    if (!defined('SM_PATH')) {
        define('SM_PATH', '../');
    }
    include_once(SM_PATH . 'src/validate.php');
}

// load the compatibility plugin
include_once(SM_PATH . 'plugins/compatibility/functions.php');

global $SPAMRULE_OPTS, $action, $color;
// When letting the user select from the CUSTOM group of filters,
// we're merging data from the form into SPAMRULE_OPTS[groups][CUSTOM],
// so we can't use sqgetGlobalVar to manipulate that variable here.
//sqgetGlobalVar('SPAMRULE_OPTS', $SPAMRULE_OPTS);
sqgetGlobalVar('action', $action, SQ_FORM);


if ($action != 'editlist') {
    displayPageHeader($color, '');
}


// we're internationalized, so bind gettext functions to our domain
textdomain('spamrule');


// load plugin configs and functions
load_config('spamrule', array('config.php'));
if (file_exists(SM_PATH . 'plugins/spamrule/config_local.php')) {
    include_once(SM_PATH . 'plugins/spamrule/config_local.php');
}
require_once(SM_PATH . 'plugins/spamrule/lib.php');


echo <<<EOjs

<script language="JavaScript" type="text/javascript">
  // subwin:  a simple function to open a new window
  // arguments:
  //   uri      page that the new window should load
  //   name     window target name
  //   w        width of the window
  //   h        height of the window
  function subwin(uri, name, w, h) {
      window.open(uri, name, "width="+w+",height="+h+",resizable,"+
                             "scrollbars,status");
  }
</script>


EOjs;


switch ($action) {
case 'groups':
    sqgetGlobalVar('newgroup', $group, SQ_FORM);
    sqgetGlobalVar('finish', $finish, SQ_FORM);

    if ($group == 'CUSTOM' && !isset($finish)) {
     // !isset($_POST['SPAMRULE_OPTS']['groups'][$group]['filters'])) {
        sr_print_header($SPAMRULE_OPTS['custom']['header'], 3);
        sr_select_filters();
        sr_print_footer($SPAMRULE_OPTS['custom']['footer'], 3);
    }
    else {
        $filters = $group == 'CUSTOM'
                   ? $_POST['SPAMRULE_OPTS']['groups'][$group]['filters']
                   : $SPAMRULE_OPTS['groups'][$group]['filters'];
        sr_install_filters($group, $filters); // || fail();
        //sr_print_header($SPAMRULE_OPTS['groups']['summary']['header'], 3);
        //sr_print_summary($group);
        //sr_print_footer($SPAMRULE_OPTS['groups']['summary']['footer'], 3);
        sr_print_header($SPAMRULE_OPTS['show']['header'], 3);
        sr_print_subscribed();
        sr_print_footer($SPAMRULE_OPTS['show']['footer'], 3);
    }
    break;
case 'custom':
    break;
case 'subscribed':
    sr_print_header($SPAMRULE_OPTS['show']['header'], 3);
    sr_print_subscribed();
    sr_print_footer($SPAMRULE_OPTS['show']['footer'], 3);
    break;
case 'editlist':
    sqgetGlobalVar('list', $list, SQ_FORM);
    displayHtmlHeader(_("Spam Rules:"). ' '. $SPAMRULE_OPTS[$list]['title']);
    switch ($list) {
    case 'allowed_senders':
    case 'blocked_senders':
    case 'blocked_subjects':
    case 'subject_passwords':
    case 'allowed_recipients':
        sr_print_header($SPAMRULE_OPTS[$list]['header'], 3);
        sr_maintain_list($list);
        sr_print_footer($SPAMRULE_OPTS[$list]['footer'], 3);
        break;
    default:
        sr_print_header($SPAMRULE_OPTS['groupsel']['header'], 3);
        sr_select_group();
        sr_print_footer($SPAMRULE_OPTS['groupsel']['footer'], 3);
        break;
    }
    break;
default:
    sr_print_header($SPAMRULE_OPTS['groupsel']['header'], 3);
    sr_select_group();
    sr_print_footer($SPAMRULE_OPTS['groupsel']['footer'], 3);
    break;
}

?>

</body>
</html>
