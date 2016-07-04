/**
 * Exercise Metadata Controller
 * Manages edition of the parameters of the Exercise
 * @param {Object}          $location
 * @param {ExerciseService} ExerciseService
 * @param {TinyMceService} TinyMceService
 * @constructor
 */
function ExerciseMetadataCtrl($location, ExerciseService, TinyMceService) {
    this.$location        = $location;
    this.ExerciseService = ExerciseService;
    this.TinyMceService = TinyMceService;

    // Create a copy of the exercise
    angular.copy(this.ExerciseService.getMetadata(), this.meta);

    this.correctionModes = this.ExerciseService.correctionModes;
    this.markModes       = this.ExerciseService.markModes;

    // Initialize TinyMCE
    this.tinymceOptions = TinyMceService.getConfig();
};

/**
 * Tiny MCE options
 * @type {object}
 */
ExerciseMetadataCtrl.prototype.tinymceOptions = {};

/**
 * A copy of the Exercise to edit (to not override Exercise data if User cancel the edition)
 * @type {Object}
 */
ExerciseMetadataCtrl.prototype.meta = {};

/**
 * List of available correction modes for Exercise
 * @type {Object}
 */
ExerciseMetadataCtrl.prototype.correctionModes = {};

/**
 * List of available mark modes for Exercise
 * @type {Object}
 */
ExerciseMetadataCtrl.prototype.markModes = {};

/**
 * Save modifications of the Exercise
 */
ExerciseMetadataCtrl.prototype.save = function save() {
    this.ExerciseService.save(this.meta).then(function onSuccess() {
        // Go back on the overview
        this.$location.path('/');
    }.bind(this));
};

export default ExerciseMetadataCtrl
