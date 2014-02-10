/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function($) {
    "use strict";

    $(function() {
        var addCollectionButton   = $("#add_collection");
        var collectionsList       = $("#collections_list");
        var newCollectionTemplate = collectionsList.attr("data-collection-template");
        var apiUrl                = collectionsList.attr("data-action-url");
        var noCollectionElement   = $("#no_collection");

        $(".badge_container").draggable({
            helper: 'clone',
            revert: "invalid"
        });

        $(".badge_container")
            .on("dragstart", function(event, ui) {
                $(".collection").addClass("collection_state_drag_start");
            })
            .on("dragstop", function(event, ui) {
                $(".collection").removeClass("collection_state_drag_start");
            });

        var dropOptions = {
            activeClass: "collection_state_default",
            hoverClass: "collection_state_hover",
            drop: onDrop
        };

        function onDrop(event, ui) {
            var droppingZone = $(event.target);
            var nbBadges     = droppingZone.find(".clarobadge").length;

            if (0 == nbBadges) {
                droppingZone.find(".no_badge").hide();
            }

            if (droppingZone.hasClass('editing')) {
                updateCollectionTitle(droppingZone);
            }

            $(ui.draggable).each(function(index, element) {
                var element = $(element);

                var existingBadgeInCollection = $(".clarobadge[data-id=" + element.attr("data-id") + "]", droppingZone);
                if (0 == existingBadgeInCollection.length) {
                    $("ul", droppingZone).append('<li class="clarobadge" data-id="' + element.attr("data-id") + '">' + element.attr("data-image") + '</li>');
                }
                else {
                    existingBadgeInCollection.each(function(index, element) {
                        $(element).effect("highlight", {color: '#5bc0de'}, 1500);
                    });
                }
            });
        }

        $(".collection").droppable(dropOptions);

        addCollectionButton.click(function(event) {
            var addButton = $(this);
            addButton.button('loading');

            var newCollection             = $(newCollectionTemplate);
            var collectionCreationRequest = $.post(apiUrl, {'badge_collection_form[name]': $(".collection_title_input", newCollection).val()});

            collectionCreationRequest
                .success(function(data) {
                    var existedCollection = $(".collection", collectionsList);
                    if (0 == existedCollection.length) {
                        noCollectionElement.addClass("hidden");
                    }
                    else {
                        existedCollection
                            .filter(".editing")
                            .each(function(index, element) {
                                doUpdateCollectionTitle($(element));
                            });
                    }

                    $(newCollection).attr("data-id", data.collection.id);

                    newCollection
                        .droppable(dropOptions)
                        .prependTo($("#collections_list"))
                        .show('fast');

                    $(".btn-delete", newCollection).confirmModal({'confirmCallback': confirmDeleteCollection});
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function () {
                    addButton.button('reset');
                });
        });

        $(collectionsList).on('keydown', 'input',function(event){
            if (event.which == 13) {
                event.preventDefault();
                updateCollectionTitle($(event.target).parents('li.collection'));
            }
        });

        $(collectionsList)
            .on('click', '.btn-edit',function(event){
                makeCollectionTitleEditable($(event.target).parents('li.collection'));
            })
            .on('click', '.btn-success',function(event){
                var collectionContainer = $(event.target).parents('li.collection');
                $(".btn-success", collectionContainer).button('loading');
                updateCollectionTitle(collectionContainer);
            });

        function updateCollectionTitle(collectionContainer) {
            var collectionUpdateRequest = $.ajax({
                url: apiUrl + collectionContainer.attr("data-id"),
                type: 'PUT',
                data: {
                    'badge_collection_form[name]': $(".collection_title_input", collectionContainer).val()
                }
            });
            var editButton = $(".btn-success", collectionContainer);

            collectionUpdateRequest
                .success(function(data) {
                    doUpdateCollectionTitle(collectionContainer);
                })
                .fail(function() {
                    console.log("error");
                })
                .always(function () {
                    editButton.button('reset');
                });
        }

        function doUpdateCollectionTitle(collectionContainer) {
            var collectionTitle      = $(".collection_title", collectionContainer);
            var collectionTitleInput = $(".collection_title_input", collectionContainer);

            collectionTitle
                .html(collectionTitleInput.val())
                .show();

            collectionTitleInput.hide();
            collectionContainer.removeClass('editing');

            $(".btn-edit", collectionContainer).removeClass('btn-success').addClass('btn-primary');
        }

        function makeCollectionTitleEditable(collectionContainer) {
            var collectionTitle      = $(".collection_title", collectionContainer);
            var collectionTitleInput = $(".collection_title_input", collectionContainer);

            collectionContainer.addClass('editing');
            collectionTitle.hide();
            collectionTitleInput.show();
            collectionTitleInput.focus();

            $(".btn-edit", collectionContainer).removeClass('btn-primary').addClass('btn-success');
        }

        $(".btn-delete", collectionsList).confirmModal({'confirmCallback': confirmDeleteCollection});

        function confirmDeleteCollection(element)
        {
            var collectionContainer     = $(element).parents('li.collection');
            var collectionUpdateRequest = $.ajax({
                url:  apiUrl + collectionContainer.attr("data-id"),
                type: 'DELETE'
            });

            collectionUpdateRequest
                .success(function(data) {
                    deleteCollection(collectionContainer);
                })
                .fail(function() {
                    console.log("error");
                });
        }

        function deleteCollection(collectionContainer) {
            collectionContainer.remove();

            var existedCollection = $(".collection", collectionsList);
            if (0 == existedCollection.length) {
                noCollectionElement.removeClass("hidden");
            }
        }
    });
})(jQuery);