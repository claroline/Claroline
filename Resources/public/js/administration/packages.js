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

    var packageElements = $('.package-element.composer');

    $('#update-packages-btn').on('click', function () {
        var countPackages = packageElements.length;
        var addProgress = Math.round(100 / countPackages);
        var modulo = 100 % countPackages;
        var progressBar = '<div class="progress progress-striped active">'
            + '<div id="progress-bar" class="progress-bar"  role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">'
            + '</div>'
            + '</div>';

        var html = Twig.render(
            ModalWindow,
            {
                'modalId': 'wait-modal',
                'body': progressBar,
                'header': Translator.get('platform:please_wait')
            }
        );

        $('body').append(html);
        //display validation modal
        $('#wait-modal').modal('show');
        //destroy the modal when hidden
        $('#wait-modal').on('hidden.bs.modal', function () {
            $(this).remove();
        });

        packageElements.each(function(index) {
            var distRef = ($(this).attr('data-package-dist-reference'));
            var route = Routing.generate('claro_admin_update_packages', {'ref': distRef});

            $.ajax({
                url: route,
                success: function(data) {

                   if (index + 1 === packageElements.length) {
                        addProgress += modulo;
                        //close the modal
                        $('#wait-modal').modal('hide');
                        //show the upgrade button
                        //$('#upgrade-packages-btn').removeClass('hidden');
                    }

                    updateProgressBar(addProgress);
                    actualizePackage(data);
                },
                error: function(data) {
                    //do some error handling
                }
            })
        });


    });

    var updateProgressBar = function (progress) {
        var valueNow = parseInt($('#progress-bar').attr('aria-valuenow')) + progress;
        $('#progress-bar').attr('aria-valuenow', valueNow);
        $('#progress-bar').css('width', valueNow + '%');
    }

    var actualizePackage = function (data) {
        var oldSpan = $("#accordion-" + data['distRef'] + ' .alert-danger');
        oldSpan.remove();
        var currentVersion = $("#accordion-" + data['distRef'] + ' .alert-info').html();

        if (window.Claroline.Utilities.versionCompare(currentVersion, data['tag'], '<')) {
            var html = "<span class='badge pull-right alert-danger badge-new-version'>"
                + Translator.get('platform:update_available')
                + "</span>";
            var parent = $("#accordion-" + data['distRef'] + ' h5');
            parent.append(html);
        }
    }

    $('#upgrade-packages-btn').on('click', function (event) {
        event.preventDefault();
        var btn = $(event.currentTarget);

        window.Claroline.Modal.simpleContainer(
            Translator.get('platform:upgrade'),
            '<div class="alert alert-warning">' + Translator.get('platform:package_upgrade_all_warning') + '</div>'
        ).on('click', 'button.btn', function () {
            $.ajax({url: btn.attr('href')});
            window.location = btn.attr('data-upgrade-page');
        });
    });
})();
