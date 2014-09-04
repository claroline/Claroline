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

    $('body')
        .on('click', '#add-facet-btn', function(event) {
            var url = Routing.generate('claro_admin_facet_form');
            displayForm(url, 'form_facet_creation', addFacet);
        })
        .on('click', '.add-field-to-facet', function(event) {
            var url = Routing.generate('claro_admin_facet_field_form', {'facet': $(event.target).attr('data-facet-id')});
            displayForm(url, 'form_field_creation', addField);
        })
        .on('click', '.facet-reorder-right-btn', function(event) {
            var facetId = $($(event.currentTarget)[0].parentElement.parentElement.parentElement.parentElement).attr('data-facet-id');
            var url = Routing.generate('claro_admin_move_facet_up', {'facet': facetId});
            $.ajax({
                url: url,
                success: function(data) {
                    moveFacetRight(facetId)
                }
            });
        })
        .on('click', '.facet-reorder-left-btn', function(event) {
            var facetId = $($(event.currentTarget)[0].parentElement.parentElement.parentElement.parentElement).attr('data-facet-id');
            var url = Routing.generate('claro_admin_move_facet_down', {'facet': facetId});
            $.ajax({
                url: url,
                success: function(data) {
                    moveFacetLeft(facetId)
                }
            });
        })
        .on('click', '.facet-delete-btn', function(event) {
            var facetId = $($(event.currentTarget)[0].parentElement.parentElement.parentElement.parentElement).attr('data-facet-id');
            var facetName = $($(event.currentTarget)[0].parentElement.parentElement.parentElement.parentElement).attr('data-facet-name');
            var url = Routing.generate('claro_admin_facet_remove', {'facet': facetId});
            executeRequestConfirm(
                url,
                removeFacet,
                facetId,
                Translator.get('platform:remove_facet_confirm', {'name': facetName}),
                Translator.get('platform:remove_facet')
            );
        })
        .on('click', '.facet-rename-btn', function(event) {
            var facetId = $($(event.currentTarget)[0].parentElement.parentElement.parentElement.parentElement).attr('data-facet-id');
            var url = Routing.generate('claro_admin_facet_edit_form', {'facet': facetId})
            displayForm(url, 'form_facet_edit', editFacet);
        })
        .on('click', '.edit-field-facet', function(event) {
            var fieldFacetId = $($(event.currentTarget)[0].parentElement).attr('data-field-facet-id');
            var url = Routing.generate('claro_admin_field_facet_edit_form', {'fieldFacet': fieldFacetId});
            displayForm(url, 'form_field_edit', editField);
        })
        .on('click', '.remove-field-facet', function(event) {
            var fieldFacetId = $($(event.currentTarget)[0].parentElement).attr('data-field-facet-id');
            var fieldFacetName = $($(event.currentTarget)[0].parentElement).attr('data-field-facet-name');
            var url = Routing.generate('claro_admin_remove_field_facet', {'fieldFacet': fieldFacetId});
            executeRequestConfirm(
                url,
                removeField,
                fieldFacetId,
                Translator.get('platform:remove_field_confirm', {'name': fieldFacetName}),
                Translator.get('platform:remove_field')
            );
        })
        .on('click', '.facet-role-btn', function(event) {
            var facetId = $($(event.currentTarget)[0].parentElement.parentElement.parentElement.parentElement).attr('data-facet-id');
            var url = Routing.generate('claro_admin_facet_role_form', {'facet': facetId});
            displayForm(url, 'form-facet-roles', function(){})
        }).
        on('click', '.role-field-facet', function(event) {
            var fieldFacetId = $($(event.currentTarget)[0].parentElement).attr('data-field-facet-id');
            var url = Routing.generate('claro_admin_field_role_form', {'field': fieldFacetId});
            displayForm(url, 'form-field-roles', function(){});
        }).
        on('click', '#edit-general-facet-btn', function(event){
            event.preventDefault();
            submitForm('form-facet-general', submitGeneralForm);
        });

    /*******************/
    /* SORTABLE FIELDS */
    /*******************/

    $('.list-fields').sortable({
        update: function(event, ui) {
            var facetId = $($(event.target)[0]).attr('data-facet-id');
            var url = Routing.generate('claro_admin_field_facet_order', {'facet': facetId});
            $.ajax({
                url: url,
                data: {ids: $(event.target).sortable('toArray')},
                success: function() { }
            })
        }
    });

    /***********/
    /* HELPERS */
    /***********/

    function displayForm(url, formId, successHandler) {
        $.ajax({
            url: url,
            success: function(data, textStatus, jqXHR) {
                window.Claroline.Modal.hide();
                window.Claroline.Modal.create(data).on('click', 'button.btn', function(event) {
                    event.preventDefault();
                    submitForm(formId, successHandler);
                });
            }
        });
    }

    function submitForm(formId, successHandler) {
        var formData = new FormData(document.getElementById(formId));
        var url = $('#' + formId).attr('action');
        $.ajax({
            url: url,
            data: formData,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data, textStatus, jqXHR) {
                if (jqXHR.getResponseHeader('Content-Type') === 'application/json') {
                    $('.modal').modal('hide');
                    successHandler(data, textStatus, jqXHR);
                } else {
                    $('#facet-modal').replaceWith(data);
                }
            }
        });
    }

    function executeRequestConfirm(url, successHandler, successParameter, body, header) {
        var html = Twig.render(
            ModalWindow,
            {'confirmFooter': true, 'modalId': 'confirm-modal', 'body': body, 'header': header}
        );

        $('body').append(html);
        //display validation modal
        $('#confirm-modal').modal('show');
        //destroy the modal when hidden
        $('#confirm-modal').on('hidden.bs.modal', function () {
            $(this).remove();
        });

        $('#confirm-ok').on('click', function(event) {
            $.ajax({
                url: url,
                success: function(data) {
                    successHandler(event, successParameter);
                    $('#confirm-modal').modal('hide');
                }
            });
        });
    }

    /*************/
    /* CALLBACKS */
    /*************/

    var addFacet = function(data, textStatus, jqXHR) {
        $('#add-facet-btn').before(Twig.render(FacetTab, {'facet': data}));
        $('#facet-pane').append(Twig.render(FacetPane, {'facet': data}));
    }

    var removeFacet = function(event, facetId) {
        $('#facet-pane-' + facetId).remove();
        $('#tab-facet-' + facetId).remove();
    }

    var moveFacetRight = function(facetId) {
        var next = $('#tab-facet-' + facetId).next().attr('id');
        $('#tab-facet-' + facetId).insertAfter('#' + next);
    }

    var moveFacetLeft = function(facetId) {
        var prev = $('#tab-facet-' + facetId).prev().attr('id');

        if (prev !== 'general-tab-btn') {
            $('#tab-facet-' + facetId).insertBefore('#' + prev);
        }
    }

    var addField = function(data, textStatus, jqXHR) {
        $('#lu-facet-' + data['facet_id']).append(Twig.render(FieldElement, {'field': data}))
    }

    var removeField = function(event, fieldId) {
        $('#field-' + fieldId).remove();
    }

    var editFacet = function(data, textStatus, jqXHR) {
        $('#tab-facet-' + data['id']).children().html(Twig.render(FacetTabInnerHtml, {'facet': data}));
    }

    var editField = function(data, textStatus, jqXHR) {
        $('#field-' + data['id']).html(Twig.render(FieldElementInnerHtml, {'field': data}));
    }

    var submitGeneralForm = function(data, textStatus, jqXHR) {
        var flashbag =
            '<div class="alert alert-success">' +
            '<a data-dismiss="alert" class="close" href="#" aria-hidden="true">&times;</a>' +
            Translator.get('platform:edit_profile_success') +
            '</div>';

        $('.panel-body').first().prepend(flashbag);
    }
})();