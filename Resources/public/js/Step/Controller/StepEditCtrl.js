/**
 * Class constructor
 * @returns {StepEditCtrl}
 * @constructor
 */
var StepEditCtrl = function StepEditCtrl(step, inheritedResources, PathService, $scope, StepService) {
    StepBaseCtrl.apply(this, arguments);

    this.scope       = $scope;
    this.stepService = StepService;
    this.pathService = PathService;
    this.nextstep = this.pathService.getNext(step);

    // Initialize TinyMCE
    var tinymce = window.tinymce;
    tinymce.claroline.init    = tinymce.claroline.init || {};
    tinymce.claroline.plugins = tinymce.claroline.plugins || {};

    var plugins = [
        'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
        'searchreplace wordcount visualblocks visualchars fullscreen',
        'insertdatetime media nonbreaking table directionality',
        'template paste textcolor emoticons code'
    ];
    var toolbar = 'undo redo | styleselect | bold italic underline | forecolor | alignleft aligncenter alignright | preview fullscreen';

    $.each(tinymce.claroline.plugins, function(key, value) {
        if ('autosave' != key &&  value === true) {
            plugins.push(key);
            toolbar += ' ' + key;
        }
    });

    for (var prop in tinymce.claroline.configuration) {
        if (tinymce.claroline.configuration.hasOwnProperty(prop)) {
            this.tinymceOptions[prop] = tinymce.claroline.configuration[prop];
        }
    }

    this.tinymceOptions.plugins = plugins;
    this.tinymceOptions.toolbar1 = toolbar;
    this.tinymceOptions.trusted = true;
    this.tinymceOptions.format = 'html';

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
StepEditCtrl.prototype = Object.create(StepBaseCtrl.prototype);
StepEditCtrl.prototype.constructor = StepEditCtrl;

// Dependency Injection
StepEditCtrl.$inject = [ 'step', 'inheritedResources', 'PathService', '$scope', 'StepService' ];

/**
 * Defines which panels of the form are collapsed or not
 * @type {object}
 */
StepEditCtrl.prototype.collapsedPanels = {
    description       : false,
    properties        : true,
    conditions        : true
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

// Register controller into Angular
angular
    .module('StepModule')
    .controller('StepEditCtrl', StepEditCtrl);
