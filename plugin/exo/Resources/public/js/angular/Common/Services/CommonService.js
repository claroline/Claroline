/**
 * CommonService
 */
var CommonService = function CommonService() {

};

// Set up dependency injection
CommonService.$inject = [];

/**
 * @param {object} object a javascript object with type property
 * @returns null or string
 */
CommonService.prototype.getObjectSimpleType = function getObjectSimpleType(object) {
    var simpleType = null;
    switch (object.type) {
        case 'text/html':
            simpleType = 'html-text';
            break;

        case 'text/plain':
            simpleType = 'simple-text';
            break;

        case 'application/pdf':
            simpleType = 'web-pdf';
            break;

        case 'image/png':
        case 'image/jpg':
        case 'image/jpeg':
            simpleType = 'web-image';
            if (object.encoding && object.data) {
                // Image is encoded
                simpleType = 'encoded-image';
            }
            break;

        default:
            simpleType = null;
            break;
    }

    return simpleType;
};

// Register service into Angular JS
angular
    .module('Common')
    .service('CommonService', CommonService);



