<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$aColumns     = array(
    'name',
    'date',
    'email_to',
    'email',
    'subject',
    'message',
    'status'
    );

$sWhere = array();
if($this->ci->input->post('activity_log_date')){
    array_push($sWhere,'AND date LIKE "'.to_sql_date($this->ci->input->post('activity_log_date')).'%"');
}

$sIndexColumn = "id";
$sTable       = 'tblticketpipelog';
$result       = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), $sWhere);
$output       = $result['output'];
$rResult      = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'date') {
            $_data = _dt($_data);
        } else if ($aColumns[$i] == 'message') {
            $_data = mb_substr($_data, 0, 800);
        }
        $row[] = $_data;
    }
    $output['aaData'][] = $row;
}
