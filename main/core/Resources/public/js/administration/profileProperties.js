$('.property-chk').on('change', function(event) {
    var route = Routing.generate(
            'claro_admin_invert_user_properties_edition',
            {'property': $(this).attr('data-property-id')}
        );
    $.ajax({
        url: route,
        success: function() {
        }
    });
});
