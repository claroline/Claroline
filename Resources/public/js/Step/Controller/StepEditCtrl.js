/**
 * Class constructor
 * @returns {StepEditCtrl}
 * @constructor
 */
var StepEditCtrl = function StepEditCtrl(step, inheritedResources, PathService, $scope, StepService) {
    StepBaseCtrl.apply(this, arguments);

    this.scope       = $scope;
    this.stepService = StepService;

    // Set TinyMCE language
    this.tinymceOptions = tinymce.claroline.configuration;

    /*console.log(stfalcon_tinymce_config);*/

    /**
     * Activity resource picker config
     * @type {object}
     */
    this.resourcePicker = {
        // A step can allow be linked to one Activity, so disable multi-select
        isPickerMultiSelectAllowed: false,

        // Only allow Activity selection
        typeWhiteList: [ 'activity' ],
        callback: function (nodes) {
            if (typeof nodes === 'object' && nodes.length !== 0) {
                for (var nodeId in nodes) {
                    if (nodes.hasOwnProperty(nodeId)) {
                        // Load activity properties to populate step
                        this.stepService.loadActivity(this.step, nodeId);

                        break; // We need only one node, so only the last one will be kept
                    }
                }

                this.scope.$apply();

                // Remove checked nodes for next time
                nodes = {};
            }
        }.bind(this)
    };

    return this;
};

// Extends the base controller
StepEditCtrl.prototype = StepBaseCtrl.prototype;
StepEditCtrl.prototype.constructor = StepEditCtrl;

/**
 * Defines which panels of the form are collapsed or not
 * @type {object}
 */
StepEditCtrl.prototype.collapsedPanels = {
    description       : false,
    properties        : true
};

/**
 * Tiny MCE options
 * @type {object}
 */
StepEditCtrl.prototype.tinymceOptions = {};

/**
 * Display activity linked to the Step
 */
StepEditCtrl.prototype.showActivity = function () {
    var activityRoute = Routing.generate('innova_path_show_activity', {
        activityId: this.step.activityId
    });

    window.open(activityRoute, '_blank');
};

/**
 * Delete the link between the Activity and the Step (Step's data are kept)
 */
StepEditCtrl.prototype.deleteActivity = function () {
    this.step.activityId = null;
};