(function($) {
    $.fn.tree = function(opts)
    {
        return this.each(function(index)
        {
            var tree = $(this);

            $(' > ul', tree).attr('role', 'tree').find('ul').attr('role', 'group');

            var treeItem        = tree.find('li:has(ul)').addClass('parent_li').attr('role', 'treeitem');
            var treeItemTrigger = treeItem.find(' > span').attr('title', 'Collapse this year').prepend('<i class="fa fa-folder"></i> ');

            treeItem.find(' > ul > li').hide();

            treeItemTrigger.on('click', function (event) {
                var children = $(this).parent('li.parent_li').find(' > ul > li');
                if (children.is(':visible')) {
                    children.hide('fast');
                    $(this).attr('title', 'Expand this year').find(' > i').addClass('fa-folder').removeClass('fa-folder-open');
                }
                else {
                    children.show('fast');
                    $(this).attr('title', 'Collapse this year').find(' > i').addClass('fa-folder-open').removeClass('fa-folder');
                }
                event.stopPropagation();
            });
        });
    };
})(jQuery);