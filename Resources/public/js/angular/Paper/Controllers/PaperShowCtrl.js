/**
 * Paper Show Controller
 * Displays the details of a Paper
 * @param {Object} paperPromise
 * @param {Object} exercise
 * @constructor
 */
var PaperShowCtrl = function PaperShowCtrl(paperPromise, exercise) {
    this.paper     = paperPromise.paper;
    this.questions = paperPromise.questions;
    this.exercise  = exercise;
};

// Set up dependency injection
PaperShowCtrl.$inject = [ 'paperPromise', 'exercise' ];

PaperShowCtrl.prototype.paper = {};

PaperShowCtrl.prototype.questions = [];

PaperShowCtrl.prototype.paper = null;

// Register controller into Angular JS
angular
    .module('Paper')
    .controller('PaperShowCtrl', PaperShowCtrl);