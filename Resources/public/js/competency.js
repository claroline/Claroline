(function () {
    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0] });

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
        displayScaleEditionForm(this);
    });

    // scale edition
    $(document).on('click', 'a.edit-scale', function () {
        displayScaleEditionForm(this, function (data) {
            $('table#scale-table td:first').html(data.name);
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

    // framework creation form
    $('button#create-framework').on('click', function () {
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_framework'),
            function (data) {
                $('span#status-info').remove();
                $('ul#framework-list')
                    .css('display', 'block')
                    .append(Twig.render(FrameworkItem, data));
                flasher.setMessage(trans('message.framework_created'));
            },
            refreshScaleElements,
            'framework-form'
        );
    });

    // framework deletion
    $('button#delete-framework').on('click', function () {
        window.Claroline.Modal.confirmRequest(
            Routing.generate('hevinci_delete_framework', { id: this.dataset.id }),
            function () {
                document.location = Routing.generate('hevinci_frameworks');
            },
            null,
            trans('message.framework_deletion_confirm'),
            trans('framework.delete')
        );
    });

    // competency tree expansion
    $(document).on('click', 'ul.framework li i.expand', function () {
        $(this).removeClass('expand')
            .addClass('collapse')
            .removeClass('fa-plus-square-o')
            .addClass('fa-minus-square-o');
    });

    // competency tree collapsing
    $(document).on('click', 'ul.framework li i.collapse', function () {
        $(this).removeClass('collapse')
            .addClass('expand')
            .removeClass('fa-minus-square-o')
            .addClass('fa-plus-square-o');
    });

    // sub-competency creation
    $(document).on('click', 'a.create-sub-competency', function () {
        var parentItem = this.parentNode.parentNode.parentNode.parentNode;
        var parentId = parentItem.dataset.id;
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_competency', { id: parentId }),
            function (data) {
                $(parentItem).children('ul.children')
                    .append(Twig.render(CompetencyItem, { competency: data }));
                flasher.setMessage(trans('message.sub_competency_created'));
            },
            function () {},
            'competency-form'
        );
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

    function displayScaleEditionForm(node, callback) {
        var scaleId = node.parentNode.parentNode.dataset.id;
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_scale', { id: scaleId, edit: callback ? 1 : 0 }),
            callback || function () {},
            function () {},
            'scale-form'
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