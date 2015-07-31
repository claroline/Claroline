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
    var common = window.Claroline.Common;
    var tinymce = window.tinymce;
    var routing = window.Routing;
    var translator = window.Translator;

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
    })
    .on('click', '.content-region', function (event) {
        var id = $(event.target).parents('.content-element').data('id');

        modal.fromRoute('claroline_content_region', {'content': id}, function (element) {
            element.attr('id', 'regions')
            .on('click', '.panel', function (event) {
                var name = $(event.target).data('region');
                home.changeRegion(name, id);
            });
        });
    })
    .on('click', '.content-delete', function (event) {
        var content = $(event.target).parents('.content-element').first();

        modal.fromRoute('claro_content_confirm', null, function (element) {
            element.on('click', '.btn.delete', function () {
                home.deleteContent(content);
            });
        });
    })
    .on('click', '.type-delete', function (event) {
        var type = $(event.target).parents('.alert');

        modal.fromRoute('claro_content_confirm', null, function (element) {
            element.on('click', '.btn.delete', function () {
                home.deleteContent(type, true);
            });
        });
    })
    .on('click', '.type-publish', function (event) {
        home.publishType($(event.target).parents('.alert').first());
    })
    .on('click', '.type-rename', function (event) {
        var type = $(event.target).parents('.alert').data('name');
        var link = $(event.target).parents('.alert').find('strong a');

        modal.fromRoute('claro_content_rename_type_form', {'type': type}, function (element) {
            element.on('click', '.btn-primary', function () {
                var name = $('input', element).val();

                $('input', element).parent().removeClass('has-error').find('.help-block').remove();

                if (name === '') {
                    $('input', element).parent().addClass('has-error').append(
                        common.createElement('div', 'help-block field-error').html(
                            translator.trans('name_required', {}, 'platform')
                        )
                    );
                } else {
                    if (type !== name) {
                        $.ajax(routing.generate('claroline_content_type_exist', {'name': name}))
                        .done(function (data) {
                            if (data === 'false') {
                                $.ajax(routing.generate('claro_content_rename_type', {'type': type, 'name': name}))
                                .done(function (data) {
                                    if (data === 'true') {
                                        link.html(name).attr(
                                            'href', routing.generate('claro_get_content_by_type', {'type': name})
                                        );
                                        $(element).modal('hide');
                                    } else {
                                        modal.error();
                                    }
                                })
                                .error(function () {
                                    modal.error();
                                });
                            } else {
                                $('input', element).parent().addClass('has-error').append(
                                    common.createElement('div', 'help-block field-error').html(
                                        translator.trans('page_already_exists', {}, 'home')
                                    )
                                );
                            }
                        });
                    } else {
                        $(element).modal('hide');
                    }
                }
            });
        });
    })
    .on('click', '.type-template', function (event) {
        var typeId = $(event.target).parents('.alert').data('id');
        
        modal.displayForm(
            Routing.generate(
                'claro_content_change_template_form',
                {'type': typeId}
            ),
            function() {},
            function() {}
        );
    })
    .on('click', '.create-type', function (event) {
        var typeCreator = $(event.target).parents('.creator').get(0);
        var name = $('input', typeCreator);

        if (typeCreator && name.val()) {
            $.ajax(routing.generate('claroline_content_type_exist', {'name': name.val()}))
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
                    modal.simpleContainer(
                        translator.trans('new_content_page', {}, 'home'),
                        translator.trans('page_already_exists', {}, 'home')
                    );
                }
            });
        }
    })
    .on('click', '.content-edit', function (event) {
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
    })
    .on('click', '.creator-button', function (event) {
        home.creator(event.target);
    })
    .on('click', '.creator .edit-button', function (event) {
        var element = $(event.target).parents('.creator').get(0);
        var id = $(element).data('id');

        if (element && id) {
            home.creator(event.target, id, true);
        }
    })
    .on('click', '.creator .cancel-button', function (event) {
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
    })
    .on('click', '.creator .addlink', function () {
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
    })
    .on('click', '.send-content', function (event) {
        var id = $(event.target).parents('.content-element').data('id');
        var type = $(event.target).parents('.content-element').data('type');
        var content = $(event.target).parents('.content-element').first();

        modal.fromRoute('claroline_move_content_form', {'currentType': type}, function (element) {
            element.on('change', 'select', function () {
                var page = $(this).val();

                $.ajax(routing.generate('claroline_move_content', {'content': id, 'type': type, 'page': page}))
                .success(function (data) {
                    if (data === 'true') {
                        $(element).modal('hide');
                        content.hide('slow', function () {
                            $(this).remove();
                            $('.contents').trigger('ContentModified');
                        });
                    } else {
                        modal.error();
                    }
                })
                .error(function () {
                    modal.error();
                });
            });
        });
    }).on('click', '.collapse-content', function (event) {
        var element = $(event.target).parents('.content-element').get(0);
        var id = $(element).data('id');
        var type = $(element).data('type');

        if (id && element) {
            home.collapse(element, id, type);
        }
    });

    $('.contents').sortable({
        items: '> .content-element',
        cancel: 'input, textarea, button, select, option, a.btn.dropdown-toggle, .dropdown-menu,a',
        cursor: 'move'
    }).on('sortupdate', function (event, ui) {
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

    $('.content-element > .list-group.menu').sortable({
        items: '> .content-element.list-group-item',
        placeholder: 'ui-state-highlight',
        cursor: 'move'
    }).on('sortupdate', function (event, ui) {
        var a = $(ui.item).data('id');
        var b = $(ui.item).next().data('id');
        var type = $(ui.item).data('type');
        var father = $(ui.item).parents('.content-element').first().data('id');

        if (a && type) {
            $.ajax(home.path + 'content/reorder/' + type + '/' + a + '/' + b + '/' + father)
            .error(function () {
                modal.error();
            });
        }
    });
}());
