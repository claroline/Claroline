$(function(){
    var badgeFormFile = $("#badge_form_file");
    badgeFormFile.hide();

    var uploadImagePlaceholder = $(".upload_image_placeholder");
    uploadImagePlaceholder.click(function(event) {
        badgeFormFile.click();
        event.preventDefault();
    });

    badgeFormFile.change(function(){
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (event) {
                $("img", uploadImagePlaceholder).attr('src', event.target.result);
            };

            reader.readAsDataURL(input.files[0]);
        }
    });

    var ruleTabs = $("#ruleTabs");
    $('li:first-child a', ruleTabs).tab('show');

    var tabPrototype        = ruleTabs.attr('data-tab-prototype');
    var tabContentPrototype = $(".badges_rules").attr('data-prototype');

    var badgeRules = $("#badge_form_badgeRules");
    var addTagLink = $("#add_rule");

    addTagLink.click(function(event){
        addRule();
        $('#no_rule').hide();
        event.preventDefault();
    });

    $(ruleTabs).on({
        mouseenter: function () {
            $(".delete", $(this)).show();
        },
        mouseleave: function () {
            $(".delete", $(this)).hide();
        }
    }, "li");

    $(ruleTabs).on({
        click: function () {
            deleteRule($(this).attr('data-id-tab'));
        }
    }, "li .delete");

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
            $('#no_rule').show();
        }
    }
});