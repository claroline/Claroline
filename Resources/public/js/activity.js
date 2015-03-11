(function () {
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

    $(document).on('click', 'ul.framework li span.node-name', function (event) {
        if ($currentSelection) {
            $currentSelection.removeClass('selected');
        } else {
            $pickerModal.find('button#save').removeClass('disabled');
        }

        $currentSelection = $(this);
        $currentSelection.addClass('selected');
    });

    $(document).on('click', 'button#save', function () {
        var activityId = $(document).find('span#activity-id').data('id');
        var $item = $currentSelection.parent();
        var targetId = $item.data('id');
        var url = $item.data('type') === 'ability' ?
            Routing.generate('hevinci_activity_link_ability', { id: activityId, abilityId: targetId }) :
            'FALSE URL';
            //Routing.generate('hevinci_activity_link_competency', { id: activityId, competencyId: targetId });
        $.post(url)
            .done(function () {
                alert('OK')
            });
    });
}());