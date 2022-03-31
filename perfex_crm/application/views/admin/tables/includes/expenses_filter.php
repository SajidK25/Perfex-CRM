<?php

if($this->ci->input->post('invoiced')){
    array_push($filter, 'OR invoiceid IS NOT NULL');
}
if($this->ci->input->post('billable')){
    array_push($filter, 'OR billable = 1');
}
if($this->ci->input->post('non-billable')){
    array_push($filter, 'OR billable = 0');
}
if($this->ci->input->post('unbilled')){
    array_push($filter, 'OR invoiceid IS NULL');
}
if($this->ci->input->post('recurring')){
    array_push($filter, 'OR recurring = 1');
}
$categories = $this->ci->expenses_model->get_category();
$_categories = array();
foreach($categories as $c){
    if($this->ci->input->post('expenses_by_category_'.$c['id'])){
        array_push($_categories,$c['id']);
    }
}
if(count($_categories) > 0){
    array_push($filter,'AND category IN ('.implode(', ',$_categories).')');
}

$_months = array();
for ($m = 1; $m <= 12; $m++) {
    if($this->ci->input->post('expenses_by_month_'.$m)){
        array_push($_months,$m);
    }
}
if(count($_months) > 0){
    array_push($filter,'AND MONTH(date) IN ('.implode(', ',$_months).')');
}
$years = $this->ci->expenses_model->get_expenses_years();
$_years = array();
foreach($years as $year){
    if($this->ci->input->post('year_'.$year['year'])){
        array_push($_years,$year['year']);
    }
}
if(count($_years) > 0){
    array_push($filter,'AND YEAR(date) IN ('.implode(', ',$_years).')');
}

if(count($filter) > 0){
    array_push($where,'AND ('.prepare_dt_filter($filter).')');
}
