<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('home_payment_records'); ?>">
   <?php if(has_permission('payments','','view') || has_permission('invoices','','view_own')){ ?>
   <div class="row" id="payments">
      <div class="col-md-12">
         <div class="panel_s">
            <div class="panel-body padding-10">
               <div class="widget-dragger"></div>
               <div class="col-md-12">
                  <p class="pull-left mtop5"><?php echo _l('home_payment_records'); ?></p>
                  <?php if(has_permission('reports','','view')){ ?>
                  <a href="<?php echo admin_url('reports/sales'); ?>" class="pull-right mtop5"><?php echo _l('home_stats_full_report'); ?></a>
                  <?php } ?>
                  <div class="dropdown pull-right mtop5 mright10">
                     <a href="#" id="PaymentChartmode" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span id="Payment-chart-name" data-active-chart="weekly"> <?php echo _l('weekly') ?> </span>
                        <i class="fa fa-caret-down" aria-hidden="true"></i>
                     </a>
                     <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="PaymentChartmode">
                        <li>
                              <a href="#" data-type="weekly" onclick="update_payment_statistics(this); return false;"><?php echo _l('weekly') ?></a>
                        </li>
                        <li>
                              <a href="#" data-type="monthly" onclick="update_payment_statistics(this); return false;"><?php echo _l('monthly') ?></a>
                        </li>
                     </ul>
                  </div>
                  <div class="clearfix"></div>
                  <div class="row mtop5">
                     <hr class="hr-panel-heading-dashboard">
                  </div>
                  <?php if (is_using_multiple_currencies()) { ?>
                    <select class="selectpicker pull-left mbot15" name="currency" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                       <?php foreach($currencies as $currency){
                          $selected = '';
                          if($currency['isdefault'] == 1){
                           $selected = 'selected';
                        }
                        ?>
                        <option value="<?php echo $currency['id']; ?>" <?php echo $selected; ?> data-subtext="<?php echo $currency['name']; ?>"><?php echo $currency['symbol']; ?></option>
                        <?php } ?>
                     </select>
                   <?php } ?>
                   <canvas height="130" class="payments-chart-dashboard" id="payment-statistics"></canvas>
                   <div class="clearfix"></div>
                </div>
             </div>
          </div>
       </div>
    </div>
    <?php } ?>
 </div>

