<?php

/**
 * spamrule/config.php
 * 
 * User configuration page for the SquirrelMail "spamrule" plugin,
 * which provides users with simple management of email filter groups.
 * 
 * @copyright Copyright (c) 2002-2007 O'Shaughnessy Evans <shaug-sqml @ wumpus.org>
 * @version $Id: config.php.ex,v 1.3 2007/09/13 19:54:49 shaug Exp $
 * @license http://opensource.org/licenses/artistic-license-2.0.php
 * @package plugins
 * @subpackage spamrule
 */


// make these global so that the vlogin plugin can override options
// and other files can access them
global $SPAMRULE_OPTS, $SPAMRULE_INSTALL, $domain, $javascript_on, $username;
sqsession_register($SPAMRULE_OPTS, 'SPAMRULE_OPTS');
sqsession_register($SPAMRULE_INSTALL, 'SPAMRULE_INSTALL');
sqgetGlobalVar('domain', $domain, SQ_SESSION);
sqgetGlobalVar('javascript_on', $javascript_on, SQ_SESSION);
sqgetGlobalVar('username', $username, SQ_SESSION);


/*
 * Filter Installation Config
 * 
 * In the options below, you should configure the name of the host that
 * provides IMAP user with FTP access to their accounts.  Each user must
 * have their own account with the ability to change their mail forward
 * file and their mail filtering file.
 * 
 * If this isn't an option, you can configure a local program to run when
 * installing filters by setting $SPAMRULE_INSTALL['command'].  If that 
 * variable is set, it will be used instead of FTP.
 */

// The host that stores user mail filter info:
//  Each user with an IMAP account must have the same login and password here.
// Don't override this if it's already set so that the vlogin plugin can
// define it.
if (empty($SPAMRULE_OPTS['ftphost'])) {
    $SPAMRULE_OPTS['ftphost'] = 'localhost';
}

// User mail filter info:
//
// What is the name of the mail filter config?
//  for procmail, it would be .procmailrc
//  for maildrop, .mailfilter
$SPAMRULE_INSTALL['filter_file'] = '.mailfilter';
// What should an individual line in the filter file look like?
//  %s will be replaced with the name of the given filter as set in
//  $SPAMRULE_FILTERS[] (see below).
$SPAMRULE_INSTALL['filter_string'] =
 "include(\"/usr/local/etc/maildroprcs/%s\")\n";

// User forward file info:
//
// What is the name of the user's filter file?
//  for sendmail or postfix, it would be .forward
//  for qmail, .qmail
$SPAMRULE_INSTALL['forward_file']    = '.forward';
// What should the filter file contain?  (make sure to include the newline)
//$SPAMRULE_INSTALL['forward_string']  = '| /usr/local/bin/maildrop';
$SPAMRULE_INSTALL['forward_string']  = '"|/usr/local/bin/maildrop"';
// What pattern will match the forward filter string?
//$SPAMRULE_INSTALL['forward_pattern'] = '^\| */usr/local/bin/maildrop';
$SPAMRULE_INSTALL['forward_pattern'] = '^\"?\| */usr/local/bin/maildrop\"?';
// What pattern will match the local inbox?
//$SPAMRULE_INSTALL['inbox_pattern']   = '^\.\/Incoming';
$SPAMRULE_INSTALL['inbox_pattern']   = "^\.$username";

// The command to run to install the new set of filters:
//  %s will be replaced with the names of all the filters being installed,
//  as named in the keys of $SPAMRULE_FILTERS[].
/*
$SPAMRULE_INSTALL['command'] =
 "/usr/local/www/libexec/save-mailfilters -l {$username} %s";
*/



/*
 * Individual Filters
 * 
 * Define an array of filters here:
 * Each filter entry should be keyed by the name with which its form input
 * will be known.  Each filter value should be an array with these strings:
 *   title:      A brief name for the filter.
 *   text:       A few sentences describing the filter.
 *   install:    If defined, this command will be run when the filter has been
 *               selected for use.  If not defined, the default install command
 *               or FTP process will be used (see above).
 *   uninstall:  If defined, this command will be run whenever the filter has
 *               *not* been selected for use.
 */
$SPAMRULE_OPTS['filters'] = array(
  'only-from'  => array(
    'title'    => _("Only From"),
    'text'     => _("Only accept mail from an <em>Allowed Sender</em>.  Mail ".
                    "from them will always go through; mail from <em>everyone ".
                    "else will be sent to your &quot;Junk&quot; mailbox.".
                    "</em>  <em>Please note!</em>  This supercedes all other ".
                    "filters.  IF YOU ENABLE THIS, NONE OF YOUR OTHER FILTERS ".
                    "WILL BE USED.").
                  '<div align=right><small><em>'. _("Update your:"). '  '.
                  ( $javascript_on
                    ? '<a href="javascript:void(0)" onclick="subwin'.
                      '(\'options.php?action=editlist&list=allowed_senders\', '.
                      '\'sr_editlist\', 500, 600)">'
                    : '<a href="options.php?action=editlist'.
                      '&list=allowed_senders" target="_blank">'
                  ).
                  _("Allowed Senders"). '</a></em></small></div><hr size=1>'
  ),
  'always-from' => array(
    'title'    => _("Always From"),
    'text'     => _("Always accept mail from an <em>Allowed Sender</em>.  ".
                    "Mail from them will always go through, no matter what ".
                    "other filters are in use.").
                  '<div align=right><small><em>'. _("Update your:"). '  '.
                  ( $javascript_on
                    ? '<a href="javascript:void(0)" onclick="subwin'.
                      '(\'options.php?action=editlist&list=allowed_senders\', '.
                      '\'sr_editlist\', 450, 450)">'
                    : '<a href="options.php?action=editlist'.
                      '&list=allowed_senders" target="_blank">'
                  ).
                  _("Allowed Senders"). '</a></em></small></div>'
),
  'subject-passwords' => array(
    'title'    => _("Subject Passwords"),
    'text'     => _("Always accept mail with one of your <em>Subject ".
                    "Passwords</em>.  Create one or more secret words, ".
                    "and then tell them to your friends.  All they have to ".
                    "do is include a secret word in the <b>Subject</b> and ".
                    "the message will get past your filters.").
                  '<div align=right><small><em>'. _("Update your:"). '  '.
                  ( $javascript_on
                    ? '<a href="javascript:void(0)" onclick="subwin'.
                      '(\'options.php?action=editlist'.
                      '&list=subject_passwords\', \'sr_editlist\', 450, 450)">'
                    : '<a href="options.php?action=editlist'.
                      '&list=subject_passwords" target="_blank">'
                  ).
                  _("Subject Passwords"). '</a></em></small></div>'
  ),
  'only-to-cc' => array(
    'title'    => _("Only To Me"),
    'text'     => _("If"). " $username@$domain ". _("or one of your ".
                    "<em>aliases</em> isn't in the <b>To</b> or <b>Cc</b> ".
                    "headers, send the message to your &quot;Junk&quot; ".
                    "mailbox.  This <em>may</em> catch some mailing lists, ".
                    "so if you're subscribed to a list, enable the ".
                    "<em>Always From</em> filter and add the list sender to ".
                    "your <em>Allowed Senders</em> before using this.").
                  '<div align=right><small><em>'. _("Update your:"). '  '.
                  ( $javascript_on
                    ? '<a href="javascript:void(0)" onclick="subwin'.
                      '(\'options.php?action=editlist&list='.
                       'allowed_recipients\', \'sr_editlist\', 450, 450)">'
                    : '<a href="options.php?action=editlist&list='.
                      'allowed_recipients" target="_blank">'
                  ).
                  _("Aliases"). '</a> '.
                  ( $javascript_on
                    ? '<a href="javascript:void(0)" onclick="subwin'.
                      '(\'options.php?action=editlist&list=allowed_senders\', '.
                      '\'sr_editlist\', 450, 450)">'
                    : '<a href="options.php?action=editlist'.
                      '&list=allowed_senders" target="_blank">'
                  ).
                  _("Allowed Senders"). '</a></em></small></div>'
  ),
  /* // this doesn't work on a qmail system.  only-to-cc is enough.
  'reject-bcc' => array(
    'title'    => _("No Blind Ccs"),
    'text'     => _("Filter any mail that was sent as a Blind Carbon Copy.  ".
                    "This means the message was sent to people that aren't ".
                    "listed in the <b>To</b> or <b>Cc</b> header, and there's ".
                    "no way for you to know who.")
  ),
  */
  'reject-ad' => array(
    'title'    => _("No Ads"),
    'text'     => _("Catch any mail with a subject that starts with ".
                    "&quot;AD&quot; or &quot;ADV&quot;, or mail that has an ".
                    "<b>X-Advertisement</b> or <b>X-Promotion</b> header.  ".
                    "Some spammers will announce their unsolicited mail ".
                    "this way.")
  ),
  'reject-bulk' => array(
    'title'    => _("No Bulk Mail"),
    'text'     => _("Block any mail that has a &quot;bulk&quot; or ".
                    "&quot;low&quot; <b>Priority</b> header.  This is ".
                    "almost always junk mail.")
  ),
  'reject-not822' => array(
    'title'    => _("No Broken Messages"),
    'text'     => _("Filter any mail that isn't properly formatted email ".
                    "(called &quot;RFC 822-compliant&quot;).  Most Internet ".
                    "Providers will make sure the mail from their servers ".
                    "complies with this and other standards because it's more ".
                    "likely to get delivered correctly, and less likely to ".
                    "break mail programs like Outlook or Eudora.  Spammers, ".
                    "on the other hand, don't seem to care quite as much.")
  ),
  'reject-me' => array(
    'title'    => _("Never From Me"),
    'text'     => _("Never accept mail that was sent from your own address, ".
                    "&quot;"). $username.'@'.$domain. _("&quot;.  Some ".
                    "spammers will fake the sender and make it appear that ".
                    "the message is coming <em>from</em> you as well as ".
                    "being sent <em>to</em> you.  Unless you send yourself ".
                    "email, this is a safe way to stop some junk from ".
                    "getting through.")
  ),
  'reject-from' => array(
    'title'    => _("Never From"),
    'text'     => _("Filter any mail from a <em>Blocked Sender</em>.  Add ".
                    "the addresses of people from whom you never want to ".
                    "get mail.").
                  '<div align=right><small><em>'. _("Update your:"). '  '.
                  ( $javascript_on
                    ? '<a href="javascript:void(0)" onclick="subwin'.
                      '(\'options.php?action=editlist&list=blocked_senders\','.
                      ' \'sr_editlist\', 450, 450)">'
                    : '<a href="options.php?action=editlist&list='.
                      'blocked_senders" target="_blank">'
                  ).
                  _("Blocked Senders"). '</a></em></small></div>'
  ),
  'reject-subject' => array(
    'title'    => _("Bad Subjects"),
    'text'     => _("Filter any mail that has a <em>Blocked Subject</em>.  ".
                    "Edit your list of subject words, then any mail with a ".
                    "subject that matches your list will be filtered.").
                  '<div align=right><small><em>'. _("Update your:"). '  '.
                  ( $javascript_on
                    ? '<a href="javascript:void(0)" onclick="subwin'.
                      '(\'options.php?action=editlist&list=blocked_subjects\','.
                      ' \'sr_editlist\', 450, 450)">'
                    : '<a href="options.php?action=editlist&list='.
                      'blocked_subjects" target="_blank">'
                  ).
                  _("Blocked Subjects"). '</a></em></small></div>'
  ),
  'spamassassin' => array(
    'title'    => _("SpamAssassin"),
    'text'     => _("Use").
                  '<a href="http://www.spamassassin.org" target="_blank">'.
                  _("SpamAssassin"). '</a>, '.
                  _("a comprehensive program that applies many advanced ".
                    "tests, to check whether a message is junk.  Note that ".
                    "if a message is filtered by this rule, you'll see a ".
                    "description of why it was caught in the first part of ".
                    "your message.")
  ),
  'GLOBAL' => array(
    'title'    => _("System-Wide Filters"),
    'text'     => _("These filters are used for all incoming mail at ").
                  $domain.
		  _(".  They utilize various tools to stop junk ".
                    "mail, including sophisticated tests to look for common ".
                    "spam characteristics and blacklists that block mail ".
                    "from known Internet abusers and stop a lot of junk ".
                    "before it gets inside the HOL computers.  Occasionally, ".
                    "though, they may block legitimate email.  We ".
                    "<em>strongly</em> recommend that you leave this enabled, ".
                    "but if you know that mail from a contact is getting ".
                    "blocked, you may want to opt out by disabling this ".
                    "option via the &quot;Custom&quot; filters."),
    'install'   => '/usr/local/www/libexec/spamfriend-del '. $username,
    'uninstall' => '/usr/local/www/libexec/spamfriend-add '. $username,
  ),
/*
 *   'FORWARD' => array(
 *     'default'  => FALSE,
 *     'title'    => _("Forward All Mail"),
 *     'text'     => _("Send all my mail to another address:"). '  '.
 *                   '<input type=text size=30 name=ADD_FORWARD> '.
 *                   '(<small><i>'. _("Keep a local copy, too").
 *                   '</i></small><input type=checkbox name=KEEP_FORWARD>)'
 *   ),
 */
);



/*
 * Filter Groups
 * 
 * Define each group of filters that the user can choose.  These will be
 * presented at the first option page for the plugin.  Each group should
 * have a 'title' and a 'text' definition, both of which will be listed in
 * the option page.  The 'filters' are an array of keys from the
 * $SPAMRULE_FILTERS list.  If the user selects a group, that list of filters
 * will be saved in their account.  'CUSTOM' is a special element here:  if
 * the user selects that, they'll be shown a form that lists each element
 * in $SPAMRULE_FILTERS, with those in this 'filters' element selected by
 * default, where they can choose exactly which filters they want to use.
 */
// Name the default group here (should be one of the keys in $SPAMRULE_GROUPS)
$SPAMRULE_OPTS['default_group'] = 'CAREFUL';
// Array of arrays, each containing a group of filters that the user can
// select.
$SPAMRULE_OPTS['groups'] = array(
  'ALL_OFF' => array(
    'title'   => _("No Personal Filtering"),
    //'filters' => array('GLOBAL'),
    'filters' => array(),
    'text'    => _("Don't do any special filtering."). '<br>'
                 //'<small><i>'. _("Your mail will still go through the ".
                 //"system-wide filters (see the description under ".
                 //"&quot;Custom&quot;).". '</i></small>'
  ),
  'CAREFUL' => array(
    'title'   => _("Careful"),
    //'filters' => array('only-to-cc', 'reject-ad', 'reject-bulk', 'GLOBAL'),
    'filters' => array('only-to-cc', 'reject-ad', 'reject-bulk'),
    'text'    => _("Only filter out messages that clearly look like ".
                   "junk mail."). '<br><small><i>'.
                 _("If you have any other email addresses that go to this ".
                   "mailbox, make sure to return here and customize your ".
                   "aliases when you're finished."). '</i></small>'
  ),
  'STRICT' => array(
    'title'   => _("Strict"),
    'filters' => array('always-from', 'subject-passwords', 'only-to-cc',
                       'reject-ad', 'reject-bulk', 'reject-not822',
                       'reject-from', 'reject-subject'),
                        #'spamassassin', 'GLOBAL'),
    'text'    => _("Be aggressive:  use all the available filters.").
                 '<br><small><i>'.
		 _("... including your own lists of Allowed Senders, ".
		   "Blocked Senders, Blocked Subjects, and Subject Passwords ".
                   "that can be very useful."). '</i></small>'
  ),
  'CUSTOM' => array(
    'title'   => _("Custom"),
    'filters' => array('only-to-cc', 'reject-ad', 'reject-bulk',
                       'reject-not822'), #'GLOBAL'),
    'text'    => _("Choose your own set of filters.").
                 '<br><small><i>'.
                 _("Use this to select from all the filters we have ".
                   "available.").
                 //_("Use this to select from all the filters we have ".
                 //  "available, or to opt out of the system-wide filters ".
                 //  "if you're having trouble getting mail from someone.")
                 '</i></small>'
  )
);



/*
 * Page Descriptions
 * 
 * These sections go at the top and bottom of each different Options page.
 * 
 * $SPAMRULE_OPTS is an array of arrays.  Each array contains text for a
 * different options page:
 * 
 *   $SPAMRULE_OPTS['groups']
 * 
 * Each of these arrays contains a sub-array with specific fields:
 * 
 *   $SPAMRULE_OPTS[<array>]['header']
 *   $SPAMRULE_OPTS[<array>]['footer']
 * 
 *   For the lists that users can maintain:
 *   $SPAMRULE_OPTS[<array>]['file'] -- where the list is kept (can be relative)
 *   $SPAMRULE_OPTS[<array>]['type'] -- if "email", then only addrs are allowed
 *   $SPAMRULE_OPTS[<array>]['title'] -- title of the editing window page
 *   $SPAMRULE_OPTS[<array>]['default'] -- how to initialize the file's contents
 *   $SPAMRULE_OPTS[<array>]['anchor'] -- whether the regex should be anchored
 *                                        to the beginning or end of the string
 *                                        if a partial email addr is given
 *                                        (e.g. "user" or "@domain")
 */

// Description of the first page, showing the filter group selection form:
$SPAMRULE_OPTS['groupsel']['header'] = "<p>\n".
  _("Here you can control the way your incoming mail will be filtered.  ".
    "All messages caught by the rules you select will be delivered to your ".
    "&quot;Junk&quot; mail folder."). "</p>\n";
$SPAMRULE_OPTS['groupsel']['footer'] = "<div align=left><p>\n".
  '<em>'. _("Please note:"). '</em><ul>'. "\n".
  '<li>'. _("Messages in your &quot;Trash&quot; mail folder <u>do not</u> ".
            "count against your storage."). "\n".
  '<li>'. _("Messages in your &quot;Junk&quot; mail folder <u>do</u> ".
            "count against your storage."). "\n".
  '<li>'. _("Messages more than a few days old are removed from both of ".
            "these folders to keep them from getting too large.  Be sure to ".
            "check your &quot;Junk&quot; mailbox periodically to make sure ".
            "you don't miss any mail that you want to get."). "\n".
  '<li>'. _("No junk mail filter is perfect.  Spammers are always trying to ".
            "come up with new ways to get around the things we do to block ".
            "them.  It's possible that some legitimate mail will be caught ".
            "by our filters, so check your Junk mailbox regularly.  The ".
            "stricter the filter you choose, the more likely it will be to ".
            "catch good email, but of course it should also catch a lot more ".
            "spam."). "\n".
  "</ul></p></div>\n";

// Description of the follow up page after filters have been saved
$SPAMRULE_OPTS['summary']['header'] = "<p align=left>\n".
  _("Your preferences have been saved.  Here's the ruleset you've selected:").
  "</p>\n";
$SPAMRULE_OPTS['summary']['footer'] = $SPAMRULE_OPTS['groupsel']['footer']. 
  '<p><a href="options.php">'. _("Back to the beginning"). '</a></p>'. "\n";

// Description of the page showing a user's subscribed filters
$SPAMRULE_OPTS['show']['header'] = NULL;
$SPAMRULE_OPTS['show']['footer'] = $SPAMRULE_OPTS['groupsel']['footer'].
  '<div align=center><p><a href="options.php">'. _("Back to the beginning").
  "</a></p></div>\n";

// Description of the custom filters selection form:
$SPAMRULE_OPTS['custom']['header'] = "<p>\n".
  _("Here you can choose exactly which filters you want to use.  If you ".
    "customized your filters before now, your old settings will already be ".
    "loaded.  If this is your first time, our recommended list will be ".
    "selected.").
  "</p>\n<p><strong>".
  _("Make sure you press &quot;Finish&quot; to save your changes.").
  "</strong></p>\n";
$SPAMRULE_OPTS['custom']['footer'] = '<p><a href="options.php">'.
  _("Back to the beginning"). "</a></p>\n";

// Some custom filter subpages:
// ... for editing the list of senders from whom we'll *always* accept mail
$SPAMRULE_OPTS['allowed_senders']['file'] = '.mailfilters/senders+';
$SPAMRULE_OPTS['allowed_senders']['type'] = 'email';
$SPAMRULE_OPTS['allowed_senders']['title'] = _("Allowed/Exclusive Senders");
$SPAMRULE_OPTS['allowed_senders']['anchor'] = TRUE;
$SPAMRULE_OPTS['allowed_senders']['header'] = '<p align=center>'.
  '<u>'. _("Allowed Senders"). "</u></p>\n".
  "<p align=left>\n".
  _("Here you can list people from whom you <strong><em>always</em></strong> ".
    "want to get email, regardless of your other Spam Filters.  For example, ".
    "if you add &quot;<tt>a_good_friend@{$domain}</tt>&quot;, then any mail ".
    "from your friend will always go directly to your Inbox; if you just add ".
    "&quot;<tt>@{$domain}</tt>&quot;, then mail from anyone at your friend's ".
    "domain will be allowed through.  If you've chosen the <em>Only From</em> ".
    "filter, then you'll <strong>only</strong> get mail from these people, ".
    "and <strong>none from anyone else</strong>.  If you've chosen <em>Always ".
    "From</em>, then mail from these people will always go to your Inbox, but ".
    "mail from other people may also get through.").
  "</p>\n";
$SPAMRULE_OPTS['allowed_senders']['footer'] = $javascript_on
 ? '<p>[ <a href="javascript:window.close()">'. _("Close"). '</a> ]</p>'
 : '';
//$SPAMRULE_OPTS['allowed_senders']['default'] = $username. '@'. $domain;

// ... for editing the list of users from whom we'll *never* accept mail
$SPAMRULE_OPTS['blocked_senders']['file'] = '.mailfilters/senders-';
$SPAMRULE_OPTS['blocked_senders']['type'] = 'email';
$SPAMRULE_OPTS['blocked_senders']['title'] = _("Blocked Senders");
$SPAMRULE_OPTS['blocked_senders']['anchor'] = TRUE;
$SPAMRULE_OPTS['blocked_senders']['header'] = '<p align=center><u>'.
  _("Blocked Senders"). "</u></p>\n".
  "<p align=left>\n".
  _("Here you can list addresses from which you <strong><em>never".
    "</em></strong> want to get email.  For example, if you add ".
    "&quot;<tt>some_joker@example.org</tt>&quot;, then any mail from that ".
    "address will always be put into your &quot;Junk&quot; mailbox.").
  "</p>\n";
$SPAMRULE_OPTS['blocked_senders']['footer']
 = $SPAMRULE_OPTS['allowed_senders']['footer'];

// ... for editing the list of users that we use as aliases in only-to-cc
$SPAMRULE_OPTS['allowed_recipients']['file'] = '.mailfilters/recipients+';
$SPAMRULE_OPTS['allowed_recipients']['type'] = 'email';
$SPAMRULE_OPTS['allowed_recipients']['title'] = _("Allowed Recipients");
$SPAMRULE_OPTS['allowed_recipients']['anchor'] = TRUE;
$SPAMRULE_OPTS['allowed_recipients']['header'] = '<p align=center>'.
  '<u>'. _("Allowed Recipients"). "</u></p>\n".
  "<p align=left>\n".
  _("Here you can list alternative addresses, a.k.a. &quot;aliases&quot;, ".
    "that get sent to"). " <em>$username@$domain</em>.  ".
  _("If you are forwarding mail from other Internet Providers to your").
    ' '. $domain. ' '.
  _("account, or you have an alias or mailbox-only account with us, and you ".
    "chose the <em>Only To Me</em> filter, we recommend that you add those ".
    "other addresses here.").
  "</p>\n".
  "<p align=left>\n".
  _("For example, if you have a Yahoo! account and forward your messages ".
    "here, you should add &quot;<tt>yourname@yahoo.com</tt>&quot; to this ".
    "list so that messages for <em>yourname@yahoo.com</em> won't get caught ".
    "by the <em>Only to Me</em> filter.").
  "</p>\n";
$SPAMRULE_OPTS['allowed_recipients']['footer']
 = $SPAMRULE_OPTS['allowed_senders']['footer'];


// ... for editing the list of subject words that we want to block
$SPAMRULE_OPTS['blocked_subjects']['file'] = '.mailfilters/subjects-';
$SPAMRULE_OPTS['blocked_subjects']['title'] = _("Blocked Subjects");
$SPAMRULE_OPTS['blocked_subjects']['anchor'] = FALSE;
$SPAMRULE_OPTS['blocked_subjects']['type'] = '';
$SPAMRULE_OPTS['blocked_subjects']['header'] = '<p align=center>'.
  '<u>'. _("Blocked Subjects"). "</u></p>\n".
  "<p align=left>\n".
  _("Here you can create a list of subject words that you want to block.  ".
    "Any message that has one of these phrases in the <b>Subject</b> field ".
    "will be filtered.  For example, if you add &quot;<tt>some bad phrase".
    "</tt>&quot;, then any mail that says &quot;<em>some bad phrase</em>".
    "&quot; anywhere in the subject will be put into your &quot;Junk&quot; ".
    "mail folder."). "\n".
  "</p>\n";
$SPAMRULE_OPTS['blocked_subjects']['footer']
 = $SPAMRULE_OPTS['allowed_senders']['footer'];

// ... for editing the list of subject words that we want to accept
$SPAMRULE_OPTS['subject_passwords']['file'] = '.mailfilters/subjects+';
$SPAMRULE_OPTS['subject_passwords']['title'] = _("Subject Passwords");
$SPAMRULE_OPTS['subject_passwords']['anchor'] = FALSE;
$SPAMRULE_OPTS['subject_passwords']['type'] = '';
$SPAMRULE_OPTS['subject_passwords']['header'] = '<p align=center>'.
  '<u>'. _("Subject Passwords"). "</u></p>\n".
  "<div align=left>\n<p>\n".
  _("Here you can create a list of secret keywords that will let email ".
    "go straight past your filters and right into your Inbox.  Any message ".
    "that has one of these phrases in the <b>Subject</b> field will ".
    "<strong><em>always</em></strong> be allowed.  For example, if you ".
    "add <tt>(My Friends)</tt>, then any mail that says &quot;<em>(My ".
    "Friends)</em>&quot; anywhere in the <b>Subject</b> line will make it ".
    "past your spam filters.  You can create more than one secret:  give ".
    "one to your friends and family and another to business associates."). "\n".
  "</p>\n<p>\n".
  _("For example:").
  "<br><ul>\n".
  '<li>'. _("Add <tt>(Friends)</tt> and tell your friends to use that tag in ".
            "the Subject line whenever they send you mail."). "\n".
  '<li>'. _("Create a entry like <tt>short term secret:</tt>, then give it to ".
            "someone that you want to send you mail for just a limited time.").
            "\n".
  '<li>'. _("If you're on a mailing list that tags Subject lines, add that ".
            "tag to your keywords list below."). "\n".
  "</ol>\n".
  "</p></div>\n";
$SPAMRULE_OPTS['subject_passwords']['footer']
 = $SPAMRULE_OPTS['allowed_senders']['footer'];


?>
