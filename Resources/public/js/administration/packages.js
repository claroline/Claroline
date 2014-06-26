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

    var packageElements = $('.package-element');

    $('#update-packages-btn').on('click', function (event) {
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

        packageElements.each(function(index, value) {
            var distRef = ($(this).attr('data-package-dist-reference'));
            var route = Routing.generate('claro_admin_update_package', {'ref': distRef});

            $.ajax({
                url: route,
                success: function(data) {

                   if (index + 1 === packageElements.length) {
                        addProgress += modulo;
                        //close the modal
                        $('#wait-modal').modal('hide');
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
        var html = "<span class='badge pull-right alert-danger badge-new-version'>" + data['tag'] + "</span>";
        var parent = $("#accordion-" + data['distRef'] + ' h5');
        parent.append(html);
    }
})();