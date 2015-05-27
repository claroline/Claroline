/**
 * Resources primary controller
 * @returns {ResourcesPrimaryShowCtrl}
 * @constructor
 */
var ResourcesPrimaryShowCtrl = function ResourcesPrimaryShowCtrl($sce) {
    // Call parent constructor
    ResourcesPrimaryBaseCtrl.apply(this, arguments);

    // Get resource URL to populate IFrame
    if (angular.isObject(this.resources) && angular.isObject(this.resources[0])) {
        console.log('calculate resource URL');
        var url = Routing.generate('claro_resource_open', {
            node         : this.resources[0].id,
            resourceType : this.resources[0].type
        });

        if (url) {
            this.resourceUrl = $sce.trustAsResourceUrl('http://localhost' + url);

            console.log(this.resourceUrl);
        }
    }

    return this;
};

// Extends the base controller
ResourcesPrimaryShowCtrl.prototype = ResourcesPrimaryBaseCtrl.prototype;
ResourcesPrimaryShowCtrl.prototype.constructor = ResourcesPrimaryShowCtrl;

ResourcesPrimaryShowCtrl.prototype.resourceUrl = null;