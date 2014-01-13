/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function(){
    var ruleTabs = $("#ruleTabs");
    $('li:first-child a', ruleTabs).tab('show');

    var noRuleBlock = $('#no_rule');

    if (0 == $("a[data-toggle='tab']", ruleTabs).length) {
        noRuleBlock.show();
    } else {
        noRuleBlock.hide();
    }

    var tabPrototype        = ruleTabs.attr('data-tab-prototype');
    var tabContentPrototype = $(".badges_rules").attr('data-prototype');

    var badgeRules = $("#badge_form_badgeRules");
    var addTagLink = $("#add_rule");

    addTagLink.click(function(event){
        addRule();
        noRuleBlock.hide();
        event.preventDefault();
    });

    var ruleDeleteSelector = ".delete_rule";

    $(ruleTabs).on({
        mouseenter: function () {
            $(ruleDeleteSelector, $(this)).show();
        },
        mouseleave: function () {
            $(ruleDeleteSelector, $(this)).hide();
        }
    }, "li");

    var ruleDeleteNoConfirmSelector = "li .delete_rule_no_confirm";
    var ruleDeleteConfirmSelector   = "li .delete_rule_confirm";
    $(ruleDeleteSelector, ruleTabs).hide();
    $(ruleTabs).on({
        click: function () {
            deleteRule($(this).attr('data-id-tab'));
        }
    }, ruleDeleteNoConfirmSelector);

    $(ruleDeleteConfirmSelector, ruleTabs).confirmModal({'confirmCallback': confirmDeleteRule});



    $(".rules").on('click', ".action_panel select[id$='_action_']", function(event){
        var badgePanel = $(this).parents(".tab-pane").find(".badge_panel");
        if ("badge" == $(this).val().toLowerCase()) {
            badgePanel.removeClass("hide");
        }
        else {
            badgePanel.addClass("hide");
            $("select", badgePanel).val(null);
        }
    })

    function confirmDeleteRule(element)
    {
        deleteRule(element.attr('data-id-tab'));
    }

    function addRule()
    {
        var countExistingTabs = $("a[data-toggle='tab']", ruleTabs).length;
        addRuleTab(++countExistingTabs);
        addRuleTabContent(countExistingTabs);
        $("a[href=#rule" + countExistingTabs + "]", ruleTabs).tab('show');
    }

    function addRuleTab(tabIndex)
    {
        var newTab = tabPrototype.replace(/__name__/g, tabIndex);

        $("#add_rule", ruleTabs).before(newTab);
    }

    function addRuleTabContent(tabIndex)
    {
        var newTabContent = tabContentPrototype.replace(/__name__/g, tabIndex);

        $(".rules").append(newTabContent);
    }

    function deleteRule(tabId)
    {
        $("#tab" + tabId).remove();
        $("#" + tabId).remove();

        $('li:first-child a', ruleTabs).tab('show');

        if (0 == $("a[data-toggle='tab']", ruleTabs).length) {
            noRuleBlock.show();
        }
    }
});