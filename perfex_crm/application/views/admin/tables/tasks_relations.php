<?php
defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionEdit = has_permission('tasks', '', 'edit');
$bulkActions = $this->ci->input->get('bulk_actions');

$aColumns = array(
    'name',
    'startdate',
    'duedate',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM tbltags_in JOIN tbltags ON tbltags_in.tag_id = tbltags.id WHERE rel_id = tblstafftasks.id and rel_type="task" ORDER by tag_order ASC) as tags',
     get_sql_select_task_asignees_full_names().' as assignees',
    'priority',
    'status',
);

if ($bulkActions) {
    array_unshift($aColumns, '1');
}

$sIndexColumn = "id";
$sTable       = 'tblstafftasks';

$where = array();
include_once(APPPATH.'views/admin/tables/includes/tasks_filter.php');

if (!$this->ci->input->post('tasks_related_to')) {
    array_push($where, 'AND rel_id="' . $rel_id . '" AND rel_type="' . $rel_type . '"');
} else {
    // Used in the customer profile filters
    $tasks_related_to = explode(',', $this->ci->input->post('tasks_related_to'));
    $rel_to_query = 'AND (';

    $lastElement = end($tasks_related_to);
    foreach ($tasks_related_to as $rel_to) {
        if ($rel_to == 'invoice') {
            $rel_to_query .= '(rel_id IN (SELECT id FROM tblinvoices WHERE clientid=' . $rel_id . ')';
        } elseif ($rel_to == 'estimate') {
            $rel_to_query .= '(rel_id IN (SELECT id FROM tblestimates WHERE clientid=' . $rel_id . ')';
        } elseif ($rel_to == 'contract') {
            $rel_to_query .= '(rel_id IN (SELECT id FROM tblcontracts WHERE client=' . $rel_id . ')';
        } elseif ($rel_to == 'ticket') {
            $rel_to_query .= '(rel_id IN (SELECT ticketid FROM tbltickets WHERE userid=' . $rel_id . ')';
        } elseif ($rel_to == 'expense') {
            $rel_to_query .= '(rel_id IN (SELECT id FROM tblexpenses WHERE clientid=' . $rel_id . ')';
        } elseif ($rel_to == 'proposal') {
            $rel_to_query .= '(rel_id IN (SELECT id FROM tblproposals WHERE rel_type=' . $rel_id . ' AND rel_type="customer")';
        } elseif ($rel_to == 'customer') {
            $rel_to_query .= '(rel_id IN (SELECT userid FROM tblclients WHERE userid=' . $rel_id . ')';
        } elseif ($rel_to == 'project') {
            $rel_to_query .= '(rel_id IN (SELECT id FROM tblprojects WHERE clientid=' . $rel_id . ')';
        }

        $rel_to_query .= ' AND rel_type="'.$rel_to.'")';
        if ($rel_to != $lastElement) {
            $rel_to_query .= ' OR ';
        }
    }

    $rel_to_query .= ')';
    array_push($where, $rel_to_query);
}

$join          = array();

$custom_fields = get_table_custom_fields('tasks');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_'.$key.'.value as '.$selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblstafftasks.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$aColumns = do_action('tasks_related_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, array(
    'tblstafftasks.id',
    'billed',
    '(SELECT staffid FROM tblstafftaskassignees WHERE taskid=tblstafftasks.id AND staffid='.get_staff_user_id().') as is_assigned',
     get_sql_select_task_assignees_ids() .' as assignees_ids',
     '(SELECT MAX(id) FROM tbltaskstimers WHERE task_id=tblstafftasks.id and staff_id='.get_staff_user_id().' and end_time IS NULL) as not_finished_timer_by_current_staff',
));

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {

    $row = array();

    if ($this->ci->input->get('bulk_actions')) {
        $row[] = '<div class="checkbox"><input type="checkbox" value="'.$aRow['id'].'"><label></label></div>';
    }

    $outputName = '<a href="'.admin_url('tasks/view/'.$aRow['id']).'" class="display-block main-tasks-table-href-name" onclick="init_task_modal(' . $aRow['id'] . '); return false;">' . $aRow['name'] . '</a>';

    $row[] = $outputName;

    $row[] = _d($aRow['startdate']);

    $row[] = _d($aRow['duedate']);

    $row[] = render_tags($aRow['tags']);

    $row[] = format_members_by_ids_and_names($aRow['assignees_ids'],$aRow['assignees']);

    $row[] = '<span style="color:'.task_priority_color($aRow['priority']).';" class="inline-block">' . task_priority($aRow['priority']) . '</span>';

    $status = get_task_status_by_id($aRow['status']);
    $outputStatus = '<span class="inline-block label" style="color:'.$status['color'].';border:1px solid '.$status['color'].'" task-status-table="'.$aRow['status'].'">' . $status['name'];

    if ($aRow['status'] == 5) {
        $outputStatus .= '<a href="#" onclick="unmark_complete(' . $aRow['id'] . '); return false;"><i class="fa fa-check task-icon task-finished-icon" data-toggle="tooltip" title="' . _l('task_unmark_as_complete') . '"></i></a>';
    } else {
        $outputStatus .= '<a href="#" onclick="mark_complete(' . $aRow['id'] . '); return false;"><i class="fa fa-check task-icon task-unfinished-icon" data-toggle="tooltip" title="' . _l('task_single_mark_as_complete') . '"></i></a>';
    }

    $outputStatus .= '</span>';
    $row[] = $outputStatus;

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('tasks_related_table_row_data', array(
        'output' => $row,
        'row' => $aRow,
    ));

    $row = $hook['output'];

    $options = '';
    if ($hasPermissionEdit) {
        $options .= icon_btn('#', 'pencil-square-o', 'btn-default pull-right mleft5', array(
            'onclick' => 'edit_task(' . $aRow['id'] . '); return false',
        ));
    }

    $class = 'btn-success';

    $tooltip        = '';
    if ($aRow['billed'] == 1 || !$aRow['is_assigned'] || $aRow['status'] == 5) {
        $class = 'btn-default disabled';
        if ($aRow['status'] == 5) {
            $tooltip = ' data-toggle="tooltip" data-title="' . format_task_status($aRow['status'], false, true) . '"';
        } elseif ($aRow['billed'] == 1) {
            $tooltip = ' data-toggle="tooltip" data-title="' . _l('task_billed_cant_start_timer') . '"';
        } elseif (!$aRow['is_assigned']) {
            $tooltip = ' data-toggle="tooltip" data-title="' . _l('task_start_timer_only_assignee') . '"';
        }
    }

    if ($aRow['not_finished_timer_by_current_staff']) {
        $options .= icon_btn('#', 'clock-o', 'btn-danger pull-right no-margin', array(
            'onclick' => 'timer_action(this,' . $aRow['id'] . ',' . $aRow['not_finished_timer_by_current_staff'] . '); return false',
        ));
    } else {
        $options .= '<span' . $tooltip . ' class="pull-right">' . icon_btn('#', 'clock-o', $class . ' no-margin', array(
             'onclick' => 'timer_action(this,' . $aRow['id'] . '); return false',
         )) . '</span>';
    }

    $row[]              = $options;

    $row['DT_RowClass'] = '';
    if ((!empty($aRow['duedate']) && $aRow['duedate'] < date('Y-m-d')) && $aRow['status'] != 5) {
        $row['DT_RowClass'] = 'text-danger bold ';
    }

    $output['aaData'][] = $row;
}
