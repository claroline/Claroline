/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    function enableRuleConfiguration()
    {
        var evaluationType = $('#activity_parameters_form_evaluation_type').val();
        
        if (evaluationType === 'automatic') {
            $('#activity-rule-form-div').show('slow', function () {
                $(this).removeClass('hidden')
            });
        } else {
            $('#activity-rule-form-div').hide('slow', function () {
                $('#activity_rule_form_action').val('none');
                enableRuleOption();
            });
        }
    }

    function enableRuleOption()
    {
        var actionSelect = $('#activity_rule_form_action').val();
        
        switch (actionSelect) {
            case 'none':
                $('#activity_rule_form_occurrence').prop('readonly', true);
                $('#activity_rule_form_occurrence').val(1);
                $('#activity_rule_form_result').attr('disabled', 'disabled');
                $('#activity_rule_form_resultMax').attr('disabled', 'disabled');
                $('#activity_rule_form_isResultVisible').attr('disabled', 'disabled');
                $('#activity_rule_form_badge').attr('disabled', 'disabled');
                $('#activity_rule_form_badge').val('');
                $('.activity-rule-option-date').attr('disabled', 'disabled');
                break;
            case 'badge-awarding':
                $('#activity_rule_form_badge').attr('disabled', false);
                $('.activity-rule-option-date').attr('disabled', false);
                $('#activity_rule_form_occurrence').prop('readonly', true);
                $('#activity_rule_form_occurrence').val(1);
                $('#activity_rule_form_result').attr('disabled', 'disabled');
                $('#activity_rule_form_resultMax').attr('disabled', 'disabled');
                $('#activity_rule_form_isResultVisible').attr('disabled', 'disabled');
                break;
            default:
                $('#activity_rule_form_occurrence').prop('readonly', false);
                $('#activity_rule_form_result').attr('disabled', false);
                $('#activity_rule_form_resultMax').attr('disabled', false);
                $('#activity_rule_form_isResultVisible').attr('disabled', false);
                $('.activity-rule-option-date').attr('disabled', false);
                $('#activity_rule_form_badge').attr('disabled', 'disabled');
        }
    }
    
    function updateRuleActions(actions)
    {
        var ruleActionSelect = $('#activity_rule_form_action');
        var selectValue = ruleActionSelect.val();
        
        ruleActionSelect.children().each(function () {
            var value = $(this).val();
            
            if (value === 'none' || actions.indexOf(value) >= 0) {
                $(this).removeClass('hidden');
            } else {
                $(this).addClass('hidden');
                
                if (value === selectValue) {
                    ruleActionSelect.val('none');
                    enableRuleOption();
                }
            }
        });
    }

    function checkAvailableActions()
    {
        var primaryResourceType = $('#activity_form_primaryResource').data('type');
        var route;
        
        if (typeof primaryResourceType === 'undefined') {
            route = Routing.generate(
                'claro_get_rule_actions_from_resource_type'
            );
        } else {
            route = Routing.generate(
                'claro_get_rule_actions_from_resource_type',
                {'resourceTypeName': primaryResourceType}
            );
        }
        
        $.ajax({
            url: route,
            type: 'GET',
            success: function (datas) {
                var actions = [];
                
                if (datas !== 'false') {
                    actions = $.parseJSON(datas);
                }
                updateRuleActions(actions);
            }
        });
    }
    
    function setRuleDefaultStartingDate()
    {
        if ($('#activity_rule_form_activeFrom').val() === '') {
            var defaultStartingDate =
                $('#activity-rule-form-div').data('rule-default-starting-date');

            $('#activity_rule_form_activeFrom').val(defaultStartingDate);
        }
    }
    
    $('#activity_form_primaryResource').on('change', function () {
        checkAvailableActions();
    });
    
    $('#activity_rule_form_action').on('change', function () {
        enableRuleOption();
    });
    
    $('#activity_parameters_form_evaluation_type').on('change', function () {
        enableRuleConfiguration();
    });
    
    enableRuleConfiguration();
    checkAvailableActions();
    enableRuleOption();
    setRuleDefaultStartingDate();
})();