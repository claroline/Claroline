/**
 * Image Area Service
 * @constructor
 */
var ImageAreaService = function AreaService() {

};

// Set up dependency injection
ImageAreaService.$inject = [];

/**
 * Map color name to RGBA value
 * @type {object}
 */
ImageAreaService.prototype.COLORS = {
    white:  'rgba(255, 255, 255, 0.5)',
    black:  'rgba(0,   0,   0,   0.5)',
    red:    'rgba(193, 0,   31,  0.5)',
    blue:   'rgba(0,   156, 221, 0.5)',
    purple: 'rgba(86,  38,  125, 0.5)',
    green:  'rgba(17,  142, 63,  0.5)',
    orange: 'rgba(201, 82,  38,  0.5)',
    yellow: 'rgba(255, 235, 0,   0.5)',
    brown:  'rgba(90,  76,  65,  0.5)'
};

/**
 * Draw an area on a canvas
 * @param {Object} areaDefinition the full definition of the area (score + feedback + area)
 * @param {CanvasRenderingContext2D} context canvas context to draw on
 */
ImageAreaService.prototype.drawArea = function drawArea(areaDefinition, context) {
    context.beginPath();

    var area = areaDefinition.area;
    switch (area.shape) {
        case 'rect':
            var width  = area.coords[1].x - area.coords[0].x;
            var height = area.coords[1].y - area.coords[0].y;

            context.rect(area.coords[0].x, area.coords[0].y, width, height);

            break;
        case 'circle':
            context.arc(area.center.x, area.center.y, area.radius, 0, 2 * Math.PI, false);

            break;
        default:
            console.log('Image area: Unsupported area shape `' + area.shape + '`.');
            break;
    }

    context.closePath();

    context.fillStyle = this.COLORS[area.color];
    context.fill();
};

/**
 * Check whether a point is in an area
 * @param {Object} areaDefinition
 * @param {{x: number, y: number}} coords
 *
 * @returns {Boolean}
 */
ImageAreaService.prototype.isInArea = function isInArea(areaDefinition, coords) {
    var inArea = false;

    var area = areaDefinition.area;
    switch (area.shape) {
        case 'rect':
            if (coords.x >= area.coords[0].x && coords.x <= area.coords[1].x
                && coords.y >= area.coords[0].y && coords.y <= area.coords[1].y) {
                inArea = true;
            }

            break;
        case 'circle':
            if (Math.sqrt((coords.x - area.center.x)*(coords.x - area.center.x) + (coords.y - area.center.y)*(coords.y - area.center.y)) <= area.radius) {
                inArea = true;
            }

            break;
        default:
            console.log('Image area: Unsupported area shape `' + area.shape + '`.');
            break;
    }

    return inArea;
};

// Register service into AngularJS
angular
    .module('Image')
    .service('ImageAreaService', ImageAreaService);
