/**
 * Exercise Controller
 * Base controller for Exercises
 * @param {ExerciseService} ExerciseService
 * @param {object}          $route
 * @constructor
 */
var ExerciseCtrl = function ExerciseCtrl(ExerciseService, $route) {
    // Share current Exercise with the whole application
    ExerciseService.setExercise(this.exercise);
    ExerciseService.setEditEnabled(this.editEnabled);
    ExerciseService.setComposeEnabled(this.composeEnabled);

    // Force reload of the route (as ng-view is deeper in the directive tree, route resolution is deferred and it causes issues)
    $route.reload();

    this.currentView = $route.current;

    console.log($route);
};

// Set up dependency injection
ExerciseCtrl.$inject = [ 'ExerciseService', '$route' ];

/**
 * Current displayed view (aka route) of the Exercise (e.g. overview, edit parameters, questions)
 * @type {null}
 */
ExerciseCtrl.prototype.currentView = {};

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
 * If the current User has the rights to do the Exercise,
 * we display him the button to access it
 * @type {boolean}
 */
ExerciseCtrl.prototype.composeEnabled = false;

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