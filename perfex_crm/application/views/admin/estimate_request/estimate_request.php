<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-sm-7">
                        <h3 class="no-margin">
                            <?php echo $title; ?>
                        </h3>
                        <h4><?php echo $estimate_request->form_data->name ?? ''; ?></h4>
                        </div>
                        <div class="col-sm-5 mbot5">
                            <div class="btn-group pull-right">
                                <?php if (staff_can('create', 'estimates') || staff_can('create', 'proposals')) { ?>
                                    <span class="dropdown">
                                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false"
                                            id="convertDropdown">
                                    Convert <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="convertDropdown">

                                        <?php if (staff_can('create', 'estimates')) { ?>
                                            <li>
                                                <a href="#"
                                                   onclick="convert_estimate_request_to('estimate'); return false;">
                                                <?php echo _l('estimate') ?>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <?php if (staff_can('create', 'proposals')) { ?>
                                            <li>
                                                <a href="#" data-template="proposal"
                                                   onclick="convert_estimate_request_to('proposal'); return false;">
                                                    <?php echo _l('proposal') ?>
                                                </a>
                                            </li>
                                        <?php } ?>

                                    </ul>
                                </span>
                                <?php } ?>
                                <?php if (staff_can('edit', 'estimate_request')) { ?>
                                    <span class="dropdown">
                                    <button type="button" class="btn btn-secondary dropdown-toggle mleft10"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                            id="actionsDropdown">
                                    more <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-right"
                                        aria-labelledby="actionsDropdown">
                                        <?php foreach ($statuses as $status) { ?>
                                            <li>
                                            <a href="#" data-template="estimate"
                                               onclick="return mark_estimate_request_as(<?= $status['id'] ?>, <?= $estimate_request->id ?>)">
                                            <?php echo _l('mark_estimate_request_as', $status['name']) ?></a>
                                        </li>
                                        <?php } ?>
                                        <?php if (staff_can('delete', 'estimate_request')) { ?>
                                            <li>
                                        <a href="<?= admin_url('estimate_request/delete/' . $estimate_request->id) ?>"
                                           class="_delete text-danger">
                                        <?= _l('delete') ?></a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </span>
                                <?php } ?>
                            </div>
                        </div>
                      </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div>
                            <?php if (staff_can('edit', 'estimate_request')) { ?>
                                <div class="form-group no-mbot" id="inputTagsWrapper">
                                    <label for="tags" class="control-label"><i class="fa fa-tag"
                                                                               aria-hidden="true"></i> <?php echo _l('tags'); ?>
                                    </label>
                                    <input type="text" class="tagsinput" id="tags" name="tags"
                                           value="<?php echo(isset($estimate_request) ? prep_tags_input(get_tags_in($estimate_request->id, 'estimate_request')) : ''); ?>"
                                           data-role="tagsinput">
                                </div>
                                <?php
                                echo render_select_with_input_group(
                                    'assigned',
                                    $members,
                                    array('staffid', array('firstname', 'lastname')),
                                    'estimate_request_assigned', $estimate_request->assigned,
                                    '<a href="#" id="savAssigned" onclick="save_estimate_request_assigned_staff();return false;" class="inline-field-new">'._l('save').'</a>'
                                    );
                                ?>
                            <?php } ?>
                            <h4 class="mbot5 font-medium">
                                <?php echo _l('estimate_request_date_added') ?>
                            </h4>
                            <p>
                                <?php echo _dt($estimate_request->date_added) ?>
                            </p>
                            <hr class="mbot5"/>
                            <h4 class="mbot5 font-medium">
                                <?php echo _l('estimate_request_status') ?>
                            </h4>
                            <p id="est_request_status_name">
                                <?php echo $estimate_request->status_name ?>
                            </p>
                            <?php
                            $submissions = json_decode($estimate_request->submission);
                            foreach ($submissions as $data) {
                                ?>
                                <hr class="mbot5"/>
                                <h4 class="mbot5 font-medium">
                                    <?php echo $data->label ?>
                                </h4>
                                <p>
                                <?php
                                if (is_string($data->value)) {
                                    echo $data->value;
                                } elseif (is_null($data->value)) {
                                    if (count($estimate_request->attachments) > 0) { ?>
                                        <div class="mtop20" id="lead_attachments">
                                            <?php $this->load->view('admin/estimate_request/estimate_request_attachments_template', array('attachments' => $estimate_request->attachments)); ?>
                                        </div>
                                    <?php }
                                } else {
                                    echo implode('<br>', $data->value);
                                }
                                ?>
                                </p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/estimate_request/convert_estimate_request'); ?>
<?php init_tail(); ?>
<script>
    var lead = <?php echo (isset($lead) ? json_encode($lead) : "''"); ?>;
    var contact = <?php echo (isset($contact) ? json_encode($contact) : "''"); ?>;
    var contactCompanyName = "<?php echo isset($contact) ? get_company_name($contact->userid) : ''; ?>";
    var $createCustomerFields = $('#create_customer_fields');

    var $relIdSelect = $('#rel_id'),
    $relTypeSelect = $('#rel_type'),
    $relIdWrapper = $('#rel_id_wrapper'),
    $relTypeWrapper = $('#rel_type_wrapper');

    $(function(){
        $('#newConvertCustomer').on('click', function(e) {
            e.preventDefault();

            $createCustomerFields.toggleClass('hide')
            $('#rel_wrapper').toggleClass('hide');

            if($createCustomerFields.hasClass('hide')) {
                validate_estimate_request_convert_form();
            } else {
                validate_estimate_request_customer_form();
            }
        });

        $('input[name="tags"]').change(function (el) {
            $.post(
                admin_url + 'estimate_request/update_tags/' + "<?php echo $estimate_request->id ?>",
                {
                    tags: el.target.value
                }
                ).done(function (response) {
                    response = JSON.parse(response);
                    if (response.success == 'true' || response.success == true) {
                        alert_float('success', response.message);
                    }
                    $(el).hide();
                });
            });

        init_ajax_search($relTypeSelect.val(), $relIdSelect, {
            rel_id: $relIdSelect.val(),
            type: $relTypeSelect.val(),
        });

        $relTypeSelect.on('change', function () {
            var currentSelectedOption = $relIdSelect.find('option:first')[0];
            var clonedSelect = $relIdSelect.html('').clone();
            var value = $(this).selectpicker('val');

            if(!$(currentSelectedOption).hasClass('bs-title-option')) {
                clonedSelect.html($(currentSelectedOption))
            }

            $relIdSelect.selectpicker('destroy').remove();
            $relIdSelect = clonedSelect;

            $('#rel_id_select').append(clonedSelect);

            init_ajax_search($relTypeSelect.val(), $relIdSelect, {
                rel_id: $relIdSelect.val(),
                type: $relTypeSelect.val(),
            });

            value != '' ? $relIdWrapper.removeClass('hide') : $relIdWrapper.addClass('hide')

            $('.rel_id_label').html(
                $relTypeSelect.find('option:selected').text()
                );
        });
    });

    function save_estimate_request_assigned_staff() {
        $.post(
            admin_url + 'estimate_request/update_assigned_staff/',
            {
                assigned: $('select#assigned').val(),
                requestid: "<?php echo $estimate_request->id ?>",
            }
        ).done(function (response) {
            response = JSON.parse(response);
            if (response.success == 'true' || response.success == true) {
                alert_float('success', response.message);
            }
        });
    }

        function mark_estimate_request_as(status_id, request_id) {
            $.post(admin_url + 'estimate_request/update_request_status', {
                status: status_id,
                requestid: request_id,
            }).done(function (response) {
                response = JSON.parse(response);
                if (response.success == 'true' || response.success == true) {
                    alert_float('success', response.message);
                    $('#est_request_status_name').text(response.status_name);
                }
            });

            return false;
        }

        function validate_estimate_request_convert_form() {
            var $form = $('#request_to_client_form');
            resetRequestForm($form, false);

            $form.appFormValidator({rules: {
                rel_type:{
                    required: true
                },
                rel_id: {
                    required: true
                },
            }});
        }

        function validate_estimate_request_customer_form() {
            var $form = $('#request_to_client_form');
            resetRequestForm($form, true);

            var rules = {
                firstname: {
                    required: true
                },
                lastname: {
                    required: true
                },
                password: {
                    required: {
                        depends: function (element) {
                            return $('input[name="send_set_password_email"]').prop('checked') === false;
                        }
                    }
                },
            }

            rules.email = {
                <?php if(hooks()->apply_filters('contact_email_required', "true") === "true"){ ?>
                required: true,
                <?php } ?>
                email: true,
                <?php if(hooks()->apply_filters('contact_email_unique', "true") === "true"){ ?>
                remote: {
                    url: admin_url + "misc/contact_email_exists",
                    type: 'post',
                    data: {
                        email: function () {
                            return $('#request_to_client_form input[name="email"]').val();
                        },
                    }
                }
                <?php } ?>
            }

            if (app.options.company_is_required == 1) {
                rules.company = {
                    required: true
                }
            }

            $form.appFormValidator({rules: rules, submitHandler: function(form) {
                $relIdSelect.selectpicker('val','');
                $relTypeSelect.selectpicker('val','');
                return true;
            }});
        }

        function convert_estimate_request_to(type) {
            if (type === 'proposal') {
                $relTypeSelect.selectpicker('val', '');
                $relTypeWrapper.show();
            } else if (type === 'estimate') {
                $relTypeWrapper.hide();
                $relTypeSelect.selectpicker('val', 'customer');
            }

            $relIdSelect.selectpicker('val', '');

            if(contact !== '') {
                $relTypeSelect.selectpicker('val', 'customer');
                $relIdSelect.html('<option value="'+contact.userid+'" selected>'+contactCompanyName+'</option>');
            } else if(type === 'proposal' && lead !== '') {
                $relTypeSelect.selectpicker('val', 'lead');
                $("select#rel_id").html('<option value="'+lead.id+'" selected>'+lead.name+'</option>');
            }

            $relTypeSelect.trigger('change');

            $('input[name="convert_to"]').val(type);
            $('#convert_estimate_request_form').modal('show');
        }

        function resetRequestForm($form, withCustomFields) {
            if($form.data('validator')) {
                $form.data('validator').destroy();
            }

            $.each($form.find($.fn.appFormValidator.internal_options.required_custom_fields_selector), function() {
                if(withCustomFields === true) {
                    $(this).removeClass('do-not-validate')
                } else {
                   $(this).addClass('do-not-validate');
                }
            });
        }

        validate_estimate_request_convert_form();

</script>

</body>
</html>
