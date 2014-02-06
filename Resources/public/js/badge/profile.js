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

            $("ul", droppingZone).append('<li class="clarobadge"><img class="badge_image_mini" src="/claroline/uploads/badges/html5_badge_256.png" alt="Création d\'une section dans un wiki"></li>');

        }

        $(".collection").droppable(dropOptions);

        addCollectionButton.click(function(event) {
            var existedCollection = $(".collection", collectionsList);
            if (0 == existedCollection.length) {
                noCollectionElement.hide();
            }
            else {
                existedCollection
                    .filter(".editing")
                    .each(function(index, element) {
                        updateCollectionTitle($(element));
                    });
            }

            $(newCollectionTemplate)
                .droppable(dropOptions)
                .hide()
                .prependTo($("#collections_list"))
                .show('fast')
                .find(".collection_title")
                    .hide();
        });

        $(collectionsList).on('keydown', 'input',function(event){
            if (event.which == 13) {
                event.preventDefault();
                updateCollectionTitle($(event.target).parents('li.collection'));
            }
        });

        function updateCollectionTitle(collectionContainer) {
            var collectionTitle      = $(".collection_title", collectionContainer);
            var collectionTitleInput = $(".collection_title_input", collectionContainer);

            collectionTitle
                .html(collectionTitleInput.val())
                .show();

            collectionTitleInput.hide();

            collectionContainer.removeClass('editing');
        }
    });
})(jQuery);