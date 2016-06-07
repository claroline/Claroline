/**
 * Draw areas on a canvas
 * @param {ImageAreaService} ImageAreaService
 * @constructor
 */
function AreasDirective(ImageAreaService) {
    return {
        restrict: 'E',
        replace: true,
        template: '<canvas class="img-areas"></canvas>',
        scope: {
            img: '=',
            areas: '='
        },
        link: function link(scope, element, attr) {
            var canvas = element.get(0);
            var context = canvas.getContext('2d');

            /**
             * Redraw all the areas on the canvas
             */
            var redraw = function redraw() {
                var originalHeight = scope.img.data('original-height');
                var originalWidth = scope.img.data('original-width');

                // Set the canvas size
                canvas.width = originalWidth;
                canvas.height = originalHeight;

                if (null !== context) {
                    for (var i = 0; i < scope.areas.length; i++) {
                        ImageAreaService.drawArea(scope.areas[i], context);
                    }
                } else {
                    console.error('Image areas : can not find context to draw on.');
                }
            };

            // Only redraw if the img is fully loaded (else compute of size not work)
            scope.img.on('load', redraw);

            // Redraw when areas change
            scope.$watch('areas', function (newValue) {
                if (typeof newValue !== 'undefined' && newValue.length !== 0) {
                    redraw();
                }
            }, true);

            scope.$on('$destroy', function () {
                scope.img.off('load', redraw);
            });
        }
    };
}

export default AreasDirective
