(function () {
    'use strict';

    var flasher = new HeVinci.Flasher({ element: $('.panel-body')[0], animate: false });
    var picker = new HeVinci.CompetencyPicker({ callback: onCompetencySelection });
    var resourceId = $(document).find('span#resource-id').data('id');
    var $tableBody = $(document).find('table.associated-competencies tbody');

    $('button.associate-competency').on('click', picker.open.bind(picker));
    $(document).on('click', 'a.delete-association', onAssociationDeletion);

    function onCompetencySelection(selection) {
        var url = selection.targetType === 'ability' ?
            Routing.generate('hevinci_resource_link_ability', { id: resourceId, abilityId: selection.targetId }) :
            Routing.generate('hevinci_resource_link_competency', { id: resourceId, competencyId: selection.targetId });
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

                picker.close();
                flasher.setMessage(trans(message), category);
            });
    }

    function onAssociationDeletion(event) {
        event.preventDefault();
        var row = this.parentNode.parentNode;
        var targetId = row.dataset.id;
        var targetType = row.dataset.type;
        var url = row.dataset.type === 'ability_' ?
            Routing.generate('hevinci_resource_remove_ability', { id: resourceId, abilityId: targetId }) :
            Routing.generate('hevinci_resource_remove_competency', { id: resourceId, competencyId: targetId });
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
    }

    function trans(message) {
        return Translator.trans(message, 'competency');
    }
}());