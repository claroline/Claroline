/**
 * Exercise Controller
 * Base controller for Exercises
 * @param {ExerciseService} ExerciseService
 * @param {object}          $route
 * @constructor
 */
var ExerciseCtrl = function ExerciseCtrl(ExerciseService, $route) {
    this.ExerciseService = ExerciseService;

    // Share current Exercise with the whole application
    this.ExerciseService.setExercise(this.exercise);
    this.ExerciseService.setEditEnabled(this.editEnabled);
    /*this.ExerciseService.setComposeEnabled(this.composeEnabled);*/

    this.$route = $route;
};

// Set up dependency injection
ExerciseCtrl.$inject = [ 'ExerciseService', '$route' ];

/**
 * Current Exercise
 * @type {Object}
 */
ExerciseCtrl.prototype.exercise = null;

/**
 * The numbers of Papers submitted for this Exercise
 * @type {Number}
 */
ExerciseCtrl.prototype.nbPapers = 0;

/**
 * The numbers of Papers submitted for this Exercise
 * @type {Number}
 */
ExerciseCtrl.prototype.nbUserPapers = 0;

/**
 * If the current User has the rights to administrate the Exercise,
 * we display him tools to do it
 * @type {boolean}
 */
ExerciseCtrl.prototype.editEnabled = false;

/**
 * Publish the Current exercise
 */
ExerciseCtrl.prototype.publish = function publish() {
    this.ExerciseService.publish();
};

/**
 * Unpublish the Current exercise
 */
ExerciseCtrl.prototype.unpublish = function unpublish() {
    this.ExerciseService.unpublish();
};

// Register controller into AngularJS
angular
    .module('Exercise')
    .controller('ExerciseCtrl', ExerciseCtrl);