(function () {
    'use strict';

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
        handleFrameworkCreation(false);
    });

    // framework import
    $('button#import-framework').on('click', function () {
        handleFrameworkCreation(true);
    });

    // framework export
    $(document).on('click', 'a.export-framework', function () {
        var id = this.parentNode.parentNode.dataset.id;
        window.location = Routing.generate('hevinci_export_framework', { id: id });
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

        console.log(this)

        $(this).removeClass('active');
    });

    // prevent hash change on disabled menu actions
    $(document).on('click', 'li.disabled > a', function (event) {
        event.preventDefault();
    });

    // sub-competency creation
    $(document).on('click', 'li:not(.disabled) > a.create-sub-competency', function (event) {
        event.preventDefault();
        var parentItem = getCompetencyNode(this);
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
    $(document).on('click', 'li:not(.disabled) > a.edit-competency', function (event) {
        event.preventDefault();
        var node = getCompetencyNode(this);
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
    $(document).on('click', 'li:not(.disabled) > a.delete-competency', function (event) {
        var node = getCompetencyNode(this);
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

    // ability creation
    $(document).on('click', 'li:not(.disabled) > a.create-ability', function (event) {
        event.preventDefault();
        var node = getCompetencyNode(this);
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_new_ability', { id: node.dataset.id }),
            function (data) {
                addAbility($(node), data);
                flasher.setMessage(trans('message.ability_created'));
            },
            function () {},
            'ability-form'
        );
    });

    // ability import
    $(document).on('click', 'li:not(.disabled) > a.add-ability', function (event) {
        event.preventDefault();
        var node = getCompetencyNode(this);
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_add_ability_form', { id: node.dataset.id }),
            function (data) {
                addAbility($(node), data);
                flasher.setMessage(trans('message.ability_added'));
            },
            enableAbilityTypeAhead(node.dataset.id),
            'ability-form'
        );
    });

    // ability edition
    $(document).on('click', 'a.edit-ability', function (event) {
        event.preventDefault();
        var row = this.parentNode.parentNode;
        var node = row.parentNode.parentNode.parentNode.parentNode.parentNode;
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_ability', { id: node.dataset.id, abilityId: row.dataset.id }),
            function (data) {
                sortAbilities($(row.parentNode), $(Twig.render(AbilityRow, data)));
                $(row).detach();
                flasher.setMessage(trans('message.ability_edited'));
            },
            function () {},
            'ability-form'
        );
    });

    // ability deletion
    $(document).on('click', 'a.delete-ability', function (event) {
        event.preventDefault();
        var row = this.parentNode.parentNode;
        var item = row.parentNode.parentNode.parentNode.parentNode.parentNode;

        window.Claroline.Modal.confirmRequest(
            Routing.generate('hevinci_delete_ability', { id: item.dataset.id, abilityId: row.dataset.id }),
            function () {
                var $tableBody = $(row.parentNode);
                var $item = $(item);
                $(row).remove();

                if ($tableBody.children('tr').length === 0) {
                    $tableBody.parent().css('display', 'none');
                    $item.children('i')
                        .addClass('fa-plus-square-o empty')
                        .removeClass('fa-minus-square-o collapse');
                    $item.find('a.create-sub-competency')
                        .parent()
                        .removeClass('disabled');
                }

                flasher.setMessage(trans('message.ability_deleted'));
            },
            null,
            trans('message.ability_deletion_confirm'),
            trans('ability.delete')
        );
    });

    // activities linked to ability/competency display
    $(document).on('click', 'table.framework-activities a.show-activities ', function (event) {
        event.preventDefault();
        var target = this.parentNode.parentNode;
        Claroline.Modal.fromUrl(
            Routing.generate(
                target.dataset.type === 'ability' ?
                    'hevinci_ability_activities' :
                    'hevinci_competency_activities',
                { id: target.dataset.id }
            )
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
            'scale-form',
            false
        );
    }

    function displayScaleEditionForm(scaleRow, callback) {
        var scaleId = scaleRow.dataset.id;
        window.Claroline.Modal.displayForm(
            Routing.generate('hevinci_scale', { id: scaleId, edit: callback ? 1 : 0 }),
            callback || function () {},
            function () {},
            'scale-form',
            false
        );
    }

    function handleFrameworkCreation(isImport) {
        var route = isImport ?
            'hevinci_import_framework_form' :
            'hevinci_new_framework';
        window.Claroline.Modal.displayForm(
            Routing.generate(route),
            function (data) {
                $('div.alert.alert-info').hide();
                $('table#framework-table')
                    .css('display', 'table')
                    .children('tbody')
                    .append(Twig.render(FrameworkRow, data));
                flasher.setMessage(trans('message.framework_created'));
            },
            refreshScaleElements,
            isImport ? 'framework-import-form' : 'framework-form'
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

    function getCompetencyNode(dropdownItem) {
        return dropdownItem.parentNode.parentNode.parentNode.parentNode;
    }

    function sortAbilities($tableBody, $newAbilityRow) {
        var $rows = $tableBody.children('tr');
        var newAbilityLevel = $newAbilityRow.get(0).dataset.level;
        var i = $rows.length - 1;

        while (i >= 0) {
            if ($rows.get(i).dataset.level <= newAbilityLevel) {
                $newAbilityRow.insertAfter($rows[i]);
                return;
            }
            --i;
        }

        $tableBody.prepend($newAbilityRow);
    }

    function addAbility($competency, newAbility) {
        $competency.children('i')
            .removeClass('fa-plus-square-o empty')
            .addClass('fa-minus-square-o collapse');
        $competency.children('ul.children')
            .css('display', 'block')
            .find('table.abilities')
            .css('display', 'table');
        $competency.find('a.create-sub-competency')
            .parent()
            .addClass('disabled');
        sortAbilities($competency.find('tbody'), $(Twig.render(AbilityRow, newAbility)));
    }

    function enableAbilityTypeAhead(competencyId) {
        return function () {
            var abilities = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: Routing.generate('hevinci_suggest_ability', {id: competencyId, query: 'QUERY'}),
                    wildcard: 'QUERY'
                }
            });

            abilities
                .initialize(true)
                .done(function () {
                    // without this, bloodhound keep suggesting already
                    // added abilities without making a new http request,
                    // even if a new instance is created for each form...
                    abilities.clearRemoteCache();

                    $('textarea.ability-search').typeahead(
                        {
                            minLength: 1,
                            highlight: true
                        },
                        {
                            name: 'abilities',
                            displayKey: 'name',
                            source: abilities.ttAdapter()
                        }
                    );
                });
        }
    }
})();