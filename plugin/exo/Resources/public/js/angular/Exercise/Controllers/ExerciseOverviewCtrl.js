/**
 *
 * @param {Object} exercise
 * @param {boolean} editEnabled
 * @param {UserPaperService} UserPaperService
 * @constructor
 */
var ExerciseOverviewCtrl = function ExerciseOverviewCtrl(exercise, editEnabled, UserPaperService) {
    this.exercise    = exercise;
    this.editEnabled = editEnabled;
    this.UserPaperService = UserPaperService;
};

// Set up dependency injection
ExerciseOverviewCtrl.$inject = [ 'exercise', 'editEnabled', 'UserPaperService' ];

/**
 * Current Exercise
 * @type {Object}
 */
ExerciseOverviewCtrl.prototype.exercise = null;

/**
 * If the current User has the rights to administrate the Exercise,
 * we display him tools to do it
 * @type {boolean}
 */
ExerciseCtrl.prototype.editEnabled = false;

/**
 * Display/Hide additional info of the Exercise
 * @type {boolean}
 */
ExerciseOverviewCtrl.prototype.additionalInfo = false;

// Register controller into Angular JS
angular
    .module('Exercise')
    .controller('ExerciseOverviewCtrl', ExerciseOverviewCtrl);