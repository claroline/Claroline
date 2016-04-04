$(document).ready(function () {
    var $notificationElement = $('#' + notificationElementId);
    var href = $notificationElement.children('a').attr('href');
    var notificationsLoaded = false;
    $notificationElement.children('a').removeAttr('href');
    $notificationElement.addClass('dropdown');
    $notificationElement.children('a').addClass('pointer-hand dropdown-toggle');
    $notificationElement.children('a').attr('data-toggle', 'dropdown');
    $notificationElement.children('a').after('<div class="notifications-dropdown-list dropdown-menu"><div class="notification-pointer"></div><div class="notification-content"><div style="text-align: center"><img src="' + loadingIcon + '"></div></div></div>');
    $notificationElement.children('a').on('click', function (event) {
        //event.preventDefault();
        if (notificationsLoaded == false) {
            $.get(href)
                .always(function () {
                    if (notificationsLoaded == true) {

                    }
                })
                .done(function (data) {
                    $notificationElement.children('div.notifications-dropdown-list').children('div.notification-content').html(data);
                    notificationsLoaded = true;
                    var unviewedNotifications = $notificationElement.find('ul#notification-list').attr("data-count");
                    unviewedNotifications = parseInt(unviewedNotifications);
                    $notificationElement.children('a').children('span.badge').remove();
                    if (unviewedNotifications > 0) {
                        $notificationElement.children('a').append('<span class="badge">' + unviewedNotifications + '</span>');
                    }

                    /*$('#wnsc-'+newLink.attr('data-section')).html(data);
                     newLink.attr('data-empty','false');
                     containerNewForm = $('#newSectionContainer-'+newLink.attr('data-section'));
                     containerNewForm.find('#icap_wiki_section_type_activeContribution_text').attr('id', 'icap_wiki_section_type_'+newLink.attr('data-section'));
                     $('#wnsc-'+newLink.attr('data-section')).show();*/
                })
            ;
        } else {
            $notificationElement.children('div.notifications-dropdown-list').find('.not-viewed-notification').removeClass('not-viewed-notification');
            $notificationElement.children('a').unbind('click');
        }
    });
});