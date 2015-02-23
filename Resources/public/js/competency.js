(function () {
    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });

    // scale creation from the framework management page
    $('button#create-first-scale').on('click', function () {
        displayScaleCreationForm(refreshScaleElements);
    });

    // scale creation from the scale management page
    $('button#create-scale').on('click', function () {
        displayScaleCreationForm(function (data) {
            $('div#no-scale-info').remove();
            $('table#scale-table tbody').append(Twig.render(ScaleRow, data));
            $('table#scale-table').css('display', 'table');
        });
    });

    // scale details
    $(document).on('click', 'a.view-scale', function () {
        displayScaleEditionForm(this.parentNode.parentNode);
    });

    // scale edition
    $(document).on('click', 'a.edit-scale', function () {
        var row = this.parentNode.parentNode;
        displayScaleEditionForm(row, function (data) {
            $(row).replaceWith(Twig.render(ScaleRow, data));
            flasher.setMessage(trans('message.scale_edited'));
        });
    });

    // scale deletion
    $(document).on('click', 'a.delete-scale', function () {
        var row = this.parentNode.parentNode;
        var scaleId = row.dataset.id;
        window.Claroline.Modal.confirmRequest(
            Routing.generate('hevinci_delete_scale', { id: scaleId }),
            function () {
                $(row).remove();
                flasher.setMessage(trans('message.scale_deleted'));
            },
            null,
            trans('message.scale_deletion_confirm'),
            trans('scale.delete')
        );
    });

    // framework creation
    $('button#create-framework').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_framework'),
            function (data) {
                $('span#status-info').remove();
                $('table#framework-table')
                    .css('display', 'table')
                    .children('tbody')
                    .append(Twig.render(FrameworkRow, data));
                flasher.setMessage(trans('message.framework_created'));
            },
            refreshScaleElements,
            'framework-form'
        );
    });

    // framework edition
    $(document).on('click', 'a.edit-framework', function (event) {
        var row = this.parentNode.parentNode;
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_edit_framework_form', { id: row.dataset.id }),
            function (data) {
                $(row).replaceWith(Twig.render(FrameworkRow, data));
                flasher.setMessage(trans('message.framework_edited'));
            },
            function () {},
            'framework-form'
        );
    });

    // framework deletion
    $(document).on('click', 'a.delete-framework', function (event) {
        var row = this.parentNode.parentNode;
        deleteCompetency(event, 'framework', row.dataset.id, function () {
            $(row).remove();
            flasher.setMessage(trans('message.framework_deleted'));
        });
    });

    // competency tree expansion
    $(document).on('click', 'ul.framework li > i.expand', function () {
        $(this).removeClass('expand')
            .addClass('collapse')
            .removeClass('fa-plus-square-o')
            .addClass('fa-minus-square-o');
        $(this.parentNode).children('ul.children').css('display', 'block');
    });

    // competency tree collapsing
    $(document).on('click', 'ul.framework li > i.collapse', function () {
        $(this).removeClass('collapse')
            .addClass('expand')
            .removeClass('fa-minus-square-o')
            .addClass('fa-plus-square-o');
        $(this.parentNode).children('ul.children').css('display', 'none');
    });

    // expand/collapse the whole competency tree
    $('button#control-expansion').on('click', function () {
        if (this.dataset.status === 'collapsed') {
            $('ul.framework ul.children').css('display', 'block');
            $('ul.framework i.expand')
                .removeClass('fa-plus-square-o expand')
                .addClass('fa-minus-square-o collapse');
            this.dataset.status = 'expanded';
            $(this).text(trans('collapse_all'));
        } else {
            $('ul.framework ul.children').css('display', 'none');
            $('ul.framework i.collapse')
                .removeClass('fa-minus-square-o collapse')
                .addClass('fa-plus-square-o expand');
            this.dataset.status = 'collapsed';
            $(this).text(trans('expand_all'));
        }
    });

    // sub-competency creation
    $(document).on('click', 'a.create-sub-competency', function (event) {
        event.preventDefault();
        var parentItem = this.parentNode.parentNode.parentNode.parentNode;
        var parentId = parentItem.dataset.id;
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_competency', { id: parentId }),
            function (data) {
                $(parentItem).children('ul.children')
                    .append(Twig.render(CompetencyItem, { competency: data, level: data.level }))
                    .css('display', 'block');
                $(parentItem).children('i')
                    .removeClass('fa-plus-square-o empty')
                    .addClass('fa-minus-square-o collapse');
                flasher.setMessage(trans('message.sub_competency_created'));
            },
            function () {},
            'competency-form'
        );
    });

    // competency edition
    $(document).on('click', 'a.edit-competency', function (event) {
        event.preventDefault();
        var node = this.parentNode.parentNode.parentNode.parentNode;
        var competencyId = node.dataset.id;
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_competency', { id: competencyId }),
            function (data) {
                $(node).children('span.dropdown')
                    .find('span.competency-name')
                    .text(data.name);
                flasher.setMessage(trans('message.competency_edited'));
            },
            function () {},
            'competency-form'
        );
    });

    // competency deletion
    $(document).on('click', 'a.delete-competency', function (event) {
        var node = this.parentNode.parentNode.parentNode.parentNode;
        deleteCompetency(event, 'competency', node.dataset.id, function () {
            if ($(node.parentNode).length === 1) {
                $(node.parentNode.parentNode).children('i')
                    .removeClass('fa-minus-square-o collapse')
                    .addClass('fa-plus-square-o empty');
            }

            $(node).remove();
            flasher.setMessage(trans('message.competency_deleted'));
        });
    });

    function displayScaleCreationForm(successCallback) {
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_scale'),
            function (data) {
                flasher.setMessage(trans('message.scale_created'));
                successCallback(data);
            },
            function () {},
            'scale-form'
        );
    }

    function displayScaleEditionForm(scaleRow, callback) {
        var scaleId = scaleRow.dataset.id;
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_scale', { id: scaleId, edit: callback ? 1 : 0 }),
            callback || function () {},
            function () {},
            'scale-form'
        );
    }

    function deleteCompetency(event, target, id, callback) {
        event.preventDefault();
        window.Claroline.Modal.confirmRequest(
            Routing.generate('hevinci_delete_competency', { id: id }),
            callback,
            null,
            trans('message.' + target + '_deletion_confirm'),
            trans(target + '.delete')
        );
    }

    function refreshScaleElements() {
        $('button#create-first-scale').css('display', 'none');
        $('a#manage-scales').css('display', 'inline-block');
        $('span#status-info').html(trans('info.no_frame'));
    }

    function trans(message) {
        return Translator.trans(message, {}, 'competency');
    }
})();