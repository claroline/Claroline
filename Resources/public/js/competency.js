(function () {
    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0] });

    // scale creation from the framework management page
    $('button#create-first-scale').on('click', function () {
        displayScaleCreationForm(function () {
            $('button#create-scale').css('display', 'none');
            $('a#manage-scales').css('display', 'inline-block');
            $('span#status-info').html(trans('info.no_frame'));
        });
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
    $(document).on('click', 'a.scale-view', function () {
        displayScaleEditionForm(this);
    });

    // scale edition
    $(document).on('click', 'a.scale-edit', function () {
        displayScaleEditionForm(this, function (data) {
            $('table#scale-table td:first').html(data.name);
            flasher.setMessage(trans('message.scale_edited'));
        });
    });

    // scale deletion
    $(document).on('click', 'a.scale-delete', function () {
        var row = this.parentNode.parentNode;
        var scaleId = row.dataset.id;
        console.log(scaleId);
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

    function trans(message) {
        return Translator.trans(message, {}, 'competency');
    }
})();