(function () {
    'use strict';

    angular.module('Question').controller('GraphicQuestionCtrl', [
        '$ngBootbox',
        '$scope',
        'CommonService',
        'QuestionService',
        'PlayerDataSharing',
        'ExerciseService',
        function ($ngBootbox, $scope, CommonService, QuestionService, PlayerDataSharing, ExerciseService) {
            this.question = {};
            // keep coord(s)
            this.coords = [];
            this.currentQuestionPaperData = {};
            this.usedHints = [];

            // instant feedback data
            this.canSeeFeedback = false;
            this.feedbackIsVisible = false;


            this.init = function (question, canSeeFeedback) {
                // those data are updated by view and sent to common service as soon as they change
                this.currentQuestionPaperData = PlayerDataSharing.setCurrentQuestionPaperData(question);
                this.question = question;
                this.canSeeFeedback = canSeeFeedback;
                console.log('this.question');
                console.log(this.question);
                console.log('this.currentQuestionPaperData');
                console.log(this.currentQuestionPaperData);
                // init student data question object
                PlayerDataSharing.setStudentData(question);

                if (this.currentQuestionPaperData.hints && this.currentQuestionPaperData.hints.length > 0) {
                    // init used hints display
                    for (var i = 0; i < this.currentQuestionPaperData.hints.length; i++) {
                        this.getHintData(this.currentQuestionPaperData.hints[i]);
                    }
                }
                // init draggable elements if exists
                if (this.currentQuestionPaperData.answer && this.currentQuestionPaperData.answer.length > 0) {
                    // init previously given answer
                    // this.checkChoices(this.question.multiple);
                } else {
                    for (var i = 0; i < this.question.coords.length; i++) {
                        var coord = {
                            id: this.question.coords[i].id,
                            x:0,
                            y:0
                        };
                        this.coords.push(coord);
                    }
                }
            };

            this.initDragAndDrop = function () {
                var self = this;
                // we want the center of crosshair image to be able to match the extremities of the document
                // we do it dynamically if the layout / or crosshair image / styles change
                var crossHairSize = $(".draggable")[0].width;
                var containerWidth = this.question.width + crossHairSize;
                var containerHeight = this.question.height + crossHairSize;
                var imgMargin = crossHairSize / 2;
                $('.droppable-container').css("width", containerWidth.toString() + "px");
                $('.droppable-container').css("height", containerHeight.toString() + "px");
                $('#document-img').css("margin", imgMargin.toString() + "px");
                // init ui draggable objects
                $(".draggable").draggable({
                    // automatic z-index
                    stack: ".draggable",
                    // drop only in container
                    containment: '.droppable-container',
                    stop: function (event, ui) {
                        console.log('droppped');
                        var draggedId = $(this).attr('id');                       
                        // get dragged coordonates with offset instead of position
                        var coordX = $(this).offset().left - $('.droppable-container').offset().left;
                        var coordY = $(this).offset().top - $('.droppable-container').offset().top;
                        // update this.coords
                        for(var i=0; i < self.coords.length; i++){
                            if(self.coords[i].id === draggedId){
                                self.coords[i].x = coordX;
                                self.coords[i].y = coordY;
                            }
                        }
                        console.log(self.coords);
                        self.updateStudentData();
                    }
                });
            };


            this.getAssetsDir = function () {
                return AngularApp.webDir;
            };


            /**
             * check if a Hint has already been used (in paper)
             * @param {type} id
             * @returns {Boolean}
             */
            this.hintIsUsed = function (id) {
                if (this.currentQuestionPaperData && this.currentQuestionPaperData.hints) {
                    for (var i = 0; i < this.currentQuestionPaperData.hints.length; i++) {
                        if (this.currentQuestionPaperData.hints[i] === id) {
                            return true;
                        }
                    }
                }
                return false;
            };

            /**
             * Get hint data and update student data in common service
             * @param {type} hintId
             * @returns {undefined}
             */
            this.showHint = function (id) {
                var penalty = QuestionService.getHintPenalty(this.question.hints, id);
                $ngBootbox.confirm(Translator.trans('question_show_hint_confirm', {1: penalty}, 'ujm_sequence'))
                        .then(function () {
                            this.getHintData(id);
                            this.currentQuestionPaperData.hints.push(id);
                            this.updateStudentData();
                            // hide hint button
                            angular.element('#hint-' + id).hide();
                        }.bind(this));
            };

            this.getHintData = function (id) {
                var promise = QuestionService.getHint(id);
                promise.then(function (result) {
                    //console.log(result);
                    this.usedHints.push(result);

                }.bind(this));
            };

            /**
             * Checks if the question has meta
             * @returns {boolean}
             */
            this.questionHasOtherMeta = function () {
                return ExerciseService.objectHasOtherMeta(this.question);
            };

            /**
             * Called on each checkbox / radiobutton click
             * We need to share those informations with parent controllers
             * For that purpose we use a shared service
             */
            this.updateStudentData = function () {
                
                //array(
        //  "471-335.9999694824219",
        //  "583-125"
        // )
                var answers = [];
                for(var i = 0 ; i < this.coords.length; i++){
                    var answerString = this.coords[i].x.toString() + '-' + this.coords[i].y.toString();
                    answers.push(answerString);
                }                
                this.currentQuestionPaperData.answer = answers;
                PlayerDataSharing.setStudentData(this.question, this.currentQuestionPaperData);
                
                var test = PlayerDataSharing.getStudentData();
                console.log('student data updated ?');
                console.log(test);
            };

            /**
             * Hide / show a specific panel content and handle hide / show button icon 
             * @param {string} id (part of the panel id)
             */
            this.toggleDetails = function (id) {

                // custom toggle function to avoid the use of jquery
                if (angular.element('#question-body-' + id).attr('style') === undefined) {
                    angular.element('#question-body-' + id).attr('style', 'display: none;');
                } else {
                    // hide / show panel body
                    if (angular.element('#question-body-' + id).attr('style') === 'display: none;') {
                        angular.element('#question-body-' + id).attr('style', 'display: block;');
                    } else if (angular.element('#question-body-' + id).attr('style') === 'display: block;') {
                        angular.element('#question-body-' + id).attr('style', 'display: none;');
                    }
                }

                // handle hide / show button icon 
                if (angular.element('#question-toggle-' + id).hasClass('fa-chevron-down')) {
                    angular.element('#question-toggle-' + id).removeClass('fa-chevron-down').addClass('fa-chevron-right');
                } else if (angular.element('#question-toggle-' + id).hasClass('fa-chevron-right')) {
                    angular.element('#question-toggle-' + id).removeClass('fa-chevron-right').addClass('fa-chevron-down');
                }
            };
        }
    ]);
})();