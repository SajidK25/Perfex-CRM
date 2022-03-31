<?php
defined('BASEPATH') or exit('No direct script access allowed');

$total_client_contacts = total_rows('tblcontacts', array('userid'=>$client_id));

$aColumns = array(
    'firstname',
    'lastname',
    'email',
    'title',
    'phonenumber',
    'active',
    'last_login'
);

$sIndexColumn = "id";
$sTable = 'tblcontacts';
$join = array();

$custom_fields = get_table_custom_fields('contacts');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_'.$key . ' ON tblcontacts.id = ctable_'.$key . '.relid AND ctable_'.$key . '.fieldto="'.$field['fieldto'].'" AND ctable_'.$key . '.fieldid='.$field['id']);
}

$where = array('AND userid='.$client_id);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array('tblcontacts.id as id', 'userid', 'is_primary'));

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();

    $row[] = '<img src="'.contact_profile_image_url($aRow['id']).'" class="client-profile-image-small mright5"><a href="#" onclick="contact('.$aRow['userid'].','.$aRow['id'].');return false;">'.$aRow['firstname'].'</a>';

    $row[] = $aRow['lastname'];

    $row[] = '<a href="mailto:'.$aRow['email'].'">'.$aRow['email'].'</a>';

    $row[] = $aRow['title'];

    $row[] = '<a href="tel:'.$aRow['phonenumber'].'">'.$aRow['phonenumber'].'</a>';

    $outputActive = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="'.admin_url().'clients/change_contact_status" name="onoffswitch" class="onoffswitch-checkbox" id="c_'.$aRow['id'].'" data-id="'.$aRow['id'].'"' . ($aRow['active'] == 1 ? ' checked': '') . '>
                <label class="onoffswitch-label" for="c_'.$aRow['id'].'"></label>
            </div>';
            // For exporting
            $outputActive .= '<span class="hide">' . ($aRow['active'] == 1 ? _l('is_active_export') : _l('is_not_active_export')) . '</span>';
    $row[] = $outputActive;

    $row[] = (!empty($aRow['last_login']) ? '<span class="text-has-action" data-toggle="tooltip" data-title="'._dt($aRow['last_login']).'">' . time_ago($aRow['last_login']) . '</span>' : '');

     // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $options = '';
    $options .= icon_btn('#', 'pencil-square-o', 'btn-default', array('onclick'=>'contact('.$aRow['userid'].','.$aRow['id'].');return false;'));
    if (has_permission('customers', '', 'delete') || is_customer_admin($aRow['userid'])) {
        if ($aRow['is_primary'] == 0 || ($aRow['is_primary'] == 1 && $total_client_contacts == 1)) {
            $options .= icon_btn('clients/delete_contact/'.$aRow['userid'].'/'.$aRow['id'], 'remove', 'btn-danger _delete');
        }
    }

    $row[] = $options;
    $output['aaData'][] = $row;
}
