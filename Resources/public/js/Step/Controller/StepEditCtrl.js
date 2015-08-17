/**
 * Class constructor
 * @returns {StepEditCtrl}
 * @constructor
 */
var StepEditCtrl = function StepEditCtrl(step, inheritedResources, PathService, $scope, StepService) {
    StepBaseCtrl.apply(this, arguments);

    this.scope       = $scope;
    this.stepService = StepService;

    // Set TinyMCE language (need to remap english language)
    this.tinymceOptions.language = 'en_EN' == AngularApp.locale ? 'en' : AngularApp.locale;

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
StepEditCtrl.prototype = StepBaseCtrl.prototype;
StepEditCtrl.prototype.constructor = StepEditCtrl;

/**
 * Defines which panels of the form are collapsed or not
 * @type {object}
 */
StepEditCtrl.prototype.collapsedPanels = {
    description       : false,
    properties        : true
};

//we need the claroline tinymce object for additionnal initialization parameters
var tinymce = window.tinymce;
tinymce.claroline.init = tinymce.claroline.init || {};
tinymce.claroline.plugins = tinymce.claroline.plugins || {};

/**
 * Tinymce setup
 */ 
 
var tinymceSetup = function(editor) {
    $.each(tinymce.claroline.init, function(key, func) {
        func(editor);
    });
}

var plugins = [
    'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
    'searchreplace wordcount visualblocks visualchars fullscreen',
    'insertdatetime media nonbreaking save table directionality',
    'template paste textcolor emoticons code'
];
var toolbar = 'undo redo | styleselect | bold italic underline | forecolor | alignleft aligncenter alignright | preview fullscreen';

$.each(tinymce.claroline.plugins, function(key, value) {
    if (value === true) {
        plugins.push(key);
        toolbar += ' ' + key;
    }
});

/**
 * Tiny MCE options
 * @type {object}
 */
StepEditCtrl.prototype.tinymceOptions = {
    relative_urls: false,
    theme: 'modern',
    browser_spellcheck : true,
    entity_encoding : "numeric",
    autoresize_min_height: 150,
    autoresize_max_height: 500,
    plugins: plugins, //j'ai repris la liste des plugins de ce fichier, mais sinon on peut aussi utiliser celle du tinymce du core bundle
    toolbar1: toolbar, //même chose pour la toolbar
    //setup: tinymceSetup, //pour une raison que j'ignore, la détection auto du changement de description casse avec cette ligne
    paste_preprocess: function (plugin, args) { //on peut aussi utiliser le paste du CoreBundle (mais il est différent donc je n'en suis pas sûr, et puis le comportement doit peut-être être différent de toute façon).
        var link = $('<div>' + args.content + '</div>').text().trim(); //inside div because a bug of jquery
        var url = link.match(/^(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})$/);

        if (url) {
            args.content = '<a href="' + link + '">' + link + '</a>';
            window.Claroline.Home.generatedContent(link, function (data) {
                insertContent(data);
            }, false);
        }
    }
};

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
