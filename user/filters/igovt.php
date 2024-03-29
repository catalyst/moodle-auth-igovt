<?php //$Id$

require_once($CFG->dirroot .'/user/filters/lib.php');

/**
 * User filter based on values of auth_igovt invitations
 */
class user_filter_igovt extends user_filter_type {
    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     */
    function user_filter_igovt($name, $label, $advanced) {
        parent::user_filter_type($name, $label, $advanced);
    }

    function get_igovt_statuses () {
        return (array(
                    ''          => get_string('anyvalue', 'filters'),
                    'notinvited' => get_string('notinvited', 'auth_igovt'),
                    'invited'   => get_string('invited', 'auth_igovt'),
                    'validated' => get_string('validated', 'auth_igovt'),
                    'expired'   => get_string('expired', 'auth_igovt'), 
                    'locked'    => get_string('locked', 'auth_igovt'), 
                    'cancelled' => get_string('cancelled', 'auth_igovt')));

    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    function setupForm(&$mform) {
        $objs = array();
        $objs[] =& $mform->createElement('select', $this->_name, null, $this->get_igovt_statuses());
        $objs[] =& $mform->createElement('static', $this->_name.'_break', null, '<br/>');
        $objs[] =& $mform->createElement('checkbox', $this->_name.'_sck', null, get_string('isafter', 'filters'));
        $objs[] =& $mform->createElement('date_selector', $this->_name.'_sdt', null);
        $objs[] =& $mform->createElement('static', $this->_name.'_break', null, '<br/>');
        $objs[] =& $mform->createElement('checkbox', $this->_name.'_eck', null, get_string('isbefore', 'filters'));
        $objs[] =& $mform->createElement('date_selector', $this->_name.'_edt', null);
        $objs[] = & $mform->createElement('checkbox', $this->_name.'_never', null, get_string('includenever', 'filters'));

        $grp =& $mform->addElement('group', $this->_name.'_grp', $this->_label, $objs, '', false);
        $grp->setHelpButton(array('date',$this->_label,'filters'));

        if ($this->_advanced) {
            $mform->setAdvanced($this->_name.'_grp');
        }

        $mform->disabledIf($this->_name.'_sdt[day]', $this->_name.'_sck', 'notchecked');
        $mform->disabledIf($this->_name.'_sdt[month]', $this->_name.'_sck', 'notchecked');
        $mform->disabledIf($this->_name.'_sdt[year]', $this->_name.'_sck', 'notchecked');
        $mform->disabledIf($this->_name.'_edt[day]', $this->_name.'_eck', 'notchecked');
        $mform->disabledIf($this->_name.'_edt[month]', $this->_name.'_eck', 'notchecked');
        $mform->disabledIf($this->_name.'_edt[year]', $this->_name.'_eck', 'notchecked');

       $mform->disabledIf($this->_name.'_never', $this->_name.'_eck', 'notchecked');

    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    function check_data($formdata) {
        $sck = $this->_name.'_sck';
        $sdt = $this->_name.'_sdt';
        $eck = $this->_name.'_eck';
        $edt = $this->_name.'_edt';
        $never = $this->_name.'_never';
        $status = $this->_name;

        if (empty($formdata->$status)) {
            return false;
        }

        if (!array_key_exists($sck, $formdata) and !array_key_exists($eck, $formdata) and !array_key_exists($status, $formdata)) {
            return false;
        }


        $data = array();
        if (array_key_exists($sck, $formdata)) {
            $data['after'] = $formdata->$sdt;
        } else {
            $data['after'] = 0;
        }
        if (array_key_exists($eck, $formdata)) {
            $data['before'] = $formdata->$edt;
        } else {
            $data['before'] = 0;
        }
        if (array_key_exists($never, $formdata)) {
            $data['never'] = $formdata->$never;
        } else {
            $data['never'] = 0;
        }
        $data['status'] = (string)$formdata->$status;


        return ($data);
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return string the filtering condition or null if the filter is disabled
     */
    function get_sql_filter($data) {
        global $CFG;

        $after  = $data['after'];
        $before = $data['before'];
        $never = $data['never'];
        $status  = $data['status'];

        if ($status == 'notinvited') {
            $sql = "id NOT IN (SELECT userid FROM {$CFG->prefix}auth_igovt_invitations)";
        } else {

            $sql = "id IN (SELECT userid 
                           FROM {$CFG->prefix}auth_igovt_invitations 
                           WHERE status = '$status' ";

                if (!empty($never)) {
                    $sql .= " AND datachanged >= 0 " ;
                } else {
                    $sql .= " AND datechanged > 0 " ;
                }

            if ($after) {
                $sql .= " AND datechanged >= $after";
            }

            if ($before) {
                $sql .= " AND datechanged <= $before";
            }

            $sql .= ')';
        }
        return($sql);
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        $after  = $data['after'];
        $before = $data['before'];
        $never = $data['never'];
        $status  = $data['status'];

        $a = new object();
        $a->label  = $this->_label;
        $a->after  = userdate($after);
        $a->before = userdate($before);
        $a->status = get_string($status, 'auth_igovt');

        $str = get_string('igovtstatusis', 'auth_igovt', $a);

        if ($never) {
            $strnever = ' ('.get_string('includenever', 'filters').')';
        } else {
            $strnever = '';
        }

        if ($after and $before) {
            $str .=  ' '.get_string('datelabelisbetween', 'auth_igovt', $a).$strnever;
        } else if ($after) {
            $str .=  ' '.get_string('datelabelisafter', 'auth_igovt', $a).$strnever;
        } else if ($before) {
            $str .=  ' '.get_string('datelabelisbefore', 'auth_igovt', $a).$strnever;
        }
        return $str;

    }
}
