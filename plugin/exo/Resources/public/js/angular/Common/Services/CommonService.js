/**
 * CommonService
 */
angular.module('Common').factory('CommonService', [
    function CommonService() {
        return {
            /**
             * get a sequence correction mode in a human readable word
             * @param {integer} mode
             * @returns {string} the humanized correction mode
             */
            getCorrectionMode: function (mode) {
                switch (mode) {
                    case "1":
                        return "test-end";
                        break;
                    case "2":
                        return "last-try";
                        break;
                    case "3":
                        return "after-date";
                        break;
                    case "4":
                        return "never";
                        break;
                    default:
                        return "never";
                }
            },

            /**
             * @param {object} object a javascript object with type property
             * @returns null or string
             */
            getObjectSimpleType: function (object) {
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
            }
        };
    }
]);



