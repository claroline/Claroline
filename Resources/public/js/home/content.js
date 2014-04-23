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

    var home = window.Claroline.Home;
    var modal = window.Claroline.Modal;
    var tinymce = window.tinymce;

    $('body').on('click', '.content-size', function (event) {
        var content = $(event.target).parents('.content-element').get(0);
        var size = (content.className.match(/\bcontent-\d+/g) || []).join(' ').substr(8);
        var id = $(content).data('id');
        var type = $(content).data('type');

        modal.fromRoute('claroline_content_size', {'id': id, 'size': size, 'type': type}, function (element) {
            element.attr('id', 'sizes')
            .on('click', '.panel', function (event) {
                size = 'content-' + event.target.innerHTML;
                home.changeSize(size, id, type, content);
            });
        });
    });

    $('body').on('click', '.content-region', function (event) {
        var id = $(event.target).parents('.content-element').data('id');

        modal.fromRoute('claroline_content_region', {'content': id}, function (element) {
            element.attr('id', 'regions')
            .on('click', '.panel', function (event) {
                var name = $(event.target).data('region');
                home.changeRegion(name, id);
            });
        });
    });

    $('body').on('click', '.content-delete', function (event) {
        var content = $(event.target).parents('.content-element');

        modal.fromRoute('claro_content_confirm', null, function (element) {
            element.on('click', '.btn.delete', function () {
                home.deleteContent(content);
            });
        });
    });

    $('body').on('click', '.type-delete', function (event) {
        var type = $(event.target).parents('.alert');

        modal.fromRoute('claro_content_confirm', null, function (element) {
            element.on('click', '.btn.delete', function () {
                home.deleteContent(type, true);
            });
        });
    });

    $('body').on('click', '.create-type', function (event) {
        var typeCreator = $(event.target).parents('.creator').get(0);
        var name = $('input', typeCreator);

        if (typeCreator && name.val()) {
            $.ajax(home.path + 'content/typeexist/' + name.val())
            .done(function (data) {
                if (data === 'false') {
                    $.ajax(home.path + 'content/createtype/' + name.val())
                    .done(function (data) {
                        if (data !== 'false' && data !== '') {
                            $('.panel .panel-body', typeCreator).append(data);
                            name.val('');
                        } else {
                            modal.error();
                        }
                    })
                    .error(function () {
                        modal.error();
                    });
                } else {
                    modal.error();
                }
            });
        }
    });

    $('body').on('click', '.content-edit', function (event) {
        var element = $(event.target).parents('.content-element').get(0);
        var id = $(element).data('id');
        var type = $(element).data('type');
        var father = $(element).data('father');

        if (id && type && element) {
            var contentPath = 'content/creator/' + type + '/' + id;

            if (father) {
                contentPath = 'content/creator/' + type + '/' + id + '/' + father;
            }

            $.ajax(home.path + contentPath)
            .done(function (data) {
                $(element).replaceWith(data);
                $(window).scrollTop($('.creator[data-id="' + id + '"]').offset().top);
                $('.contents').trigger('ContentModified');
            })
            .error(function () {
                modal.error();
            });
        }
    });


    $('body').on('click', '.creator-button', function (event) {
        home.creator(event.target);
    });

    $('body').on('click', '.creator .edit-button', function (event) {
        var element = $(event.target).parents('.creator').get(0);
        var id = $(element).data('id');

        if (element && id) {
            home.creator(event.target, id, true);
        }
    });

    $('body').on('click', '.creator .cancel-button', function (event) {
        var element = $(event.target).parents('.creator').get(0);
        var id = $(element).data('id');
        var type = $(element).data('type');
        var father = $(element).data('father');

        if (id && type && element) {
            var contentPath = 'content/' + id + '/' + type;

            if (father) {
                element = $(element).parents('.creator' + father).get(0);
                contentPath = 'content/' + id + '/' + type + '/' + father;
            }

            $.ajax(home.path + contentPath)
            .done(function (data) {
                $(element).replaceWith(data);
                $('.contents').trigger('ContentModified');
            })
            .error(function () {
                modal.error();
            });
        }
    });

    $('body').on('click', '.creator .addlink', function () {
        var creator = $(event.target).parents('.creator').get(0);

        modal.fromRoute('claro_content_link', null, function (element) {
            element.on('click', '.btn-primary', function () {
                var urls = home.findUrls($('input', element).val());

                if (urls.length > 0) {
                    home.generatedContent(urls[0], function (data) {
                        var editor = tinymce.get($('.lang:not(.hide) textarea', creator).attr('id'));
                        editor.insertContent('<div>' + data + '</div>');
                        setTimeout(function () {
                            editor.fire('change');
                        }, 500);
                    });

                    element.modal('hide');
                } else {
                    $('.form-group', element).addClass('has-error');
                }
            });
        });
    });

    $('.contents').sortable({
        items: '> .content-element',
        cancel: 'input, textarea, button, select, option, a.btn.dropdown-toggle, .dropdown-menu,a',
        cursor: 'move'
    });

    $('.contents').on('sortupdate', function (event, ui) {
        if (this === ui.item.parent()[0]) {
            var a = $(ui.item).data('id');
            var b = $(ui.item).next().data('id');
            var type = $(ui.item).data('type');

            if (a && type) {
                $.ajax(home.path + 'content/reorder/' + type + '/' + a + '/' + b)
                .done(function () {
                    $('.contents').trigger('ContentModified');
                })
                .error(function () {
                    modal.error();
                });
            }
        }
    });
}());
