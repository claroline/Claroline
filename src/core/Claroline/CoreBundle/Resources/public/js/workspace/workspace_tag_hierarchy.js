(function () {
    'use strict';

    var tagId = document.getElementById('twig-tag-id').getAttribute('data-tag-id');

    $('#admin-tag-hierarchy-children-button').click(function () {
        var possibleSelected = [];
        $('input:checkbox[name=tag-possible-child]:checked').each(function() {
            possibleSelected.push($(this).val());
        });
        var possibleSelectedString = possibleSelected.join();

        var selected = [];
        $('input:checkbox[name=tag-child]').each(function() {

            if (!$(this).is(':checked')) {
                selected.push($(this).val());
            }
        });
        var selectedString = selected.join();

        if (selectedString !== '') {
            $.ajax({
                url: Routing.generate(
                    'claro_workspace_admin_tag_remove_children',
                    {'tagId': tagId, 'childrenString': selectedString}
                ),
                type: 'GET',
                success: function () {
                    $('input:checkbox[name=tag-child]').each(function() {

                        if (!$(this).is(':checked')) {
                            $(this).attr('checked', false);
                            $(this).attr('name', 'tag-possible-child');
                            $('#possible-children-list').append('<li>' + $(this).parent().html() + '</li>');
                            $(this).parent().remove();
                        }
                    });
                }
            });
        }

        if (possibleSelectedString !== '') {
            $.ajax({
                url: Routing.generate(
                    'claro_workspace_admin_tag_add_children',
                    {'tagId': tagId, 'childrenString': possibleSelectedString}
                ),
                type: 'GET',
                success: function () {
                    $('input:checkbox[name=tag-possible-child]:checked').each(function() {
                        $(this).attr('checked', true);
                        $(this).attr('name', 'tag-child');
                        $('#children-list').append('<li>' + $(this).parent().html() + '</li>');
                        $(this).parent().remove();
                    });
                }
            });
        }
    });
})();