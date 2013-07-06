<?php

/**
 * spamrule/setup.php
 * 
 * The plugin initialization page for the SquirrelMail "spamrule" plugin,
 * which provides users with simple management of email filter groups.
 * 
 * @copyright Copyright (c) 2002-2007 O'Shaughnessy Evans <shaug-sqml @ wumpus.org>
 * @version $Id: setup.php,v 1.8 2007/09/13 18:56:07 shaug Exp $
 * @license http://opensource.org/licenses/artistic-license-2.0.php
 * @package plugins
 * @subpackage spamrule
 */


/**
 * squirrelmail_plugin_init_spamrule()
 * 
 * Initialize the plugin.
 */
function squirrelmail_plugin_init_spamrule()
{
    global $squirrelmail_plugin_hooks;

    $squirrelmail_plugin_hooks['optpage_register_block']['spamrule']
     = 'spamrule_options';
    $squirrelmail_plugin_hooks['menuline']['spamrule'] = 'spamrule_menuline';
}


/**
 * spamrule_options()
 * 
 * Set up the Options page block.
 */
function spamrule_options()
{
    global $optpage_blocks;

    //bindtextdomain('spamrule', SM_PATH. 'plugins/spamrule/locale');
    textdomain('spamrule');

    $optpage_blocks[] = array(
        'name' => _("Spam Rules"),
        'url'  => '../plugins/spamrule/options.php',
        'desc' => _("Decide how you want to control the incoming flood of ".
                    "mail you didn't ask for.  These filters work all the ".
                    "time, whether you use webmail or your own program to ".
                    "read email."),
        'js'   => FALSE
    );

    //bindtextdomain('squirrelmail', SM_PATH. 'locale');
    textdomain('squirrelmail');
}


/**
 * spamrule_menuline()
 * 
 * Add a link to the main frame's menu line.
 */
function spamrule_menuline()
{
    //bindtextdomain('spamrule', SM_PATH. 'plugins/spamrule/locale');
    textdomain('spamrule');

    displayInternalLink('plugins/spamrule/options.php', _("Spam Filters"), '');
    echo '&nbsp;&nbsp;';

    //bindtextdomain('squirrelmail', SM_PATH. 'locale');
    textdomain('squirrelmail');
}


/**
 * spamrule_info()
 * 
 * @returns array with various bits of information about the plugin.
 * Thanks to Paul Lesniewski for the example in the compatibility plugin.
 * 
 * Each element in the array is an info parameter.  Elements may be of any type.
 */
function spamrule_info()
{
    return array(
        'english_name' => 'Spamrule',
        'authors' => array(
            'O\'Shaughnessy Evans' => array('email' => 'shaug-sqml@wumpus.org'),
        ),
        'version' => '0.5',
        'required_sm_version' => '1.2',
        'requires_configuration' => 1,
        'requires_source_patch' => 0,
        'required_plugins' => array('compatibility'),
        'summary' => 'Provides users with simple sets of filters from which '.
                     'they can choose, or lets them customize their own set.',
        'details' => <<<EOdetails
Spamrule gives users an Options page to configure their own set of spam filters based on maildrop or procmail filters provided by the site admin. The filters can be grouped however you like (e.g. "no filters", "loose filters", and "strict filters") so that users can just choose a group, or users can choose to select from any of the filters to create their own group if they like. There are also mechanisms to edit lists that the filters use -- e.g. a filter might only allow email from certain senders, then the list editor page would let them maintain that list of senders. It's designed to initially appear as simple as Hotmail's filters, but it provides much more flexibility if the user wants it.
EOdetails
    );
}


/**
 * spamrule_version()
 * 
 * @returns string identifying the plugin's version number.
 */
function spamrule_version()
{
    $info = spamrule_info();
    return $info['version'];
}
