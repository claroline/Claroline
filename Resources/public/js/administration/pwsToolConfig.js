$('#tool-table-body').on('click', '.granted-btn', function () {
    var currentBtn = $(this);
    var perm = $(this).data('value');
    var roleId = $(this).data('role-id');
    var toolId = $(this).data('tool-id');
    var action = $(this).data('decoder-name');
    var iconClass = $(this).data('icon-class');
    var inverseIconClass = $(this).data('inverse-icon-class');

    $.ajax({
        url: Routing.generate(
            'claro_admin_pws_remove_tool',
            {
                'perm': perm,
                'role': roleId,
                'tool': toolId
            }
        ),
        type: 'POST',
        success: function() {
            currentBtn.removeClass('granted-btn');
            currentBtn.removeClass('text-success');
            currentBtn.removeClass(iconClass);
            currentBtn.addClass('denied-btn');
            currentBtn.addClass('text-danger');
            currentBtn.addClass(inverseIconClass);
            currentBtn.data('icon-class', inverseIconClass);
            currentBtn.data('inverse-icon-class', iconClass);
        }
    });
});

$('#tool-table-body').on('click', '.denied-btn', function () {
    var currentBtn = $(this);
    var perm = $(this).data('value');
    var pwsConfigId = $(this).data('pws-config-id');
    var roleId = $(this).data('role-id');
    var toolId = $(this).data('tool-id');
    var action = $(this).data('decoder-name');
    var iconClass = $(this).data('icon-class');
    var inverseIconClass = $(this).data('inverse-icon-class');

    $.ajax({
        url: Routing.generate(
            'claro_admin_pws_activate_tool',
            {
                'perm': perm,
                'role': roleId,
                'tool': toolId
            }
        ),
        type: 'POST',
        success: function() {
            currentBtn.removeClass('denied-btn');
            currentBtn.removeClass('text-danger');
            currentBtn.removeClass(iconClass);
            currentBtn.addClass('granted-btn');
            currentBtn.addClass('text-success');
            currentBtn.addClass(inverseIconClass);
            currentBtn.data('icon-class', inverseIconClass);
            currentBtn.data('inverse-icon-class', iconClass);
        }
    });
});
