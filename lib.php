<?php

/**
 * spamrule/lib.php
 * 
 * The primary function library for the SquirrelMail "spamrule" plugin,
 * which provides users with simple management of email filter groups.
 * 
 * @copyright Copyright (c) 2002-2007 O'Shaughnessy Evans <shaug-sqml @ wumpus.org>
 * @version $Id: lib.php,v 1.15 2007/09/13 20:00:17 shaug Exp $
 * @license http://opensource.org/licenses/artistic-license-2.0.php
 * @package plugins
 * @subpackage spamrule
 */


/*
 * Table of Contents:
 * 
 * function sr_print_eeader($mesg, $columns)
 * function sr_print_footer($mesg, $columns)
 * function sr_select_group()
 * function sr_select_filters()
 * function sr_print_summary($group)
 * function sr_print_subscribed()
 * function sr_install_filters($group, $filters)
 * function sr_load_prefs()
 * function sr_maintain_list($list)
 * function sr_ftp_get($path)
 * function sr_ftp_put($path, $data)
 * function sr_syslog($message)
 * function sr_get_forward()
 * function sr_install_forward($install_forwared)
 */


if (!defined('SM_PATH')) {
    define('SM_PATH', '../../');
}
include_once(SM_PATH . 'functions/imap_general.php'); /* for parseAddress */


/**
 * sr_print_header($mesg, $columns)
 * 
 * Print out the beginning of a table, with $mesg in the first row.
 * 
 * @param string $mesg  text to print out
 * @param int $columns  how many columns will be in the table
 * 
 * Returns:
 *   nothing
 */
function sr_print_header($mesg, $columns)
{
    global $color;

    $title = _("Options") . ' - '. _("Spam Rules");

    echo <<<EOtable_top
<br>
<table bgcolor="{$color[0]}" border="0" width="95%"
       cellspacing="0" cellpadding="1" align="center">
  <tr bgcolor="{$color[0]}">
    <th>{$title}</b></th>
  </tr>
  <tr><td>
    <table bgcolor="{$color[4]}" border="0" width="100%"
           cellspacing="0" cellpadding="5" align="center" valign="top">
      <tr align="center" bgcolor="{$color[4]}">
        <td colspan="$columns">
EOtable_top;

    if (isset($mesg))
        echo "<p>{$mesg}</p>\n";

    echo "</td>\n</tr>\n\n";
}


/**
 * sr_print_footer($mesg, $columns)
 * 
 * Print out the end of a table, with $mesg in the last row.
 * 
 * @param string $mesg  text to print out
 * @param int $columns  how many columns the table has
 * 
 * Returns:
 *   nothing
 */
function sr_print_footer($mesg, $columns)
{
    global $color;

    if (isset($mesg)) {
        echo <<<EOmesg

  <tr align=center bgcolor="{$color[4]}">
    <td colspan="$columns">
      <p>{$mesg}</p>
    </td>
  </tr>
EOmesg;
    }

    echo <<<EOfooter

    </table>
  </td></tr>
</table>

EOfooter;
}


/**
 * sr_select_group()
 * 
 * Read the list of filters that a user has from their squirrelmail preferences,
 * then print out a form allowing them to change the filter group they're
 * using.
 * 
 * Parameters:
 *   none
 * 
 * Returns:
 *   nothing
 */
function sr_select_group()
{
    global $SPAMRULE_OPTS, $color, $javascript_on;
    sqgetGlobalVar('SPAMRULE_OPTS', $SPAMRULE_OPTS, SQ_SESSION);

    $groups = $SPAMRULE_OPTS['groups'];

    // load the saved filters
    list($group, $filters) = sr_load_prefs();
    if (isset($group) && array_key_exists($group, $groups)) {
        $SPAMRULE_OPTS['default_group'] = $group;

        if (isset($filters)) {
            $groups[$group]['filters'] = $filters;
        }
    }
    else {
        $group = $SPAMRULE_OPTS['default_group'];
    }

    // print out the start of the group selection form
    echo <<<EOheader
    <tr align="left" bgcolor="{$color[12]}">
      <th align="left" colspan="3">
        <form action="options.php" method="GET">
EOheader;
    print _("You may choose one of these options:");
    echo <<<EOheader
      </th>
    </tr>
EOheader;

    // print each of the groups in its own table row
    $n = 1;
    foreach ($groups as $g => $settings) {
        $n++;
        $rowcolor = $n % 2 ? $color[12] : $color[4];
        echo "<tr valign=top align=left bgcolor=\"$rowcolor\">\n" .
             "<td><input type=radio name=newgroup value=\"$g\"" .
              ($g == $group ? ' checked' : '' ) . "></td>\n" .
             "<td><b>{$settings['title']}</b></td>\n" .
             "<td>{$settings['text']}</td>\n" .
             "</tr>\n";
    }
    $rowcolor = ($n+1) % 2 ? $color[12] : $color[4];

    // close out the form
    $update_links = '';
    if ($javascript_on) {
        $update_links .= ' <a href="javascript:void(0)" onclick="subwin'.
         '(\'options.php?action=editlist&list=allowed_recipients\','.
         ' \'sr_editlist\', 450, 450)">'. _("Aliases").
         '</a><br>';
        $update_links .= ' <a href="javascript:void(0)" onclick="subwin'.
         '(\'options.php?action=editlist&list=allowed_senders\','.
         ' \'sr_editlist\', 450, 450)">'. _("Allowed Senders").
         '</a><br>';
        $update_links .= ' <a href="javascript:void(0)" onclick="subwin'.
         '(\'options.php?action=editlist&list=blocked_senders\','.
         ' \'sr_editlist\', 450, 450)">'. _("Blocked Senders").
         '</a><br>';
        $update_links .= ' <a href="javascript:void(0)" onclick="subwin'.
         '(\'options.php?action=editlist&list=subject_passwords\','.
         ' \'sr_editlist\', 450, 450)">'. _("Subject Passwords").
         '</a><br>';
        $update_links .= ' <a href="javascript:void(0)" onclick="subwin'.
         '(\'options.php?action=editlist&list=blocked_subjects\','.
         ' \'sr_editlist\', 450, 450)">'. _("Blocked Subjects").
         '</a><br>';
    }
    else {
        $update_links .= ' <a href="options.php?action=editlist&list='.
         'allowed_recipients" target="_blank">'. _("Aliases").
         '</a><br>';
        $update_links .= ' <a href="options.php?action=editlist&list='.
         'allowed_senders" target="_blank">'. _("Allowed senders").
         '</a><br>';
        $update_links .= ' <a href="options.php?action=editlist&list='.
         'blocked_senders" target="_blank">'. _("Blocked senders").
         '</a><br>';
        $update_links .= ' <a href="options.php?action=editlist&list='.
         'subject_passwords" target="_blank">'. _("Subject Passwords").
         '</a><br>';
        $update_links .= ' <a href="options.php?action=editlist&list='.
         'blocked_subjects" target="_blank">'. _("Blocked Subjects").
         '</a><br>';
    }

    $show_label = _("Show my current filters");
    $update_label = _("Update my:");
    $next_label = _("Next");
    echo <<<EOend
    <tr bgcolor="$rowcolor" align="left">
      <td>&nbsp;</td>
    </tr>
    <tr bgcolor="$rowcolor" align="left" valign="top">
      <td colspan="2"> 
        <input type="submit" value="$next_label >"> &nbsp;
        <input type="reset"> &nbsp;
      </td>
      <td>
        <table border="0" cols="3" rows="1" width="100%">
        <tr valign="top">
        <td align="center">
          <a href="options.php?action=subscribed">$show_label</a>
          <input type="hidden" name="action" value="groups">
          </form>
        </td>
        <td align="right"><small>$update_label</small></td>
        <td><small>$update_links</small></td>
        </tr>
        </table>
      </td>
    </tr>
EOend;
}


/**
 * sr_select_filters()
 * 
 * Load the individual filters that a user has saved in their preferences,
 * then allow them to customize the list.
 * 
 * Parameters:
 *   none
 * 
 * Returns:
 *   nothing
 */
function sr_select_filters()
{
    global $SPAMRULE_OPTS, $color;
    sqgetGlobalVar('SPAMRULE_OPTS', $SPAMRULE_OPTS, SQ_SESSION);
    $groups = $SPAMRULE_OPTS['groups'];

    // load the saved filters
    list($group, $filters) = sr_load_prefs();
    if (!isset($filters)) {
        $filters = $groups['CUSTOM']['filters'];
    }

    // print out the beginning of the form
    $col1_label = _("Name");
    $col2_label = _("What Does It Do?");
    echo <<<EOheader
    <tr align="left" valign="bottom" bgcolor="{$color[4]}">
      <th><form action="options.php" method="POST"></th>
      <th><u>$col1_label</u></th>
      <th><u>$col2_label</u></th>
    </tr>
EOheader;

    // print out each filter in its own table row, with a checkbox
    // to enable or disable it
    $n = 0;
    foreach ($SPAMRULE_OPTS['filters'] as $filter => $settings) {
        $n++;
        $rowcolor = $n % 2 ? $color[12] : $color[4];
	// skip filters that have been disabled in the config files
	if (is_null($settings)) {
	    continue;
	}
        echo "  <tr valign=top bgcolor=\"$rowcolor\">\n" .
             "    <td><input type=checkbox " .
              "name=\"SPAMRULE_OPTS[groups][CUSTOM][filters][]\" " .
              "value=\"$filter\"" .
               (in_array($filter, $filters) ? ' checked' : '' ) . "></td>\n" .
             "    <td>{$settings['title']}</td>\n" .
             "    <td>{$settings['text']}</td>\n" .
             "  </tr>\n";
    }

    // close out the form
    $finish_label = _("Finish");
    $reset_label = _("Reset");
    echo <<<EOF
    <tr>
      <td colspan="3"> 
        <br>
        <input type="hidden" name="newgroup" value="CUSTOM">
        <input type="hidden" name="action" value="groups">
        <input type="submit" name="finish" value="$finish_label"> &nbsp; <input type="reset" value="$reset_label">
      </td>
    </tr>
    </form>
EOF;
}


/**
 * sr_print_summary($group)
 * 
 * Print out a summary of the named group
 * 
 * @param string $group  the filter group to be described
 * 
 * Returns:
 *   nothing
 */
function sr_print_summary($group)
{
    global $SPAMRULE_OPTS, $color;
    sqgetGlobalVar('SPAMRULE_OPTS', $SPAMRULE_OPTS, SQ_SESSION);

    echo <<<EOheader
    <tr valign="top" align="left" bgcolor="{$color[12]}">
      <td>&nbsp;</td>
      <td><b><a href="options.php?action=subscribed">{$SPAMRULE_OPTS['groups'][$group]['title']}</a></b>
          </td>
      <td>{$SPAMRULE_OPTS['groups'][$group]['text']}</td>
    </tr>
EOheader;
}


/**
 * sr_print_subscribed()
 * 
 * Load the group and filters to which the user is subscribed, then print
 * a summary of each individual filter.
 * 
 * Parameters:
 *   none
 * 
 * Returns:
 *   nothing
 */
function sr_print_subscribed()
{
    global $SPAMRULE_OPTS, $color, $data_dir, $username;
    sqgetGlobalVar('SPAMRULE_OPTS', $SPAMRULE_OPTS, SQ_SESSION);
    sqgetGlobalVar('username', $username, SQ_SESSION);
    $filter_summary = '';

    // load the saved filters
    $group = getPref($data_dir, $username, 'spamrule_group');
    $filters = explode(',', getPref($data_dir, $username, 'spamrule_filters'));
    $group_title = isset($SPAMRULE_OPTS['groups'][$group]['title'])
                   ? $SPAMRULE_OPTS['groups'][$group]['title']
                   : 'Custom';

    $n = 0;
    foreach ($SPAMRULE_OPTS['filters'] as $filter => $settings) {
        if (! in_array($filter, $filters)) {
            continue;
        }
        $n++;
        $rowcolor = $n % 2 ? $color[12] : $color[4];
        $filter_summary .= <<<EOrow
        <tr valign="top" bgcolor="$rowcolor">
           <td>&nbsp;</td>
           <td><b>{$settings['title']}</b></td>
           <td>{$settings['text']}</td>
        </tr>
EOrow;
    }

    $sel_label = _("You have selected");
    $sel_label2 = _("filters");
    $none_label = _("There is no further information for this group.");
    if ($filter_summary) {
        echo <<<EOheader
    <tr align="left" bgcolor="{$color[4]}">
      <td colspan="3">$sel_label <em>$group_title</em> $sel_label2:
      </td>
    </tr>
EOheader;
        echo $filter_summary;
    }
    else {
        echo <<<EOheader
    <tr align="left" bgcolor="{$color[4]}">
      <td colspan="3">$sel_label <em>$group_title</em> $sel_label2.
      $none_label
      </td>
    </tr>
EOheader;
    }
}


/**
 * sr_install_filters($group, $filters)
 * 
 * Save the user's groups and filters to their squirrelmail prefs, then
 * FTP them to the user's account
 * 
 * @param string $group   the name of the group to save & install
 * @param array $filters  the individual filters that are a part of that group
 * 
 * Returns:
 *   nothing
 */
function sr_install_filters($group, $filters)
{
    global $SPAMRULE_OPTS, $SPAMRULE_INSTALL, $data_dir, $username;
    sqgetGlobalVar('SPAMRULE_OPTS', $SPAMRULE_OPTS, SQ_SESSION);
    sqgetGlobalVar('SPAMRULE_INSTALL', $SPAMRULE_INSTALL, SQ_SESSION);
    sqgetGlobalVar('username', $username, SQ_SESSION);

    $cmd = '';            // for building up the special (un)install commands
    $last = '';           // will hold the last line of output from exec cmds
    $results[] = array(); // will hold the output of exec commands
    $status = 0;          // will hold the status of exec and ftp commands
    $forward_has_filter = TRUE; // will indicate whether or not to install
                          // the filtering command in the forward file

    // save the group and filters to the squirrelmail prefs
    setPref($data_dir, $username, 'spamrule_group', $group);
    setPref($data_dir, $username, 'spamrule_filters', implode(',', $filters));

    // Look through the list of filters we know about.  If the user is
    // installing one that has a unique 'install' setting, run it.
    // If it user didn't select the given filter and it has an 'uninstall'
    // setting, run that.
    foreach ($SPAMRULE_OPTS['filters'] as $filter => $settings) {
        // the filter was chosen by the user
        $key = array_search($filter, $filters);
        if ($key !== FALSE) {
            // the filter has a special 'install' command
            if (isset($settings['install'])) {
                // create the system command
                $cmd = escapeshellcmd($settings['install']);
                $cmd = $settings['install'];
                $last = exec("$cmd 2>&1 &", $results, $status);

                // remove the filter name from the list to add via the
                // general install command
                unset($filters[$key]);
            }
        }
        // the filter was not chosen by the user
        else {
            // the filter has a special 'uninstall' command
            if (isset($settings['uninstall'])) {
                $cmd = escapeshellcmd($settings['uninstall']);
                $last = exec("$cmd 2>&1 &", $results, $status);
                if ($status != 0) {
                    sr_syslog('error installing filters for '. $username . 
                              ":  \"$cmd\" exited $status; output was \"".
                              implode(';', $results). '"');
                }
            }
        }
    }

    // If the option $SPAMRULE_INSTALL['command'] is set, use that
    // to install the filters.  Otherwise, upload them via FTP.
    if (isset($SPAMRULE_INSTALL['command'])) {
        $cmd = sprintf($SPAMRULE_INSTALL['command'], implode(' ', $filters));
        $last = exec(escapeshellcmd($cmd). " 2>&1 &", $results, $status);
        if ($status != 0) {
            sr_syslog("error installing filters for $username:  \"$cmd\" ".
                      "exited $status; output was \"". implode(';', $results).
                      '"');
        }
    }
    else if ($filters) {
        // create a file with the individual filters
        $data = "# mail filter file for $username\n".
                "# created by the Spam Rules SquirrelMail plugin, version ".
                spamrule_version(). "\n".
                "# on ". date('D, F j, Y, g:i a T'). "\n";
        foreach ($filters as $f) {
            $data .= sprintf($SPAMRULE_INSTALL['filter_string'], $f);
        }
    }
    else {
        // There's no filter being installed, which means the user is removing
        // himself from all the filters.  So, we want to be sure to remove
        // (or at least not install) the mail forwarding command.
        $forward_has_filter = FALSE;
    }

    // upload the filter file via FTP
    list($status, $results) = sr_ftp_put($SPAMRULE_INSTALL['filter_file'],
                                         $data);
    if ($status != TRUE) {
        sr_syslog($results);
        $err_label1 = _("Whoops!  There was trouble installing your filters.");
        $err_label2 = _("Here's the message:");
        echo <<<EOerror
  <p>
  <strong>$err_label1</strong><br>
  $err_label2<br><font color="red"><pre>$results</pre></font>
  </p>

EOerror;
        return;
    }

    // upload the forward file via FTP
    sr_syslog("forward_has_filter is $forward_has_filter");
    list($status, $results) = sr_install_forward($forward_has_filter);
    if ($status != TRUE) {
        sr_syslog($results);
        echo <<<EOerror
  <p>
  <strong>$err_label1</strong><br>
  $err_label2<br><font color="red"><pre>$results</pre></font>
  </p>

EOerror;
        return;
    }
}


/**
 * sr_load_prefs()
 * 
 * Loads all the prefs that the plugin uses and returns them in an array.
 * 
 * Parameters:
 *   none
 * 
 * @returns array (string $group, array $filters) containing the user's selected filter group and an array of filter names if the group is "CUSTOM"
 */
function sr_load_prefs()
{
    global $data_dir, $username;
    sqgetGlobalVar('username', $username, SQ_SESSION);

    $group = getPref($data_dir, $username, 'spamrule_group');
    $filters = explode(',', getPref($data_dir, $username, 'spamrule_filters'));

    return array($group, $filters);
}


/**
 * sr_maintain_list($list)
 * 
 * Create a popup window that presents a list of entries in a file and
 * allows the user to add a new entry or delete any from the list.
 * 
 * The way in which $list's contents are manipulated can be changed through
 * $SPAMRULE_OPTS[$list].
 * 
 * @param string $list  giving the name of the list to update; it should be defined in $SPAMRULE_OPTS[$list]
 * 
 * Returns:
 *   nothing
 */
function sr_maintain_list($list)
{
    global $SPAMRULE_OPTS, $color;
    sqgetGlobalVar('SPAMRULE_OPTS', $SPAMRULE_OPTS, SQ_SESSION);
    $ftpdata = '';
    $ignored = array();
    $patterns = array();
    $status = FALSE;

    // have any changes been made to the list of patterns we want to save?
    $change = FALSE;

    // are we maintaining a list of email addresses?
    $is_email = $SPAMRULE_OPTS[$list]['type'] == 'email' ? TRUE : FALSE;

    // get the list of current patterns, which will be modified as
    // appropriate by our lists of patterns to add and delete
    list($status, $ftpdata) = sr_ftp_get($SPAMRULE_OPTS[$list]['file']);
    if ($status === FALSE) {
        print _("Sorry, there was a problem downloading your list:").
	      '  '. $ftpdata;
        return;
    }

    // get rid of duplicate patterns, make sure there are no empty ones,
    // then get rid of regex escapes and anchors we added when saving
    if (!empty($ftpdata)) {
        $patterns = array_unique($ftpdata);
        foreach ($patterns as $k => $v) {
            $orig = $v;
            $v = trim($v);
            if (!$v) {
                unset($patterns[$k]);
                $change = TRUE;
            }
            else {
                // get rid of the anchors and regex character quoting we keep
                // in the pattern file
                $v = ereg_replace('^\^', '', $v);
                $v = ereg_replace('\$$', '', $v);
                $v = ereg_replace('\\\\', '', $v);
                $v = preg_replace('/\\\([.\\+*?\[\]^$(){}\!<>|:])/', '$1', $v);

                // remove surrounding quotes
                if (ereg('^"[^"]+"$', $v)) {
                    $v = ereg_replace('^"', '', $v);
                    $v = ereg_replace('"$', '', $v);
                }
                if (ereg('^\'[^\']+\'$', $v)) {
                    $v = ereg_replace('^\'', '', $v);
                    $v = ereg_replace('\'$', '', $v);
                }

                if ($is_email) {
                    // now that we've removed our own garbage, extract the
                    // actual email address (just in case the user put
                    // something funky in there, like a comment field or
                    // just a username
                    $addr = parseAddress($v, 1);
                    //sr_syslog("spamrule: address from ($v) is (".$addr[0][0].")");
                    $v = $addr[0][0];

                    // if the address doesn't have @ or ., it's not a domain
                    // or a mailbox, so we should drop it.
                    if (!ereg('[@.]', $v)) {
                        sr_syslog("spamrule: ignoring \"$orig\": not an email ".
                                  "address");
                        array_push($ignored, $orig);
                        $v = '';
                    }
                }

                // trim again
                $v = trim($v);
                if (!$v) {
                    unset($patterns[$k]);
                    $change = TRUE;
                }
                else {
                    $patterns[$k] = $v;
                }
            }
        }
    }

    // get the requested changes
    sqgetGlobalVar('sr_del_patterns', $delpats, SQ_FORM);
    sqgetGlobalVar('sr_new_pattern', $newpat, SQ_FORM);

    // clean up the list of patterns to remove:  skip any that are
    // empty or just whitespace, then quote any regular expression
    // characters to disable regex pattern matching by our filter program
    // (since most users won't know anything about regexes, they'll just
    // add confusion) and add the regex anchors
    if (isset($delpats) && $delpats) {
        foreach ($delpats as $k => $v) {
            $v = trim($v);
            if (!$v) {
                unset($delpats[$k]);
                $change = TRUE;
            }
            else {
                $delpats[$k] = $v;
            }
        }
        $patterns = array_diff($patterns, $delpats);
        $change = TRUE;
    }

    // add the new pattern to our list
    if (isset($newpat) && $newpat) {
        $newpat = trim($newpat);
        array_push($patterns, $newpat);
        $change = TRUE;
    }

    // save the new file if it has changed
    if ($change) {
        $data = '';
        foreach ($patterns as $v) {
            // escape any regular expression patterns, because regexes
            // tend to be very confusing if you don't know about them
            $v = preg_quote($v);

            // anchor the pattern:  if it's complete, anchor to the
            // beginning and end; if it's just a username (user@)
            // or domain (@domain), then just anchor the appropriate side
            if ($SPAMRULE_OPTS[$list]['anchor'] && strpos($v, '@') !== FALSE) {
                if (! ereg('^@', $v)) {
                    $v = '^'. $v;
                }
                if (! ereg('@$', $v)) {
                    $v .= '$';
                }
            }
            $data .= $v. "\n";
        }

        list($status, $ftpmesg) = sr_ftp_put($SPAMRULE_OPTS[$list]['file'],
                                             $data);
        if ($status != TRUE) {
            sr_syslog($ftpmesg);
        }
    }

    // if we ignored any entries in the file, warn the user about them.
    if (!empty($ignored)) {
        $ignored_mesg = implode('</tt></li><li><tt>', $ignored);
        $warning_msg = _("Warning:  We found some entries in your list that ".
                         "don't look like email addresses.  In the future, ".
                         "please include just the &quot;user@domain&quot; ".
			 "part of an email address, without any extra ".
			 "comments, real name, or quotes.  These have been ".
			 "ignored:");
        echo <<<EOwarning

<tr valign="top" bgcolor="$color[12]"><td colspan="3">
<p><font color="$color[2]"<i>$warning_mesg</i></font>
<ul><li><tt>$ignored_mesg
</ul>
</p></td></tr>
EOwarning;
    }
    
    // print out the rest of the form w/a table of the current patterns
    // and checkboxes to remove any of them
    echo "<form action=\"options.php?action=editlist&list=$list\" ".
         "method=POST>\n";
    if ($patterns) {
        $remove_label = _("Remove?");
        $addr_label = _("Address");
        echo <<<EOformtop

  <tr valign="top" bgcolor="$color[4]">
    <td width="5%">&nbsp;</td>
    <td width="5%"><u>$remove_label</u></td>
    <td><u>$addr_label</u></td>
  </tr>

EOformtop;
    }

    $n = 0;
    foreach ($patterns as $key => $val) {
        $n++;
        $rowcolor = $n % 2 ? $color[12] : $color[4];
        echo <<<EOrow

        <tr valign="middle" align="left" bgcolor="$rowcolor">
           <td>$n.</td>
           <td><input type="checkbox" name="sr_del_patterns[]" value="$val"></td>
           <td><tt>$val</tt></td>
        </tr>
EOrow;
    }

    $n++;
    $rowcolor = $n % 2 ? $color[12] : $color[4]; 
    $default = $patterns
               ? ''
	       : isset($SPAMRULE_OPTS[$list]['default'])
                 ? $SPAMRULE_OPTS[$list]['default']
		 : '';
    $add_label = _("Add a new entry:");
    $update_label = _("Update");
    echo <<<EOform
  <tr valign="top" bgcolor="$rowcolor">
    <td colspan="3"><br><p>$add_label
      <input type="text" name="sr_new_pattern" size="50" value="$default">
      </input></p>
    </td>
  </tr>

  <tr>
    <td colspan="3"> 
      <br>
      <input type="submit" value="$update_label"> &nbsp; <input type="reset">
    </td>
  </tr>
  </form>
EOform;
}


/**
 * sr_ftp_get($path)
 * 
 * Downloads the given list name from the user's account on
 * $SPAMRULE_OPTS['ftphost'] and returns the contents as an array.
 * 
 * @param string $path  path to the file to be downloaded via FTP
 * 
 * @returns array (boolean $status, string $info)
 *   2-element array:
 *   On success, the 1st element will be TRUE and the 2nd will contain a
 *   string with the contents of the requested file.
 *   On failure, the 1st element will be FALSE and the 2nd will contain
 *   an error message describing the problem.
 */
function sr_ftp_get($path)
{
    global $SPAMRULE_OPTS, $key, $onetimepad, $username;
    sqgetGlobalVar('SPAMRULE_OPTS', $SPAMRULE_OPTS, SQ_SESSION);
    sqgetGlobalVar('key', $key, SQ_COOKIE);
    sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);
    sqgetGlobalVar('username', $username, SQ_SESSION);

    $ftphost = $SPAMRULE_OPTS['ftphost'];
    if (!$ftphost) {
        return array(FALSE, _("Sorry, but this plugin is not completely set ".
	                      "up.  Please contact your support department ".
			      "about configuring the Spam Rules plugin."));
    }
    if (!$path) {
        return array(FALSE, _("no file was given to upload"));
    }

    // decrypt the user's password so we can pass it to the ftp site
    // (borrowed from the vacation plugin; thanks!)
    $password = OneTimePadDecrypt($key, $onetimepad);

    // upload the file to the user's home
    $ftp = @ftp_connect($ftphost);
    if ($ftp === FALSE) {
        return array(FALSE, _("cannot connect to"). " $ftphost".
                            (isset($php_errormsg) ? ": $php_errormsg" : ''));
    }

    $status = @ftp_login($ftp, $username, $password);
    if ($status === FALSE) {
        return array(FALSE, _("cannot log in to"). " $ftphost".
                            (isset($php_errormsg) ? ": $php_errormsg" : ''));
    }

    if (@ftp_chdir($ftp, dirname($path)) === FALSE) {
        return array(FALSE, '');
    }

    $file = basename($path);
    if (! @ftp_size($ftp, $file) === -1) {
        return array(FALSE, _("cannot read"). " $file".
                            (isset($php_errormsg) ? ": $php_errormsg" : ''));
    }

    // create a local temp file to store the rules
    $temp = tmpfile();
    $status = @ftp_fget($ftp, $temp, $file, FTP_ASCII);
    ftp_close($ftp);

    // put each line of the temp file into an array
    rewind($temp);
    $data = array();
    while (fstat($temp) && !feof($temp)) {
        array_push($data, trim(fgets($temp)));
    }
    fclose($temp);
    return array(TRUE, $data);
}


/**
 * sr_ftp_put($path, $data)
 * 
 * Uploads the text in $data to $path in the user's account on 
 * $SPAMRULE_OPTS['ftphost'].  Any directories in the path that don't
 * exist will be created.
 * 
 * @param string $path  the file to be modified
 * @param string $data  the data to be written to $path; if it's an empty string, $path will be removed
 * 
 * @returns array (boolean $status, string $info)
 *   a 2-part array, with the first part indicating success (TRUE) or failure
 *   (FALSE) and the second part containing an error message in the case of
 *   failure
 */
function sr_ftp_put($path, $data)
{
    global $SPAMRULE_OPTS, $key, $onetimepad, $username;
    sqgetGlobalVar('SPAMRULE_OPTS', $SPAMRULE_OPTS, SQ_SESSION);
    sqgetGlobalVar('key', $key, SQ_COOKIE);
    sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);
    sqgetGlobalVar('username', $username, SQ_SESSION);

    $ftphost = $SPAMRULE_OPTS['ftphost'];
    if (!$ftphost) {
        return array(FALSE, _("Sorry, but this plugin is not completely set ".
	                      "up.  Please contact your support department ".
			      "about configuring the Spam Rules plugin."));
    }

    if (empty($path)) {
        return array(FALSE, _("no file was given to upload"));
    }

    // decrypt the user's password so we can pass it to the ftp site
    // (borrowed from the vacation plugin; thanks!)
    $password = OneTimePadDecrypt($key, $onetimepad);

    // upload the file to the user's home
    $ftp = ftp_connect($ftphost);
    if (!$ftp) {
        return array(FALSE, _("cannot connect to"). " $ftphost".
                            (isset($php_errormsg) ? ": $php_errormsg" : ''));
    }

    $status = ftp_login($ftp, $username, $password);
    if (!$status) {
        return array(FALSE, _("cannot log in to"). " $ftphost".
                            (isset($php_errormsg) ? ": $php_errormsg" : ''));
    }

    // if there is data to upload, write it to a temporary file and upload
    // its contents
    if ($data) {
        // create a file w/the new rules
        $temp = tmpfile();
        fwrite($temp, $data);
        rewind($temp);

        // search the path to the file and create each parent dir if it
        // doesn't exist
        $dir = dirname($path);
        $dirs = preg_split('/[\\/\\\]+/', $dir, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($dirs as $d) {
            if ($d == '.') {
                continue;
            }
            if ($d == '/') {
                continue;
            }
            if (! @ftp_chdir($ftp, $d)) {
                if (!(@ftp_mkdir($ftp, $d) && @ftp_site($ftp, "chmod 0700 $d")
                     && @ftp_chdir($ftp, $d))) {
                    @ftp_close($ftp);
                    return array(FALSE, _("error creating"). " $dir".
                               (isset($php_errormsg) ? ": $php_errormsg" : ''));
                }
            }
        }
        $cwd = ftp_pwd($ftp);
        /*
        if ($cwd != "/" and $cwd != $dir and $cwd != "/$dir") {
            return array(FALSE, "error creating $dir (could only get to ".
                            "$cwd): $php_errormsg");
        }
        */

        $file = basename($path);
        $status = ftp_fput($ftp, $file, $temp, FTP_ASCII);
        if (!$status) {
            @ftp_close($ftp);
            return array(FALSE, _("error changing"). " $cwd/$file".
                               (isset($php_errormsg) ? ": $php_errormsg" : ''));
        }
        @ftp_site($ftp, "chmod 0600 $file");
        fclose($temp);
    }
    // otherwise delete the remote file (we don't want to leave an empty file)
    else {
        if (@ftp_size($ftp, $path) > 0) {
            $status = @ftp_delete($ftp, $path);
            if (!$status) {
                @ftp_close($ftp);
                return array(FALSE, _("error removing"). " $path".
                               (isset($php_errormsg) ? ": $php_errormsg" : ''));
            }
        }
    }

    @ftp_close($ftp);

    return array(TRUE, NULL);
}


/**
 * sr_syslog($message)
 * 
 * Write a message to the syslog.
 * 
 * @param string $mesg  text to send
 * 
 * Returns:
 *   nothing
 */
function sr_syslog($message)
{
    define_syslog_variables();
    openlog('spamrule', LOG_NDELAY, LOG_DAEMON);
    syslog(LOG_ERR, $message);
    closelog();
}


/**
 * sr_install_forward($install_filter)
 * 
 * Install the filtering command into the user's forward file via FTP.
 * If the forward file already has a line matching
 * $SPAMRULE_INSTALL['forward_pattern'], then the file won't be changed.
 * If it has a line matching $SPAMRULE_INSTALL['inbox_pattern'], then that
 * will be replaced with $SPAMRULE_INSTALL['forward_string'].  If neither
 * is found, the file will be appended with a brief comment and the
 * 'forward_string'.
 * 
 * @param boolean $install_filter  indicating whether the new forward file should add the filter command or not
 * 
 * @returns array (boolean $status, string $info)  as returned from sr_ftp_put()
 *    a 2-part array, with the first part indicating success (TRUE) or failure
 *    (FALSE) and the second part containing an error message in the case of
 *    failure
 */
function sr_install_forward($install_filter)
{
    global $SPAMRULE_INSTALL;
    sqgetGlobalVar('SPAMRULE_INSTALL', $SPAMRULE_INSTALL, SQ_SESSION);

    $changed = FALSE;
    $data = '';
    $ftpdata = '';
    $forward = '';
    $has_filter = FALSE;
    $status = FALSE;

    // download and install the forward file
    list($status, $ftpdata) = sr_ftp_get($SPAMRULE_INSTALL['forward_file']);
    if ($status === FALSE) {
        print _("Sorry, there was a problem downloading your forward file:").
	      '  '. $ftpdata;
        return;
    }

    // search through the forward file and look for an entry matching
    // the filter string or explicit storage to the local inbox.
    if (!empty($ftpdata)) {
	$forward = $ftpdata;
        reset($forward);
        while (list($i, $f) = each($forward)) {
            //$f = trim($f);
            // skip if the line is empty
            if (!$f) {
                continue;
            }

            // if the forward file has our filtering string pattern, we
            // won't need to add it.
            if (ereg($SPAMRULE_INSTALL['forward_pattern'], $f)) {
                $has_filter = TRUE;
                // but if we don't want it there any more, we should remove it
                if (!$install_filter) {
                    unset($forward[$i]);
                    $changed = TRUE;
                }
            }
            // if the forward file saves to the local Incoming mailbox, we
            // want to change that to run through the filter instead.
            if ($install_filter && ereg($SPAMRULE_INSTALL['inbox_pattern'], $f))
            {
                $forward[$i] = $SPAMRULE_INSTALL['forward_string'];
                $has_filter = TRUE;
                $changed = TRUE;
            }
        }

        // convert the forward file contents from an array into a string
        $data = $forward ? implode("\n", $forward) : '';
    }

    // the filter wasn't found in the file, so we need to append it
    if ($install_filter && !$has_filter) {
        $data .= $SPAMRULE_INSTALL['forward_string']. "\n";
        $changed = TRUE;
    }

    // save the new file if we must
    if ($changed) {
        return sr_ftp_put($SPAMRULE_INSTALL['forward_file'], $data);
    }
    else {
        return array(TRUE, NULL);
    }
}
