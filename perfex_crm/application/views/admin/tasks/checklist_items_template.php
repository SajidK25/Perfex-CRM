<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="clearfix"></div>
<?php if(count($checklists) > 0){ ?>
    <h4 class="bold chk-heading th font-medium pull-left"><?php echo _l('task_checklist_items'); ?></h4>
    <div class="pull-right chk-toggle-buttons">
        <button class="btn btn-xs btn-default<?php echo $hide_completed_items == 1 ? ' hide': '' ?>"
                data-hide="1"
                onclick="toggle_completed_checklist_items_visibility(this)">
            <?php echo _l('hide_task_checklist_items_completed'); ?>
        </button>
        <?php
            $finished = array_filter($checklists, function ($item) {
                return $item['finished'] == 1;
            });
        ?>
        <button class="btn btn-xs btn-default<?php echo $hide_completed_items == 1 ? '': ' hide' ?>"
                data-hide="0"
                onclick="toggle_completed_checklist_items_visibility(this)">
            <?php echo _l('show_task_checklist_items_completed', '(<span class="task-total-checklist-completed">'.count($finished).'</span>)'); ?>
        </button>
    </div>
    <div class="clearfix"></div>
<?php } ?>
<div class="progress mtop15 no-mbot hide">
    <div class="progress-bar not-dynamic progress-bar-default task-progress-bar" role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:0%">
    </div>
</div>
<div class="d-flex flex-column">
    <?php foreach($checklists as $list){ ?>
        <div>
            <div class="checklist" data-checklist-id="<?php echo $list['id']; ?>">
               <div class="d-flex">
                <div class="checkbox checkbox-success checklist-checkbox" data-toggle="tooltip" title="">
                    <input type="checkbox"<?php if($list['finished'] == 1 && $list['finished_from'] != get_staff_user_id() && !is_admin()){echo ' disabled';} ?> name="checklist-box" <?php if($list['finished'] == 1){echo 'checked';}; ?>>
                    <label for=""><span class="hide"><?php echo $list['description']; ?></span></label>
                </div>
                <div class="flex-grow-1">
                  <textarea data-taskid="<?php echo $task_id; ?>" name="checklist-description" rows="1"<?php if($list['addedfrom'] != get_staff_user_id() && !has_permission('tasks','','edit')){echo ' disabled';} ?>><?php echo clear_textarea_breaks($list['description']); ?></textarea>
              </div>
              <div class="mleft10">
                <?php if(has_permission('tasks','','delete') || $list['addedfrom'] == get_staff_user_id()){ ?>
                    <a href="#" class="pull-right text-muted remove-checklist" onclick="delete_checklist_item(<?php echo $list['id']; ?>,this); return false;"><i class="fa fa-remove"></i>
                    </a>
                <?php } ?>
                <?php if(has_permission('checklist_templates','','create')){ ?>
                    <a href="#" class="pull-right text-muted mright5 save-checklist-template<?php if($list['description'] == '' || total_rows(db_prefix().'tasks_checklist_templates',array('description'=>$list['description'])) > 0){echo ' hide';} ?>" data-toggle="tooltip" data-title="<?php echo _l('save_as_template'); ?>" onclick="save_checklist_item_template(<?php echo $list['id']; ?>,this); return false;">
                        <i class="fa fa-level-up" aria-hidden="true"></i>
                    </a>
                <?php } ?>
                  <?php if(($list['addedfrom'] == get_staff_user_id() || $current_user_is_creator || is_admin()) && count($task_staff_members) > 0){ ?>
                  <span class="pull-right mright5 mtop1 dropdown" data-title="<?php echo _l('task_checklist_assign'); ?>" data-toggle="tooltip">
                    <a href="#" class="text-muted dropdown-toggle"
                       data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false"
                       id="checklist-item-<?php echo $list['id']; ?>"
                       onclick="return false;">
                        <i class="fa fa-user-plus" aria-hidden="true"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-left" aria-labelledby="checklist-item-<?php echo $list['id']; ?>">
                        <?php foreach ($task_staff_members as $_staff) { ?>
                            <li>
                                    <a href="#"
                                       onclick="save_checklist_assigned_staff('<?php echo $_staff['staffid'] ; ?>', '<?php echo $list['id']; ?>'); return false;">
                                            <?php echo  $_staff['firstname'] . ' ' . $_staff['lastname'] ?>
                                            </a>
                                </li>
                        <?php } ?>
                    </ul>
                </span>
                  <?php } ?>
            </div>
        </div>
        <?php if($list['finished'] == 1 || $list['addedfrom'] != get_staff_user_id() || !empty($list['assigned'])){ ?>
            <p class="font-medium-xs mtop15 text-muted checklist-item-info">
                <?php
                if($list['addedfrom'] != get_staff_user_id()) {
                    echo _l('task_created_by',get_staff_full_name($list['addedfrom']));
                }
                if($list['addedfrom'] != get_staff_user_id() && $list['finished'] == 1) {
                    echo ' - ';
                }
                if($list['finished'] == 1){
                    echo _l('task_checklist_item_completed_by',get_staff_full_name($list['finished_from']));
                }
                if(($list['addedfrom'] != get_staff_user_id() || $list['finished'] == 1)&& !empty($list['assigned'])) {
                    echo ' - ';
                }
                if(!empty($list['assigned'])){
                    echo _l('task_checklist_assigned',get_staff_full_name($list['assigned']));
                }

                ?>
            </p>
        <?php } ?>
    </div>
</div>
<?php } ?>
</div>
<script>

    $(function(){
       $("#checklist-items").sortable({
            helper: 'clone',
            items: 'div.checklist',
            update: function(event, ui) {
                update_checklist_order();
            }
        });
        setTimeout(function(){
            do_task_checklist_items_height();
        },200);

        init_selectpicker();
        var _hideCompletedItems = '<?php echo $hide_completed_items?>'
        if (_hideCompletedItems == 1) {
            toggle_completed_checklist_items_visibility();
        }
   });

    function toggle_completed_checklist_items_visibility(el, forceShow) {
        var _task_checklist_items = $("body").find("input[name='checklist-box']");
        $.each(_task_checklist_items, function () {
            var $this = $(this);
            if ($this.prop('checked') == true) {
                $this.closest('.checklist ').toggleClass('hide');
            }
        });
        if (typeof  el != 'undefined')  {
            var _hideCompleted = $(el).data('hide');
            $(el).addClass('hide');
            $(el).siblings().removeClass('hide');
            $.post(admin_url+'staff/save_completed_checklist_visibility', {
                task_id: "<?php echo $task_id ?>",
                hideCompleted: _hideCompleted
            }, "json");
        }
    }

    function save_checklist_assigned_staff(staffId, list_id) {
        $.post(
            admin_url + 'tasks/save_checklist_assigned_staff',
            {
                assigned: staffId,
                checklistId: list_id,
                taskId: "<?php echo $task_id ?>",
            }
        ).done(function (response) {
            init_tasks_checklist_items(false, "<?php echo $task_id ?>");
        });
    }
</script>
