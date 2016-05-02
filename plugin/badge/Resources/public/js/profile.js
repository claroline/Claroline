(function($) {
    "use strict";

    $(function() {
        var addCollectionButton = $("#add_collection");
        var collectionsList = $("#collections_list");
        var badgeList = $("#badge_list");
        var newCollectionTemplate = collectionsList.attr("data-collection-template");
        var apiUrl = collectionsList.attr("data-action-url");
        var badgeApiUrl = badgeList.attr("data-action-url");
        var noCollectionElement = $("#no_collection");
        var deletingCollectionElement = $("#deleting_collection");
        var deletingCollectionBadgeElement = $(collectionsList.attr("data-delete-collection-badge-template"));
        var errorContainer = $("#error_container");

        $("button.close", errorContainer).click(function(event) {
            errorContainer.hide();
        });

        $(".badge_management_container").ajaxSend(function() {
            errorContainer.hide();
        });

        var clarobadgeDragOptions = {
            scroll: false,
            revert: "invalid",
            cursor: "move"
        };
        clarobadgeDragOptions.start = function(event, ui) {
            var collectionContainer = $(event.target).parents('li.collection');
            $(event.target)
              .before(collectionContainer.find(".loading_badge"))
              .data("dropped", false);
            collectionContainer.after(deletingCollectionBadgeElement);
            deletingCollectionBadgeElement.show();
        };
        clarobadgeDragOptions.stop = function(event, ui) {
            if (!$(event.target).data("dropped")) {
                deletingCollectionBadgeElement.hide("fast");
            }
        };
        $(".clarobadge").draggable(clarobadgeDragOptions);

        var clarobagdeDeleteDropOptions = {
            hoverClass:  "drag_hover",
            accept:      ".clarobadge"
        };
        clarobagdeDeleteDropOptions.drop = function(event, ui) {
            var draggable = $(ui.draggable).data("dropped", true);
            deleteBadgeFromCollection($(event.target), draggable);
        };
        deletingCollectionBadgeElement.droppable(clarobagdeDeleteDropOptions);
        deletingCollectionBadgeElement.hide();

        function deleteBadgeFromCollection(droppingZone, draggable) {
            $("span", droppingZone).hide();
            $("img", droppingZone).show();
            var collectionContainer = $(".collection[data-id=" + draggable.attr("data-collection-id") + "]");

            var userBadges = {};
            $(".badges .clarobadge", collectionContainer).each(function(index, element) {
                userBadges[index + 1] = $(element).attr("data-id");
            });
            delete userBadges[draggable.attr("data-id")];

            var collectionUpdateRequest = $.ajax({
                url: apiUrl + collectionContainer.attr("data-id"),
                type: 'PATCH',
                data: {
                    'badge_collection_form[userBadges]': userBadges
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
                  displayError('remove_badge_from_collection_error');
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
        };

        $(".collection").droppable(dropOptions);

        function addBadgeToCollection(collectionContainer, badgeElement) {
            var nbBadges = collectionContainer.find(".clarobadge").length;

            if (0 == nbBadges) {
                collectionContainer.find(".no_badge").hide();
            }

            var loadingBadge = $(".loading_badge", collectionContainer);
            loadingBadge.appendTo(collectionContainer.find(".badges")).show("fast");

            var userBadges = {0: badgeElement.attr("data-id")};
            $(".badges .clarobadge", collectionContainer).each(function(index, element) {
                userBadges[index + 1] = $(element).attr("data-id");
            });

            var collectionUpdateRequest = $.ajax({
                url: apiUrl + collectionContainer.attr("data-id"),
                type: 'PATCH',
                data: {
                    'badge_collection_form[userBadges]': userBadges
                }
            });

            collectionUpdateRequest
              .success(function(data) {
                  doAddBadgeToCollection(collectionContainer, badgeElement);
              })
              .fail(function() {
                  displayError('add_badge_to_collection_error');
                  if (0 == nbBadges) {
                      collectionContainer.find(".no_badge").show();
                  }
                  loadingBadge.hide();
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
                  displayError('add_collection_error');
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
              .show('fast')
              .css("overflow", "visible"); // a bug in the jquery version we use add overflow hidden to the element at the end of the animation

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
                type: 'PATCH',
                data: {
                    'badge_collection_form[name]': $(".collection_title_input", collectionContainer).val()
                }
            });

            collectionUpdateRequest
              .success(function(data) {
                  doUpdateCollectionTitle(collectionContainer);
              })
              .fail(function() {
                  displayError('edit_title_collection_error');
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
            });

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
                  displayError('delete_collection_error');
              })
              .always(function() {
                  deletingCollectionElement.hide();
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

        function displayError(messageKey) {
            $("p", errorContainer).text(Translator.trans(messageKey, {}, 'icap_badge'));
            errorContainer.show();
        }

        $("#collections_list").on("click", ".shared_options .shared_option", function(event) {
            event.preventDefault();
            var target = $(event.currentTarget);
            var collectionContainer = target.parents('li.collection');

            collectionContainer.find(".shared_options li.active").removeClass("active");

            var sharedLink = collectionContainer.find(".shared_toggle");
            sharedLink.html(sharedLink.attr("data-loading-state"));
            target.parent().addClass("active");

            var sharedState = target.attr("data-value");

            var exitingCollection = $(".collection.editing", collectionsList);
            exitingCollection
              .each(function(index, element) {
                  doUpdateCollectionTitle($(element));
              });

            var collectionUpdateRequest = $.ajax({
                url: apiUrl + collectionContainer.attr("data-id"),
                type: 'PATCH',
                data: {
                    'badge_collection_form[is_shared]': sharedState
                }
            });

            collectionUpdateRequest
              .success(function(data) {
                  updateSharedState(sharedLink, sharedState);

                  if (1 == sharedState) {
                      sharedLink.parent().find(".share_collection").attr("href", data.collection.slug);
                  }
              })
              .fail(function() {
                  updateSharedState(sharedLink, (0 == sharedState)? 1 : 0);
                  var errorMessage = "edit_is_shared_collection_error";
                  if (1 == sharedState) {
                      errorMessage = "edit_is_unshared_collection_error";
                  }
                  displayError(errorMessage);
              });
        });

        function updateSharedState(sharedLink, sharedState) {
            if (0 == sharedState) {
                sharedLink.html(sharedLink.attr("data-private-state"));
                sharedLink.parent().find(".share_collection").hide();
            }
            else {
                sharedLink.html(sharedLink.attr("data-shared-state"));
                sharedLink.parent().find(".share_collection").show();
            }
        }

        $("#badge_list").on("click", ".shared_options .shared_option", function(event) {
            event.preventDefault();
            var target = $(event.currentTarget);
            var badgeContainer = target.parents('.badge_container');

            badgeContainer.find(".shared_options li.active").removeClass("active");

            var sharedLink = badgeContainer.find(".shared_toggle");
            sharedLink.html(sharedLink.attr("data-loading-state"));
            target.parent().addClass("active");

            var sharedState = target.attr("data-value");

            var badgeUpdateRequest = $.ajax({
                url: badgeApiUrl + '/' + badgeContainer.attr("data-id"),
                type: 'PATCH',
                data: {
                    'user_badge_form[is_shared]': sharedState
                }
            });

            badgeUpdateRequest
              .success(function(data) {
                  updateBadgeSharedState(sharedLink, sharedState);

                  if (1 == sharedState) {
                      sharedLink.parent().find(".share_badge").attr("href", data.user_badge.url);
                  }
              })
              .fail(function() {
                  updateBadgeSharedState(sharedLink, (0 == sharedState)? 1 : 0);
                  var errorMessage = "edit_is_shared_badge_error";
                  if (1 == sharedState) {
                      errorMessage = "edit_is_unshared_badge_error";
                  }
                  displayError(errorMessage);
              });
        });

        function updateBadgeSharedState(sharedLink, sharedState) {
            if (0 == sharedState) {
                sharedLink.html(sharedLink.attr("data-private-state"));
                sharedLink.parent().find(".share_badge").hide();
            }
            else {
                sharedLink.html(sharedLink.attr("data-shared-state"));
                sharedLink.parent().find(".share_badge").show();
            }
        }
    });
})(jQuery);
