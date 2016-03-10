/**
 * Exercise Controller
 * Base controller for Exercises
 * @constructor
 */
var ExerciseCtrl = function ExerciseCtrl(ExerciseService) {
    // Share current Exercise with the whole application
    ExerciseService.setExercise(this.exercise);
    ExerciseService.setEditEnabled(this.editEnabled);
};

// Set up dependency injection
ExerciseCtrl.$inject = [ 'ExerciseService' ];

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
 * Is the Current Exercise already published ?
 * @type {boolean}
 */
ExerciseCtrl.prototype.published = false;

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
    console.log('Exercise will be published');
};

/**
 * Unpublish the Current exercise
 */
ExerciseCtrl.prototype.unpublish = function unpublish() {
    console.log('Exercise will be unpublished');
};

// Register controller into AngularJS
angular
    .module('Exercise')
    .controller('ExerciseCtrl', ExerciseCtrl);