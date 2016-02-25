/**
 * Exercise Controller
 * Base controller for Exercises
 * @constructor
 */
var ExerciseCtrl = function ExerciseCtrl() {

};

// Set up dependency injection
ExerciseCtrl.$inject = [];

/**
 * Current Exercise
 * @type {Object}
 */
ExerciseCtrl.prototype.exercise = null;

// Register controller into AngularJS
angular
    .module('Exercise')
    .controller('ExerciseCtrl', ExerciseCtrl);