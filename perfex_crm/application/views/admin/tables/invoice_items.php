<?php
defined('BASEPATH') or exit('No direct script access allowed');

$aColumns     = array(
    'description',
    'long_description',
    'tblitems.rate',
    't1.taxrate as taxrate_1',
    't2.taxrate as taxrate_2',
    'unit',
    'tblitems_groups.name',
    );
$sIndexColumn = "id";
$sTable       = 'tblitems';

$join             = array(
    'LEFT JOIN tbltaxes t1 ON t1.id = tblitems.tax',
    'LEFT JOIN tbltaxes t2 ON t2.id = tblitems.tax2',
    'LEFT JOIN tblitems_groups ON tblitems_groups.id = tblitems.group_id',
    );
$additionalSelect = array(
    'tblitems.id',
    't1.name as taxname_1',
    't2.name as taxname_2',
    't1.id as tax_id_1',
    't2.id as tax_id_2',
    'group_id',
    );

$custom_fields = get_custom_fields('items');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_'.$key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_'.$key.'.value as '.$selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_'.$key . ' ON tblitems.id = ctable_'.$key . '.relid AND ctable_'.$key . '.fieldto="items_pr" AND ctable_'.$key . '.fieldid='.$field['id']);
}

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result           = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, array(), $additionalSelect);
$output           = $result['output'];
$rResult          = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = array();
    for ($i = 0; $i < count($aColumns); $i++) {
        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
            $_data = $aRow[strafter($aColumns[$i], 'as ')];
        } else {
            $_data = $aRow[$aColumns[$i]];
        }

        if ($aColumns[$i] == 't1.taxrate as taxrate_1') {
            if (!$aRow['taxrate_1']) {
                $aRow['taxrate_1'] = 0;
            }
            $_data = '<span data-toggle="tooltip" title="' . $aRow['taxname_1'] . '" data-taxid="'.$aRow['tax_id_1'].'">' . $aRow['taxrate_1'] . '%' . '</span>';
        } elseif ($aColumns[$i] == 't2.taxrate as taxrate_2') {
            if (!$aRow['taxrate_2']) {
                $aRow['taxrate_2'] = 0;
            }
            $_data = '<span data-toggle="tooltip" title="' . $aRow['taxname_2'] . '" data-taxid="'.$aRow['tax_id_2'].'">' . $aRow['taxrate_2'] . '%' . '</span>';
        } elseif ($aColumns[$i] == 'description') {
            $_data = '<a href="#" data-toggle="modal" data-target="#sales_item_modal" data-id="'.$aRow['id'].'">'.$_data.'</a>';
        } else {
            if(_startsWith($aColumns[$i],'ctable_') && is_date($_data)){
                $_data = _d($_data);
            }
        }

        $row[] = $_data;
    }
    $options = '';
    if (has_permission('items', '', 'edit')) {
        $options .= icon_btn('#' . $aRow['id'], 'pencil-square-o', 'btn-default', array(
            'data-toggle' => 'modal',
            'data-target' => '#sales_item_modal',
            'data-id' => $aRow['id'],
            ));
    }
    if (has_permission('items', '', 'delete')) {
        $options .= icon_btn('invoice_items/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }
    $row[] = $options;

    $output['aaData'][] = $row;
}
