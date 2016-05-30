/**
 * Step Metadata Controller
 * Manages edition of the parameters of a Step
 * @param {Object} step
 * @param {$uibModalInstance} $uibModalInstance
 * @param {TinyMceService} TinyMceService
 * @param {StepService} StepService
 * @constructor
 */
var StepMetadataCtrl = function StepMetadataCtrl(step, $uibModalInstance, TinyMceService, StepService) {
    this.StepService = StepService;
    this.TinyMceService  = TinyMceService;

    this.$uibModalInstance = $uibModalInstance;
    this.step = step;

    // Create a copy of the exercise
    angular.copy(step.meta, this.meta);

    // Initialize TinyMCE
    this.tinymceOptions = TinyMceService.getConfig();
};

// Set up dependency injection
StepMetadataCtrl.$inject = [ 'step', '$uibModalInstance', 'TinyMceService', 'StepService' ];

/**
 * Tiny MCE options
 * @type {object}
 */
StepMetadataCtrl.prototype.tinymceOptions = {};

/**
 * The step to edit
 * @type {object}
 */
StepMetadataCtrl.prototype.step = {};

/**
 * A copy of the Step to edit (to not override Step data if User cancel the edition)
 * @type {Object}
 */
StepMetadataCtrl.prototype.meta = {};

/**
 * Save modifications of the Exercise
 */
StepMetadataCtrl.prototype.save = function save() {
    this.StepService.save(this.step, this.meta).then(function onSuccess() {
        // Go back on the overview
        this.$uibModalInstance.close();
    }.bind(this));
};

StepMetadataCtrl.prototype.cancel = function cancel() {
    this.$uibModalInstance.dismiss('cancel');
};

// Register controller into AngularJS
angular
    .module('Step')
    .controller('StepMetadataCtrl', StepMetadataCtrl);