(function () {
    'use strict';

    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });
    var activityId = $(document).find('span#activity-id').data('id');
    var $tableBody = $(document).find('table.associated-competencies tbody');
    var $pickerModal;
    var $currentSelection;

    // framework list
    $('button.associate-competency').on('click', function () {
        $currentSelection = null; // reset in case it's already initialized
        Claroline.Modal.fromUrl(
            Routing.generate('hevinci_activity_frameworks'),
            function (modal) {
                $pickerModal = modal;
            }
        );
    });

    // framework selection
    $(document).on('click', 'a.framework', function (event) {
        var id = event.currentTarget.dataset.id;
        event.preventDefault();
        $.ajax(Routing.generate('hevinci_activity_framework_competencies', { id: id }))
            .done(function (data) {
                $pickerModal.find('.modal-body').html(data);
            })
            .error(function () {
                Claroline.Modal.error();
            });
    });

    // competency selection
    $(document).on('click', 'ul.framework li span.node-name', function (event) {
        if ($currentSelection) {
            $currentSelection.removeClass('selected');
        } else {
            $pickerModal.find('button#save').removeClass('disabled');
        }

        $currentSelection = $(this);
        $currentSelection.addClass('selected');
    });

    // association validation
    $(document).on('click', 'button#save', function () {
        var $item = $currentSelection.parent();
        var targetId = $item.data('id');
        var url = $item.data('type') === 'ability' ?
            Routing.generate('hevinci_activity_link_ability', { id: activityId, abilityId: targetId }) :
            Routing.generate('hevinci_activity_link_competency', { id: activityId, competencyId: targetId });
        $.post(url)
            .done(function (data, text, xhr) {
                var message = 'message.competency_associated';
                var category = 'success';

                if (xhr.status === 204) {
                    message = 'message.competency_already_associated';
                    category = 'warning';
                } else {
                    data.translatedType = Translator.trans(data.type, {}, 'competency');
                    $tableBody.append(Twig.render(CompetencyRow, { target: data, isEditMode: true }));
                    $tableBody.parent().css('display', 'table');
                    $('div.alert-info').remove();
                }

                $pickerModal.modal('hide');
                flasher.setMessage(trans(message), category);
            });
    });

    // association deletion
    $(document).on('click', 'a.delete-association', function (event) {
        event.preventDefault();
        var row = this.parentNode.parentNode;
        var targetId = row.dataset.id;
        var targetType = row.dataset.type;
        var url = row.dataset.type === 'ability_' ?
            Routing.generate('hevinci_activity_remove_ability', { id: activityId, abilityId: targetId }) :
            Routing.generate('hevinci_activity_remove_competency', { id: activityId, competencyId: targetId });
        Claroline.Modal.confirmRequest(
            url,
            function () {
                $('tr[data-type=' + targetType + '][data-id=' + targetId + ']').remove();
                flasher.setMessage(trans('message.competency_association_removed'));
            },
            null,
            trans('message.' + targetType + 'association_deletion_confirm'),
            trans('competency.delete_association')
        );
    });

    function trans(message) {
        return Translator.trans(message, 'competency');
    }
}());