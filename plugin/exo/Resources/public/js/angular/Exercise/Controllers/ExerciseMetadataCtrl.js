/**
 * Exercise Metadata Controller
 * Manages edition of the parameters of the Exercise
 * @param {Object} exercise - The exercise to Edit
 * @constructor
 */
var ExerciseMetadataCtrl = function ExerciseMetadataCtrl(ExerciseService, exercise) {
    this.exerciseService = ExerciseService;

    // Create a copy of the exercise
    angular.copy(exercise, this.exercise);

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

    this.tinymceOptions.format = 'html';
};

// Set up dependency injection
ExerciseMetadataCtrl.$inject = [ 'ExerciseService', 'exercise' ];

/**
 * Tiny MCE options
 * @type {object}
 */
ExerciseMetadataCtrl.prototype.tinymceOptions = {};

/**
 * A copy of the Exercise to edit (to not override Exercise data if User cancel the edition)
 * @type {Object}
 */
ExerciseMetadataCtrl.prototype.exercise = {};

/**
 * Save modifications of the Exercise
 */
ExerciseMetadataCtrl.prototype.save = function save() {
    this.exerciseService.save(this.exercise);
};

// Register controller into AngularJS
angular
    .module('Exercise')
    .controller('ExerciseMetadataCtrl', ExerciseMetadataCtrl);