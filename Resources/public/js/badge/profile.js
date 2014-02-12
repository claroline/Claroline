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
        var addCollectionButton            = $("#add_collection");
        var collectionsList                = $("#collections_list");
        var newCollectionTemplate          = collectionsList.attr("data-collection-template");
        var apiUrl                         = collectionsList.attr("data-action-url");
        var noCollectionElement            = $("#no_collection");
        var deletingCollectionElement      = $("#deleting_collection");
        var deletingCollectionBadgeElement = $(collectionsList.attr("data-delete-collection-badge-template"));

        $(".badge_container").draggable({
            helper: "clone",
            revert: "invalid",
            scroll: false,
            cursor: "move",
            zIndex: 100
        });

        var clarobadgeDragOptions = {
            scroll: false,
            revert: "invalid",
            cursor: "move"
        };
        clarobadgeDragOptions.start = function(event, ui) {
            var collectionContainer = $(event.target).parents('li.collection');
            $(event.target).before(collectionContainer.find(".loading_badge"));
            collectionContainer.after(deletingCollectionBadgeElement);
            deletingCollectionBadgeElement.show();
        };
        clarobadgeDragOptions.stop = function(event, ui) {
            deletingCollectionBadgeElement.hide("fast");
        };
        $(".clarobadge").draggable(clarobadgeDragOptions);

        var clarobagdeDeleteDropOptions = {
            hoverClass:  "drag_hover",
            accept:      ".clarobadge"
        };
        clarobagdeDeleteDropOptions.drop = function(event, ui) {
            deleteBadgeFromCollection($(event.target), $(ui.draggable));
        };
        deletingCollectionBadgeElement.droppable(clarobagdeDeleteDropOptions);
        deletingCollectionBadgeElement.hide();

        function deleteBadgeFromCollection(droppingZone, draggable) {
            $("span", droppingZone).hide();
            $("img", droppingZone).show();
            var collectionContainer = $(".collection[data-id=" + draggable.attr("data-collection-id") + "]");

            var badges = {};
            $(".badges .clarobadge", collectionContainer).each(function(index, element) {
                badges[index + 1] = $(element).attr("data-id");
            });
            delete badges[draggable.attr("data-id")];

            var collectionUpdateRequest = $.ajax({
                url: apiUrl + collectionContainer.attr("data-id"),
                type: 'PUT',
                data: {
                    'badge_collection_form[name]':   $(".collection_title_input", collectionContainer).val(),
                    'badge_collection_form[badges]': badges
                }
            });

            collectionUpdateRequest
                .success(function(data) {
                    draggable.hide("fast", function() {
                        $(this).remove();

                        var nbBadges = $(".badges .clarobadge", collectionContainer).length;
                        if (0 == nbBadges) {
                            $(".no_badge", collectionContainer).show();
                        }
                    });
                })
                .fail(function() {
                    console.log("error removing badge to collection");
                    draggable.animate({'top':'0px', 'left': '0px'}, 500, 'easeInOutCubic');
                    draggable.effect("highlight", {color: '#d9534f'}, 1500);
                    $("img", droppingZone).hide();
                    $("span", droppingZone).show();
                })
                .always(function() {
                    $("img", droppingZone).hide();
                    $("span", droppingZone).show();
                    deletingCollectionBadgeElement.hide("slow");
                });
        }

        var dropOptions = {
            activeClass: "collection_state_drag_start",
            hoverClass:  "collection_state_hover",
            accept:      ".badge_container"
        };

        dropOptions.drop = function (event, ui) {
            var droppingZone = $(event.target);

            if (droppingZone.hasClass('editing')) {
                doUpdateCollectionTitle(droppingZone);
            }

            $(ui.draggable).each(function(index, element) {
                var element = $(element);

                var existingBadgeInCollection = $(".clarobadge[data-id=" + element.attr("data-id") + "]", droppingZone);
                if (0 == existingBadgeInCollection.length) {
                    addBadgeToCollection(droppingZone, element);
                }
                else {
                    existingBadgeInCollection.each(function(index, element) {
                        $(element).effect("highlight", {color: '#d9534f'}, 1500);
                    });
                }
            });
        }

        $(".collection").droppable(dropOptions);

        function addBadgeToCollection(collectionContainer, badgeElement) {
            var nbBadges = collectionContainer.find(".clarobadge").length;

            if (0 == nbBadges) {
                collectionContainer.find(".no_badge").hide();
            }

            var loadingBadge = $(".loading_badge", collectionContainer);
            loadingBadge.show("fast");

            var badges = {0: badgeElement.attr("data-id")};
            $(".badges .clarobadge", collectionContainer).each(function(index, element) {
                badges[index + 1] = $(element).attr("data-id");
            });

            var collectionUpdateRequest = $.ajax({
                url: apiUrl + collectionContainer.attr("data-id"),
                type: 'PUT',
                data: {
                    'badge_collection_form[name]':   $(".collection_title_input", collectionContainer).val(),
                    'badge_collection_form[badges]': badges
                }
            });

            collectionUpdateRequest
                .success(function(data) {
                    doAddBadgeToCollection(collectionContainer, badgeElement);
                })
                .fail(function() {
                    if (0 == nbBadges) {
                        collectionContainer.find(".no_badge").show();
                    }
                    loadingBadge.hide();
                    console.log("error adding badge to collection");
                });
        }

        function doAddBadgeToCollection(collectionContainer, badgeElement) {
            $(".loading_badge", collectionContainer).fadeOut("fast", function() {
                var badgeTemplate = $('<li class="clarobadge" data-id="' + badgeElement.attr("data-id") + '" data-collection-id="' + collectionContainer.attr("data-id") + '">' + badgeElement.attr("data-image") + '</li>');

                badgeTemplate.draggable(clarobadgeDragOptions);

                $(this).before(badgeTemplate);
            });
        }

        addCollectionButton.click(function(event) {
            var addButton = $(this);
            addButton.button('loading');

            var newCollection             = $(newCollectionTemplate);
            var collectionCreationRequest = $.post(apiUrl, {'badge_collection_form[name]': $(".collection_title_input", newCollection).val()});

            collectionCreationRequest
                .success(function(data) {
                    addCollection(newCollection, data)
                })
                .fail(function() {
                    console.log("error adding collection");
                })
                .always(function () {
                    addButton.button('reset');
                });
        });

        function addCollection(newCollection, data) {
            var existedCollection = $(".collection", collectionsList);
            if (0 == existedCollection.length) {
                noCollectionElement.hide("fast");
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
                .appendTo($("#collections_list"))
                .show('fast');

            collectionsList.animate({scrollTop: newCollection.offset().top}, 500, 'easeInOutCubic');

            $(".btn-delete", newCollection).confirmModal({'confirmCallback': confirmDeleteCollection});
        }

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
                updateCollectionTitle($(event.target).parents('li.collection'));
            })
            .on('click', '.btn-edit-cancel',function(event){
                doUpdateCollectionTitle($(event.target).parents('li.collection'));
            });

        function updateCollectionTitle(collectionContainer) {
            var editButton = $(".btn-success", collectionContainer);
            editButton.button('loading');

            var collectionTitleInput = $(".collection_title_input", collectionContainer);
            collectionTitleInput.attr('disabled','disabled');

            var collectionUpdateRequest = $.ajax({
                url: apiUrl + collectionContainer.attr("data-id"),
                type: 'PUT',
                data: {
                    'badge_collection_form[name]': $(".collection_title_input", collectionContainer).val()
                }
            });

            collectionUpdateRequest
                .success(function(data) {
                    doUpdateCollectionTitle(collectionContainer);
                })
                .fail(function() {
                    console.log("error update collection");
                })
                .always(function () {
                    editButton.button('reset');
                    collectionTitleInput.removeAttr('disabled');
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
            $(".btn-edit-cancel", collectionContainer).hide();
            $(".btn-delete", collectionContainer).show();
        }

        function makeCollectionTitleEditable(collectionContainer) {
            var collectionTitle      = $(".collection_title", collectionContainer);
            var collectionTitleInput = $(".collection_title_input", collectionContainer);

            collectionContainer.addClass('editing');
            collectionTitle.hide();
            collectionTitleInput.show();
            collectionTitleInput.focus();

            $(".btn-edit", collectionContainer).removeClass('btn-primary').addClass('btn-success');
            $(".btn-delete", collectionContainer).hide();
            $(".btn-edit-cancel", collectionContainer).show();
        }

        $(".btn-delete", collectionsList).confirmModal({'confirmCallback': confirmDeleteCollection});

        function confirmDeleteCollection(element)
        {
            var collectionContainer = $(element).parents('li.collection');

            collectionContainer.after(deletingCollectionElement);
            var newHeight = (parseFloat(collectionContainer.css("height")) + 12) + "px";
            var newWidth  = (parseFloat(collectionContainer.css("width")) + 2) + "px";
            deletingCollectionElement.css({
                height: newHeight,
                width:  newWidth,
                top:    collectionContainer.position().top + "px"
            })

            deletingCollectionElement.show();

            var collectionDeleteRequest = $.ajax({
                url:  apiUrl + collectionContainer.attr("data-id"),
                type: 'DELETE'
            });

            collectionDeleteRequest
                .success(function(data) {
                    deleteCollection(collectionContainer);
                })
                .fail(function() {
                    console.log("error delete collection");
                })
                .always(function() {
                    deletingCollectionElement.hide("fast");
                });
        }

        function deleteCollection(collectionContainer) {
            collectionContainer.hide("fast", function() {
                $(this).remove();

                var existedCollection = $(".collection", collectionsList);
                if (0 == existedCollection.length) {
                    noCollectionElement.show();
                }
            });
        }
    });
})(jQuery);