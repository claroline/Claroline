(function () {
    'use strict';

    angular.module('Step').controller('StepEditCtrl', [
        '$modal',
        'StepService',
        'QuestionService',
        function ($modal, StepService, QuestionService) {
            // all steps for the sequence
            this.steps = {};
            this.currentStepIndex = 0;

            // options for sortable steps
            this.sortableOptions = {
                placeholder: "placeholder",
                axis: 'x',
                stop: function (e, ui) {
                    this.updateStepsOrder();
                }.bind(this),
                cancel: ".unsortable",
                items: "li:not(.unsortable)"
            };

            // Tiny MCE options
            this.tinymceOptions = {
                relative_urls: false,
                theme: 'modern',
                browser_spellcheck: true,
                autoresize_min_height: 100,
                autoresize_max_height: 500,
                plugins: [
                    'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
                    'searchreplace wordcount visualblocks visualchars fullscreen',
                    'insertdatetime media nonbreaking save table directionality',
                    'template paste textcolor emoticons code'
                ],
                toolbar1: 'undo redo | styleselect | bold italic underline | forecolor | alignleft aligncenter alignright | preview fullscreen',
                paste_preprocess: function (plugin, args) {
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

            // Step constructor
            var my = this;
            var Step = function (isContentStep) {
                var ujm_step = {
                    description: '<h1>New step default description</h1>',
                    position: my.steps.length,
                    shuffle: false,
                    sequenceId: my.steps[0].sequenceId,
                    isLast: false,
                    isFirst: false,
                    isContentStep: isContentStep,
                    questions: []
                };
                return ujm_step;
            };

            this.addStep = function (isContentStep) {
                // create a new step
                var step = new Step(isContentStep);
                // update last step position
                var last = this.steps[this.steps.length - 1];
                last.position = step.position + 1;
                // add new step at the right index in steps array
                this.steps.splice(this.steps.length - 1, 0, step);
            };

            this.removeStep = function () {
                var current = this.steps[this.currentStepIndex];
                if (current && !current.isLast && !current.isFirst) {
                    var index = this.steps.indexOf(current);
                    // update positions...
                    for (var i = index; i < this.steps.length; i++) {
                        var step = this.steps[i];
                        step.position = step.position - 1;
                    }
                    // remove step
                    this.steps.splice(index, 1);
                }
            };

            this.update = function () {
                var promise = StepService.update(this.steps);
                promise.then(function (result) {
                    console.log('steps update success');
                }, function (error) {
                    console.log('steps update error');
                });

            };

            this.getNextStep = function () {
                var newIndex = this.currentStepIndex + 1;
                if (this.steps[newIndex]) {
                    this.setCurrentStep(this.steps[newIndex]);
                } else {
                    this.setCurrentStep(this.steps[0]);
                }
            };

            this.getPreviousStep = function () {
                var newIndex = this.currentStepIndex - 1;
                if (this.steps[newIndex]) {
                    this.setCurrentStep(this.steps[newIndex]);
                } else {
                    this.setCurrentStep(this.steps.length - 1);
                }
            };

            // on dragg end
            this.updateStepsOrder = function () {
                var index = 0;
                for (index; index < this.steps.length; index++) {
                    var step = this.steps[index];
                    step.position = index + 1;
                }
            };

            this.setSteps = function (steps) {
                this.steps = steps;
            };

            this.getSteps = function () {
                return this.steps;
            };

            // on step square click or called by getNext/getPrevious Step
            this.setCurrentStep = function (step) {
                var index = this.steps.indexOf(step);
                this.currentStepIndex = index;
                // questions are applicable only for "normal" steps
                if (index !== 0 || index !== this.steps.length - 1) {
                    this.getStepQuestions(step);
                }
            };

            this.getStepQuestions = function (step) {
                // if new step there will be no questions
                step.questions = [];
                var my = this;
                // else we need to get the already existing questions
                if (step.id) {
                    var promise = QuestionService.getStepQuestions(step);
                    promise.then(function (result) {
                        step.questions = result.data;
                    }, function (error) {
                        console.log('get step questions error');
                    });
                }
            };

            this.addQuestion = function () {
                var modalInstance = $modal.open({
                    templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/sequence/Question/Partials/questions.available.html',
                    controller: 'QuestionCtrl as questionCtrl',
                    resolve: {
                        questions: [
                            'QuestionService',
                            function (QuestionService) {
                                return QuestionService.getAll();
                            }
                        ]
                    }
                });

                modalInstance.result.then(function (selected) {
                    console.log('modal closed see selected question details');
                    console.log(selected);                   
                    // add question to collection
                    this.steps[this.currentStepIndex].questions.push(selected);
                }.bind(this));
            };
            
            this.removeQuestion = function (questionId){
                
            };
        }
    ]);
})();