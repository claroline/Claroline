(function () {
    'use strict';

    var home = window.Claroline.Home;

    $('body').on('mouseenter', '.content-element', function () {

        if ($('.content-menu', this).get(0) !== undefined) {
            $('.content-menu').each(function () {
                if (!$(this).hasClass('hide')) {
                    $(this).addClass('hide');
                }
                if ($(this).hasClass('open')) {
                    $(this).removeClass('open');
                }
            });
        }

        $(this).find('.content-menu').first().removeClass('hide');

    });

    $('body').on('mouseleave', '.content-element', function () {
        if (!$(this).find('.content-menu').first().hasClass('open')) {
            $(this).find('.content-menu').first().removeClass('open');
        }
        $(this).find('.content-menu').first().addClass('hide');
    });

    $('body').on('click', '.content-size', function (event) {
        var element = $(event.target).parents('.content-element').get(0);
        var size = (element.className.match(/\bcontent-\d+/g) || []).join(' ').substr(8);
        var id = $(element).data('id');
        var type = $(element).data('type');

        home.modal('content/size/' + id + '/' + size + '/' + type, 'sizes', element);
    });

    $('body').on('click', '#sizes .panel', function (event) {
        var size = 'content-' + event.target.innerHTML;
        var id = $('#sizes .modal-body').data('id');
        var type = $('#sizes .modal-body').data('type');
        var element = $('#sizes').data('element');

        if (id && type && element) {
            $.post(home.path + 'content/update/' + id, { 'size': size, 'type': type })
        .done(
            function (data)
            {
                if (data === 'true') {
                    $(element).removeClass(function (index, css) {
                        return (css.match(/\bcontent-\d+/g) || []).join(' ');
                    });

                    $(element).addClass(size);
                    $(element).trigger('DOMSubtreeModified'); //height resize event
                    $('#sizes').modal('hide');
                    $('.contents').trigger('ContentModified');

                } else {
                    home.modal('content/error');
                }
            }
            )
        .error(
                function ()
                {
                    home.modal('content/error');
                }
              )
        ;
        }
    });

    $('body').on('click', '.content-region', function (event) {
        var element = $(event.target).parents('.content-element').get(0);
        var id = $(element).data('id');

        home.modal('content/region/' + id, 'regions', element);
    });


    $('body').on('click', '#regions .panel', function (event) {
        var name = $(event.target).data('region');
        var id = $('#regions .modal-body').data('id');

        if (id && name) {
            $.ajax(home.path + 'region/' + name + '/' + id)
                .done(
                    function ()
                    {
                        location.reload();
                    }
                )
                .error(
                    function ()
                    {
                        home.modal('content/error');
                    }
                )
            ;
        }
    });

    $('body').on('click', '.content-delete', function (event) {
        var element = $(event.target).parents('.content-element').get(0);

        home.modal('content/confirm', 'delete-content', element);
    });

    $('body').on('click', '#delete-content .btn.delete', function () {
        var element = $('#delete-content').data('element');
        var id = $(element).data('id');

        if (id && element) {
            $.ajax(home.path + 'content/delete/' + id)
            .done(
                function (data)
                {
                    if (data === 'true') {
                        $(element).hide('slow', function () {
                            $(this).remove();
                            $('.contents').trigger('ContentModified');
                        });
                    } else {
                        home.modal('content/error');
                    }
                }
            )
            .error(
                function ()
                {
                    home.modal('content/error');
                }
            );
        }
    });

    $('body').on('click', '.type-delete', function (event) {
        var element = $(event.target).parents('.alert').get(0);

        home.modal('content/confirm', 'delete-type', element);
    });

    $('body').on('click', '#delete-type .btn.delete', function () {
        var element = $('#delete-type').data('element');
        var id = $(element).data('id');

        if (id && element) {
            $.ajax(home.path + 'content/deletetype/' + id)
            .done(
                function (data)
                {
                    if (data === 'true') {
                        $(element).parent().hide('slow', function () {
                            $(this).remove();
                        });
                    } else {
                        home.modal('content/error');
                    }
                }
            )
            .error(
                function ()
                {
                    home.modal('content/error');
                }
            );
        }
    });

    $('body').on('click', '.create-type', function (event) {
        var typeCreator = $(event.target).parents('.creator').get(0);
        var name = $('input', typeCreator);

        if (typeCreator && name.val()) {
            $.ajax(home.path + 'content/typeexist/' + name.val())
            .done(
                function (data)
                {
                    if (data === 'false') {
                        $.ajax(home.path + 'content/createtype/' + name.val())
                        .done(
                            function (data)
                            {
                                if (data !== 'false' && data !== '') {
                                    $('.panel .panel-body', typeCreator).append(data);
                                    name.val('');
                                } else {
                                    home.modal('content/error');
                                }
                            }
                        )
                        .error(
                            function ()
                            {
                                home.modal('content/error');
                            }
                        );
                    } else {
                        home.modal('content/typeerror');
                    }
                }
            );
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
                .done(
                    function (data)
                    {
                        $(element).replaceWith(data);

                        $('.creator textarea').each(function () {
                            home.resize(this);
                        });
                        $('.contents').trigger('ContentModified');
                    }
                )
                .error(
                    function ()
                    {
                        home.modal('content/error');
                    }
                )
            ;
        }
    });

    $('body').on('click', '.creator-button', function (event) {
        home.creator(event.target);
    });

    $('body').on('click', '.creator .edit-button', function (event) {
        var element = $(event.target).parents('.creator').get(0);
        var id = $(element).data('id');

        if (element && id) {
            home.creator(event.target, id);
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
                .done(
                    function (data)
                    {
                        $(element).replaceWith(data);
                        $('.contents').trigger('ContentModified');
                    }
                )
                .error(
                    function ()
                    {
                        home.modal('content/error');
                    }
                )
            ;
        }
    });

    $('.creator textarea').each(function () {
        home.resize(this);
    });

    $('body').on('keyup', '.creator textarea', function (event) {

        if (event && event.keyCode) {
            if (event.keyCode === 13 || event.keyCode === 86 || event.keyCode === 8 || event.keyCode === 46) {
                home.resize(this);
            }
        }
    });

    $('body').on('click', '.creator .addlink', function () {
        var element = $(event.target).parents('.creator').get(0);

        home.modal('content/link', 'add-link', element);
    });

    $('body').on('click', '#add-link .btn-primary', function () {
        var urls = home.findUrls($('#add-link input').val());
        var modal = $(this).parents('.modal').get(0);
        var creator = $('#add-link').data('element');

        if (urls.length > 0) {
            home.generatedContent(creator, urls[0]);

            if ($('.content-text', creator).val() === '' && $('.content-title', creator).val() === '') {
                $('.content-text', creator).val($('#add-link input').val());
            }

            $(modal).modal('hide');
        } else {
            $('.form-group', modal).addClass('has-error');
        }
    });

    $('body').on('paste', '.creator textarea', function () {
        var element = this;

        setTimeout(function () {
            var text = $(element).val();
            var urls = home.findUrls(text);

            if (urls.length > 0) {
                home.generatedContent($(element).parents('.creator').get(0), urls[0]);
            }

        }, 100);
    });

    $('body').on('click', '.generated .close', function (event) {
        $(event.target).parent().html('');
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
                .done(
                    function() {
                        $('.contents').trigger('ContentModified');
                    }
                ).error(
                        function ()
                        {
                            home.modal('content/error');
                        }
                    )
                ;
            }
        }
    });
}());
