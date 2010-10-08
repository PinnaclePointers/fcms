<?php
include_once('util_inc.php');
include_once('locale.php');

/**
 * AdminMembers 
 * 
 * @package     Family Connections
 * @copyright   Copyright (c) 2010 Haudenschilt LLC
 * @author      Ryan Haudenschilt <r.haudenschilt@gmail.com> 
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 */
class AdminMembers
{

    var $db;

    /**
     * AdminMembers 
     * 
     * @param   database    $database 
     * @return  void
     */
    function AdminMembers ($database)
    {
        $this->db = $database;
        bindtextdomain('messages', '.././language');
    }
    
    /**
     * getUsersEmail 
     * 
     * @param   int     $id 
     * @return  void
     */
    function getUsersEmail ($id)
    {
        $sql = "SELECT `email` FROM `fcms_users` WHERE `id` = $id";
        $this->db->query($sql) or displaySQLError(
            'Email Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error()
            );
        $r = $this->db->get_row();
        return $r['email'];
    }
    
    /**
     * displayCreateMemberForm 
     * 
     * @param   string $error 
     * @return  void
     */
    function displayCreateMemberForm ($error = '')
    {
        $locale = new Locale();

        $username   =   isset($_POST['username'])   ?   $_POST['username']  :   '';
        $fname      =   isset($_POST['fname'])      ?   $_POST['fname']     :   '';
        $lname      =   isset($_POST['lname'])      ?   $_POST['lname']     :   '';
        $email      =   isset($_POST['email'])      ?   $_POST['email']     :   '';
        $year       =   isset($_POST['year'])       ?   $_POST['year']      :   date('Y');
        $month      =   isset($_POST['month'])      ?   $_POST['month']     :   date('m');
        $day        =   isset($_POST['day'])        ?   $_POST['day']       :   date('d');

        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = $i;
        }
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = $locale->getMonthAbbr($i);
        }
        for ($i = 1900; $i <= date('Y')+5; $i++) {
            $years[$i] = $i;
        }

        // Display applicable errors
        if ($error != '') {
            echo '
            <p class="error-alert">'.$error.'</p>';
        }
        
        // Display the form
        echo '
            <fieldset>
                <legend><span>'.T_('Create New Member').'</span></legend>
                <form method="post" action="members.php">
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="username"><b>'.T_('Username').'</b></label></div> 
                        <div class="field-widget">
                            <input type="text" name="username" id="username" value="'.$username.'" size="25"/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        var funame = new LiveValidation(\'username\', { onlyOnSubmit: true });
                        funame.add(Validate.Presence, {failureMessage: ""});
                    </script>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="password"><b>'.T_('Password').'</b></label></div>
                        <div class="field-widget">
                            <input type="password" name="password" id="password" size="25"/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        var fpass = new LiveValidation(\'password\', { onlyOnSubmit: true });
                        fpass.add(Validate.Presence, {failureMessage: ""});
                    </script>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="fname"><b>'.T_('First Name').'</b></label></div> 
                        <div class="field-widget">
                            <input type="text" name="fname" id="fname" value="'.cleanOutput($fname).'" size="50"/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        var ffname = new LiveValidation(\'fname\', { onlyOnSubmit: true });
                        ffname.add(Validate.Presence, {failureMessage: ""});
                    </script>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="lname"><b>'.T_('Last Name').'</b></label></div> 
                        <div class="field-widget">
                            <input type="text" name="lname" id="lname" value="'.cleanOutput($lname).'" size="50"/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        var flname = new LiveValidation(\'lname\', { onlyOnSubmit: true });
                        flname.add(Validate.Presence, {failureMessage: ""});
                    </script>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="email"><b>'.T_('Email').'</b></label></div> 
                        <div class="field-widget">
                            <input type="text" name="email" id="email" value="'.cleanOutput($email).'" size="50"/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        var femail = new LiveValidation(\'email\', { onlyOnSubmit: true });
                        femail.add( Validate.Presence, { failureMessage: "'.T_('Sorry, but this information is Required.').'" } );
                        femail.add( Validate.Email, { failureMessage: "'.T_('That\'s not a valid email address is it?').'" } );
                        femail.add( Validate.Length, { minimum: 10 } );
                    </script>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="day"><b>'.T_('Birthday').'</b></label></div> 
                        <div class="field-widget">
                            <select id="day" name="day">
                                '.buildHtmlSelectOptions($days, $day).'
                            </select>
                            <select id="month" name="month">
                                '.buildHtmlSelectOptions($months, $month).'
                            </select>
                            <select id="year" name="year">
                                '.buildHtmlSelectOptions($years, $year).'
                            </select>
                        </div>
                    </div>
                    <p>
                        <input class="sub1" type="submit" id="create" name="create" value="'.T_('Create').'"/> '.T_('or').' &nbsp;
                        <a href="members.php">'.T_('Cancel').'</a>
                    </p>
                </form>
            </fieldset>';
    }

    /**
     * displayEditMemberForm 
     * 
     * @param   int     $id 
     * @param   string  $error 
     * @return  void
     */
    function displayEditMemberForm ($id, $error = '')
    {
        $locale = new Locale();

        $member = cleanInput($id, 'int');

        $sql = "SELECT `id`, `username`, `fname`, `lname`, `email`, `birthday`, `access` 
                FROM `fcms_users` 
                WHERE `id` = '$member'";
        if (!$this->db->query($sql)) {
            displaySQLError('Member Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            return;
        }
        $r = $this->db->get_row();
        
        // Display applicable errors
        if ($error != '') {
            echo '
            <p class="error-alert">'.$error.'</p>';
        }

        $id       = isset($_POST['id'])       ? $_POST['id']       : $r['id'];
        $username = isset($_POST['username']) ? $_POST['username'] : $r['username'];
        $fname    = isset($_POST['fname'])    ? $_POST['fname']    : $r['fname'];
        $lname    = isset($_POST['lname'])    ? $_POST['lname']    : $r['lname'];
        $email    = isset($_POST['email'])    ? $_POST['email']    : $r['email'];
        $year     = isset($_POST['year'])     ? $_POST['year']     : substr($r['birthday'], 0, 4);
        $month    = isset($_POST['month'])    ? $_POST['month']    : substr($r['birthday'], 5, 2);
        $day      = isset($_POST['day'])      ? $_POST['day']      : substr($r['birthday'], 8, 2);
        $access   = isset($_POST['access'])   ? $_POST['access']   : $r['access'];

        for ($i = 1; $i <= 31; $i++) {
            $days[$i] = $i;
        }
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = $locale->getMonthAbbr($i);
        }
        for ($i = 1900; $i <= date('Y')+5; $i++) {
            $years[$i] = $i;
        }

        // Display the form
        echo '
            <fieldset>
                <legend><span>'.T_('Edit Member').'</span></legend>
                <form method="post" action="members.php">
                    <a class="merge" href="?merge='.$member.'">'.T_('Merge').'</a>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="username"><b>'.T_('Username').'</b></label></div> 
                        <div class="field-widget">
                            <input type="text" name="username" id="username" disabled="disabled" value="'.$username.'" size="25"/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        var funame = new LiveValidation(\'username\', { onlyOnSubmit: true });
                        funame.add(Validate.Presence, {failureMessage: ""});
                    </script>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="password"><b>'.T_('Password').'</b></label></div>
                        <div class="field-widget">
                            <input type="password" name="password" id="password" size="25"/>
                        </div>
                    </div>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="fname"><b>'.T_('First Name').'</b></label></div> 
                        <div class="field-widget">
                            <input type="text" name="fname" id="fname" value="'.cleanOutput($fname).'" size="50"/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        var ffname = new LiveValidation(\'fname\', { onlyOnSubmit: true });
                        ffname.add(Validate.Presence, {failureMessage: ""});
                    </script>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="lname"><b>'.T_('Last Name').'</b></label></div> 
                        <div class="field-widget">
                            <input type="text" name="lname" id="lname" value="'.cleanOutput($lname).'" size="50"/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        var flname = new LiveValidation(\'lname\', { onlyOnSubmit: true });
                        flname.add(Validate.Presence, {failureMessage: ""});
                    </script>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="email"><b>'.T_('Email').'</b></label></div> 
                        <div class="field-widget">
                            <input type="text" name="email" id="email" value="'.cleanOutput($email).'" size="50"/>
                        </div>
                    </div>
                    <script type="text/javascript">
                        var femail = new LiveValidation(\'email\', { onlyOnSubmit: true });
                        femail.add( Validate.Presence, { failureMessage: "'.T_('Sorry, but this information is Required.').'" } );
                        femail.add( Validate.Email, { failureMessage: "'.T_('That\'s not a valid email address is it?').'" } );
                        femail.add( Validate.Length, { minimum: 10 } );
                    </script>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="day"><b>'.T_('Birthday').'</b></label></div> 
                        <div class="field-widget">
                            <select id="day" name="day">
                                '.buildHtmlSelectOptions($days, $day).'
                            </select>
                            <select id="month" name="month">
                                '.buildHtmlSelectOptions($months, $month).'
                            </select>
                            <select id="year" name="year">
                                '.buildHtmlSelectOptions($years, $year).'
                            </select>
                        </div>
                    </div>
                    <div class="field-row clearfix">
                        <div class="field-label"><label for="access"><b>'.T_('Access Level').'</b></label></div> 
                        <div class="field-widget">
                            <select id="access" name="access">
                                <option value="1"';
            if ($access == 1) {
                echo " selected=\"selected\"";
            }
            echo ">1. " . T_('Admin') . "</option><option value=\"2\"";
            if ($access == 2) {
                echo " selected=\"selected\"";
            }
            echo ">2. " . T_('Helper') . "</option><option value=\"3\"";
            if ($access == 3) {
                echo " selected=\"selected\"";
            }
            echo ">3. " . T_('Member') . "</option>";
            echo "<option value=\"" . (int)$access . "\"></option>";
            echo "<option value=\"" . (int)$access . "\">" . T_('Advanced Options')
                . "</option>";
            echo "<option value=\"" . (int)$access . "\">"
                . "-------------------------------------</option>";
            echo "<option value=\"4\"";
            if ($access == 4) {
                echo " selected=\"selected\"";
            }
            echo ">4. " . T_('Non-Photographer') . "</option><option value=\"5\"";
            if ($access == 5) {
                echo " selected=\"selected\"";
            }
            echo ">5. " . T_('Non-Poster') . "</option><option value=\"6\"";
            if ($access == 6) {
                echo " selected=\"selected\"";
            }
            echo ">6. " . T_('Commenter') . "</option><option value=\"7\"";
            if ($access == 7) {
                echo " selected=\"selected\"";
            }
            echo ">7. " . T_('Poster') . "</option><option value=\"8\"";
            if ($access == 8) {
                echo " selected=\"selected\"";
            }
            echo ">8. " . T_('Photographer') . "</option><option value=\"9\"";
            if ($access == 9) {
                echo " selected=\"selected\"";
            }
            echo ">9. " . T_('Blogger') . "</option><option value=\"10\"";
            if ($access == 10) {
                echo " selected=\"selected\"";
            }
            echo '>10. '.T_('Guest').'</option>
                            </select>
                        </div>
                    </div>
                    <p>
                        <input type="hidden" id="id" name="id" value="'.(int)$id.'"/>
                        <input class="sub1" type="submit" id="edit" name="edit" value="'.T_('Edit').'"/>&nbsp;&nbsp;
                        <input class="sub2" type="submit" id="delete" name="delete" value="'.T_('Delete').'"/> '.T_('or').' &nbsp;
                        <a class="u" href="members.php">'.T_('Cancel').'</a>
                    </p>
                </form>
            </fieldset>';
    }

    /**
     * displayMergeMemberForm 
     * 
     * @param int $id
     * 
     * @return void
     */
    function displayMergeMemberForm ($id)
    {
        global $cfg_mysql_host, $cfg_mysql_db, $cfg_mysql_user, $cfg_mysql_pass;
        $db2 = new database('mysql', $cfg_mysql_host, $cfg_mysql_db, $cfg_mysql_user, $cfg_mysql_pass);

        $id     = cleanInput($id, 'int');

        // Get current member info
        $sql = "SELECT u.`id`, u.`username`, u.`fname`, u.`lname`, u.`email`, u.`birthday`,
                    a.`address`, a.`city`, a.`state`, a.`zip`, a.`home`, a.`work`, a.`cell`
                FROM `fcms_users` AS u, `fcms_address` AS a
                WHERE u.`id` = '$id'
                AND u.`id` = a.`user`";
        if (!$this->db->query($sql)) {
            displaySQLError('Member Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            return;
        }
        $r = $this->db->get_row();

        // Get member list
        $sql = "SELECT `id`, `username`, `password`,`fname`, `lname`
                FROM `fcms_users` 
                WHERE `id` != '$id'";
        if (!$db2->query($sql)) {
            displaySQLError('Members Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            return;
        }
        $members = array();
        while ($row = $db2->get_row()) {
            if ($row['password'] == 'NONMEMBER') {
                $members[$row['id']] = $row['lname'].', '.$row['fname'].' ('.T_('Non-member').')';
                continue;
            }

            $members[$row['id']] = $row['lname'].', '.$row['fname'].' ('.$row['username'].')';
        }
        asort($members);

        // Display the form
        echo '
            <fieldset>
                <legend><span>'.T_('Merge Member').'</span></legend>
                <form method="post" id="merge-form" action="members.php">
                    <div id="current-member">
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('ID').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['id'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Username').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['username'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Name').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['fname'].' '.$r['lname'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Email').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['email'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Birthday').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['birthday'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Address').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['address'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('City').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['city'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('State').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['state'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Zip').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['zip'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Home Phone').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['home'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Work Phone').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['work'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Cell Phone').'</b></label>
                            </div> 
                            <div class="field-widget">'.$r['cell'].'</div>
                        </div>
                    </div>
                    <div id="merge-member">
                        <p>
                            <b>'.T_('Member to merge with').'</b><br/>
                            <select id="merge-with" name="merge-with">
                                <option value="0"></option>
                                '.buildHtmlSelectOptions($members, -1).'
                            </select>
                        </p>
                    </div>
                    <div class="clearfix"></div>
                    <p>
                        <input type="hidden" id="id" name="id" value="'.(int)$id.'"/>
                        <input class="sub1" type="submit" id="merge-review" name="merge-review" value="'.T_('Merge').'"/> '.T_('or').' &nbsp;
                        <a class="u" href="members.php">'.T_('Cancel').'</a>
                    </p>
                </form>
            </fieldset>';
    }

    /**
     * displayMergeMemberFormReview
     * 
     * @param int $id 
     * @param int $merge
     * 
     * @return void
     */
    function displayMergeMemberFormReview ($id, $merge)
    {
        $id     = cleanInput($id, 'int');
        $merge  = cleanInput($merge, 'int');

        $sql = "SELECT u.`id`, u.`username`, u.`fname`, u.`lname`, u.`email`, u.`birthday`,
                    a.`address`, a.`city`, a.`state`, a.`zip`, a.`home`, a.`work`, a.`cell`
                FROM `fcms_users` AS u, `fcms_address` AS a
                WHERE u.`id` IN ('$id', '$merge') 
                AND u.`id` = a.`user`";
        if (!$this->db->query($sql)) {
            displaySQLError('Member Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            return;
        }
        while($r = $this->db->get_row()) {
            $members[$r['id']] = $r;
        }

        // Display form
        echo '
            <fieldset>
                <legend><span>'.T_('Merge Member').'</span></legend>
                <form method="post" id="merge-form" action="members.php">
                    <p>'.T_('Choose which information you would like to use from the two members below.').'</p>
                    <p>'.sprintf(T_('Please note that user [%s] and all information not selected will be deleted.'), $merge).'</p>
                    <div id="current-member">
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('ID').'</b></label>
                            </div> 
                            <div class="field-widget">'.$members[$id]['id'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Username').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="u1" name="username" value="'.$members[$id]['username'].'"/>
                                <label for="u1">'.$members[$id]['username'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('First Name').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="f1" name="fname" value="'.$members[$id]['fname'].'"/>
                                <label for="f1">'.$members[$id]['fname'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Last Name').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="l1" name="lname" value="'.$members[$id]['lname'].'"/>
                                <label for="l1">'.$members[$id]['lname'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Email').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="e1" name="email" value="'.$members[$id]['email'].'"/>
                                <label for="e1">'.$members[$id]['email'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Birthday').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="b1" name="birthday" value="'.$members[$id]['birthday'].'"/>
                                <label for="b1">'.$members[$id]['birthday'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Address').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="a1" name="address" value="'.$members[$id]['address'].'"/>
                                <label for="a1">'.$members[$id]['address'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('City').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="c1" name="city" value="'.$members[$id]['city'].'"/>
                                <label for="c1">'.$members[$id]['city'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('State').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="s1" name="state" value="'.$members[$id]['state'].'"/>
                                <label for="s1">'.$members[$id]['state'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Zip').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="z1" name="zip" value="'.$members[$id]['zip'].'"/>
                                <label for="z1">'.$members[$id]['zip'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Home Phone').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="h1" name="home" value="'.$members[$id]['home'].'"/>
                                <label for="h1">'.$members[$id]['home'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Work Phone').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="w1" name="work" value="'.$members[$id]['work'].'"/>
                                <label for="w1">'.$members[$id]['work'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Cell Phone').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" checked="checked" id="ce1" name="cell" value="'.$members[$id]['cell'].'"/>
                                <label for="ce1">'.$members[$id]['cell'].'</label>
                            </div>
                        </div>
                    </div>
                    <div id="merge-member">
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('ID').'</b></label>
                            </div> 
                            <div class="field-widget">'.$members[$merge]['id'].'</div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Username').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="u2" name="username" value="'.$members[$merge]['username'].'"/>
                                <label for="u2">'.$members[$merge]['username'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('First Name').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="f2" name="fname" value="'.$members[$merge]['fname'].'"/>
                                <label for="f2">'.$members[$merge]['fname'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Last Name').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="l2" name="lname" value="'.$members[$merge]['lname'].'"/>
                                <label for="l2">'.$members[$merge]['lname'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Email').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="e2" name="email" value="'.$members[$merge]['email'].'"/>
                                <label for="e2">'.$members[$merge]['email'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Birthday').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="b2" name="birthday" value="'.$members[$merge]['birthday'].'"/>
                                <label for="b2">'.$members[$merge]['birthday'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Address').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="a2" name="address" value="'.$members[$merge]['address'].'"/>
                                <label for="a2">'.$members[$merge]['address'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('City').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="c2" name="city" value="'.$members[$merge]['city'].'"/>
                                <label for="c2">'.$members[$merge]['city'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('State').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="s2" name="state" value="'.$members[$merge]['state'].'"/>
                                <label for="s2">'.$members[$merge]['state'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Zip').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="z2" name="zip" value="'.$members[$merge]['zip'].'"/>
                                <label for="z2">'.$members[$merge]['zip'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Home Phone').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="h2" name="home" value="'.$members[$merge]['home'].'"/>
                                <label for="h2">'.$members[$merge]['home'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Work Phone').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="w2" name="work" value="'.$members[$merge]['work'].'"/>
                                <label for="w2">'.$members[$merge]['work'].'</label>
                            </div>
                        </div>
                        <div class="field-row clearfix">
                            <div class="field-label">
                                <label><b>'.T_('Cell Phone').'</b></label>
                            </div> 
                            <div class="field-widget">
                                <input type="radio" id="ce2" name="cell" value="'.$members[$merge]['cell'].'"/>
                                <label for="ce2">'.$members[$merge]['cell'].'</label>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <p>
                        <input type="hidden" id="id" name="id" value="'.$id.'"/>
                        <input type="hidden" id="merge" name="merge" value="'.$merge.'"/>
                        <input class="sub1" type="submit" id="merge-submit" name="merge-submit" value="'.T_('Merge').'"/> '.T_('or').' &nbsp;
                        <a class="u" href="members.php">'.T_('Cancel').'</a>
                    </p>
                </form>
            </fieldset>';
    }

    /**
     * displayMemberList 
     * 
     * Displays the list of members, by default list all or list based on search results.
     *
     * @param   int     $page   which page to display
     * @param   string  $fname  search parameter for first name
     * @param   string  $lname  search parameter for last name
     * @param   string  $uname  search parameter for username
     * @return  void
     */
    function displayMemberList ($page, $fname = '', $lname = '', $uname = '')
    {
        $valid_search = 0;
        $from = (($page * 15) - 15);
        
        // Display the add link, search box and table header
        echo '
            <div id="actions_menu" class="clearfix">
                <ul><li><a class="add" href="?create=member">'.T_('Create Member').'</a></li></ul>
            </div>
            <hr/>
            <form method="post" action="members.php" name="search_frm" id="search_frm">
                <div>
                    <b>'.T_('Search').'</b>&nbsp;&nbsp; 
                    <label for="fname">'.T_('First Name').'</label> 
                    <input type="text" name="fname" id="fname" value="'.cleanOutput($fname).'"/>&nbsp;&nbsp; 
                    <label for="lname">'.T_('Last Name').'</label> 
                    <input type="text" name="lname" id="lname" value="'.cleanOutput($lname).'"/>&nbsp;&nbsp; 
                    <label for="uname">'.T_('Username').'</label> 
                    <input type="text" name="uname" id="uname" value="'.cleanOutput($uname).'"/>&nbsp;&nbsp; 
                    <input type="submit" id="search" name="search" value="'.T_('Search').'"/>
                </div>
                <hr/>
            </form>
            <p>&nbsp;</p>
            <form method="post" action="members.php">
                <table class="sortable">
                    <thead>
                        <tr>
                            <th>'.T_('ID').'</th>
                            <th>'.T_('Username').'</th>
                            <th>'.T_('Last Name').'</th>
                            <th>'.T_('First Name').'</th>
                            <th class="nosort">
                                <a class="help u" title="'.T_('Get Help using Access Levels').'" href="../help.php#adm-access">'.T_('Access Level').'</a>
                            </th>
                            <th class="nosort">'.T_('Active?').'</th>
                            <th class="nosort">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        // prevent sql injections - only allow letters, numbers, a space and the % sign
        if (strlen($fname) > 0) {
            if (!preg_match('/^[A-Za-z0-9%\s]+$/', $fname)) {
                $valid_search++;
            }
        }
        if (strlen($lname) > 0) {
            if (!preg_match('/^[A-Za-z0-9%\s]+$/', $lname)) {
                $valid_search++;
            }
        }
        if (strlen($uname) > 0) {
            if (!preg_match('/^[A-Za-z0-9%\s]+$/', $uname)) {
                $valid_search++;
            }
        }
        
        // Search - one or valid search parameters
        if ($valid_search < 1) {
            $sql = "SELECT * FROM `fcms_users` 
                    WHERE `password` != 'NONMEMBER' 
                    AND `password` != 'PRIVATE' ";
            if (strlen($fname) > 0) {
                $sql .= "AND `fname` LIKE '".cleanInput($fname)."' ";
            }
            if (strlen($lname) > 0) {
                $sql .= "AND `lname` LIKE '".cleanInput($lname)."' ";
            }
            if (strlen($uname) > 0) {
                $sql .= "AND `username` LIKE '".cleanInput($uname)."' ";
            }
            $sql .= "ORDER BY `id` LIMIT $from, 15";
        
        // Display All - one of more blank or invalid search parameters
        } else {
            $sql = "SELECT * FROM fcms_users "
                 . "WHERE password != 'NONMEMBER' "
                 . "ORDER BY `id` "
                 . "LIMIT $from, 15";
        }
        $result = mysql_query($sql) or displaySQLError(
            'Member Info Error', 
            __FILE__ . ' [' . __LINE__ . ']', 
            $sql, 
            mysql_error()
            );
        
        // Display the member list
        while($r = mysql_fetch_array($result)) {
            if ($r['id'] > 1) {
                echo '
                        <tr>
                            <td><b>'.(int)$r['id'].'</b>:</td>
                            <td><a href="?edit='.(int)$r['id'].'">'.cleanOutput($r['username']).'</a></td>
                            <td>'.cleanOutput($r['lname']).'</td>
                            <td>'.cleanOutput($r['fname']).'</td>
                            <td>';
                echo $this->displayAccessType($r['access']);
                echo '</td>
                            <td style="text-align:center">';
                if ($r['activated'] > 0) {
                    echo T_('Yes');
                } else {
                    echo T_('No');
                }
                echo '</td>
                            <td style="text-align:center"><input type="checkbox" name="massupdate[]" value="'.(int)$r['id'].'"/></td>
                        </tr>';
            } else {
                echo '
                        <tr>
                            <td><b>'.(int)$r['id'].'</b>:</td>
                            <td><b>'.cleanOutput($r['username']).'</b></td>
                            <td>'.cleanOutput($r['lname']).'</td>
                            <td>'.cleanOutput($r['fname']).'</td>
                            <td>1. '.T_('Admin').'</td>
                            <td style="text-align:center">'.T_('Yes').'</td>
                            <td>&nbsp;</td>
                        </tr>';
            }
        }
        echo '
                    </tbody>
                </table>
                <p style="text-align:right">
                    <input type="submit" name="activateAll" id="activateAll" value="'.T_('Activate Selected').'"/>&nbsp; 
                    <input type="submit" name="inactivateAll" id="inactivateAll" value="'.T_('Inactivate Selected').'"/>&nbsp; 
                    <input type="submit" name="deleteAll" id="deleteAll" value="'.T_('Delete Selected').'"/>
                </p>
            </form>';

        // Remove the LIMIT from the $sql statement 
        // used above, so we can get the total count
        $sql = substr($sql, 0, strpos($sql, 'LIMIT'));
        $this->db->query($sql) or displaySQLError(
            'Page Count Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error()
        );
        $count = $this->db->count_rows();
        $total_pages = ceil($count / 15); 
        displayPages("members.php", $page, $total_pages);
    }
    
    /**
     * displayAccessType 
     * 
     * Displays the access type based on access level code
     *
     * @param   int     $access_level 
     * @return  void
     */
    function displayAccessType ($access_level)
    {
        switch ($access_level) {
            case 1:
                echo "1. ".T_('Admin');
                break;
            case 2:
                echo "2. ".T_('Helper');
                break;
            case 3:
                echo "3. ".T_('Member');
                break;
            case 4:
                echo "4. ".T_('Non-Photographer');
                break;
            case 5:
                echo "5. ".T_('Non-Poster');
                break;
            case 6:
                echo "6. ".T_('Commenter');
                break;
            case 7:
                echo "7. ".T_('Poster');
                break;
            case 8:
                echo "8. ".T_('Photographer');
                break;
            case 9:
                echo "9. ".T_('Blogger');
                break;
            case 10:
                echo "10. ".T_('Guest');
                break;
            default:
                echo "10. ".T_('Guest');
                break;
        }
    }

    /**
     * mergeMember 
     * 
     * @param int $id 
     * @param int $merge 
     * 
     * @return void
     */
    function mergeMember ($id, $merge)
    {
        $id    = cleanInput($_POST['id'], 'int');
        $merge = cleanInput($_POST['merge'], 'int');

        // fcms_address

        // fcms_alerts
        $sql = "DELETE FROM `fcms_alerts`
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_alerts').'<br/>';

        // fcms_board_posts
        $sql = "UPDATE `fcms_board_posts`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_board_posts').'<br/>';

        // fcms_board_thread
        $sql = "UPDATE `fcms_board_threads`
                SET `started_by` = '$id'
                WHERE `started_by` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        $sql = "UPDATE `fcms_board_threads`
                SET `updated_by` = '$id'
                WHERE `updated_by` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_board_threads').'<br/>';

        // fcms_calendar
        $sql = "UPDATE `fcms_calendar`
                SET `created_by` = '$id'
                WHERE `created_by` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_calendar').'<br/>';

        // fcms_category
        $sql = "UPDATE `fcms_category`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_category').'<br/>';

        // fcms_chat_messages
        $sql = "UPDATE `fcms_chat_messages`
                SET `user_id` = '$id'
                WHERE `user_id` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_chat_messages').'<br/>';

        // fcms_chat_users

        // fcms_config

        // fcms_documents
        $sql = "UPDATE `fcms_documents`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_documents').'<br/>';

        // fcms_gallery_comments
        $sql = "UPDATE `fcms_gallery_comments`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_gallery_comments').'<br/>';

        // fcms_gallery_photos
        $sql = "UPDATE `fcms_gallery_photos`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_gallery_photos').'<br/>';

        // fcms_gallery_photos_tags
        $sql = "UPDATE `fcms_gallery_photos_tags`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_gallery_photos_tags').'<br/>';

        // fcms_navigation

        // fcms_news
        $sql = "UPDATE `fcms_news`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_news').'<br/>';

        // fcms_news_comments
        $sql = "UPDATE `fcms_news_comments`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_news_comments').'<br/>';

        // fcms_polls

        // fcms_poll_options

        // fcms_poll_votes
        $sql = "UPDATE `fcms_poll_votes`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_poll_votes').'<br/>';

        // fcms_prayers
        $sql = "UPDATE `fcms_prayers`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_prayers').'<br/>';

        // fcms_privatemsg
        $sql = "UPDATE `fcms_privatemsg`
                SET `to` = '$id'
                WHERE `to` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        $sql = "UPDATE `fcms_privatemsg`
                SET `from` = '$id'
                WHERE `from` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_privatemsg').'<br/>';

        // fcms_recipes
        $sql = "UPDATE `fcms_recipes`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_recipes').'<br/>';

        // fcms_recipe_comment
        $sql = "UPDATE `fcms_recipe_comment`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_recipe_comment').'<br/>';

        // fcms_relationship
        $sql = "UPDATE `fcms_relationship`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        $sql = "UPDATE `fcms_relationship`
                SET `rel_user` = '$id'
                WHERE `rel_user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_relationship').'<br/>';

        // fcms_users

        // fcms_user_awards
        $sql = "UPDATE `fcms_user_awards`
                SET `user` = '$id'
                WHERE `user` = '$merge'";
        if (!$this->db->query($sql)) {
            displaySQLError('Merge Error', __FILE__ . ' [' . __LINE__ . ']', $sql, mysql_error());
            die();
        }
        echo sprintf(T_('Merge [%s] complete.'), 'fcms_user_awards').'<br/>';

        // fcms_user_settings
    }
}