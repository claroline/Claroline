(function () {
    'use strict';

    angular.module('Question').controller('MatchQuestionCtrl', [
        '$ngBootbox',
        '$scope',
        'CommonService',
        'QuestionService',
        'PlayerDataSharing',
        'ExerciseService',
        function ($ngBootbox, $scope, CommonService, QuestionService, PlayerDataSharing, ExerciseService) {
            this.question = {};
            this.currentQuestionPaperData = {};
            this.connections = [];
            this.canSeeFeedback = false;
            this.feedbackIsVisible = false;
            this.usedHints = [];

            // when in formative mode
            this.solutions = {};
            this.questionFeedback = '';

            // @todo check if already given answers and prebind elements if true
            // @todo handle toDrag MatchQuestion type
            this.init = function (question, canSeeFeedback) {
                // get used hints infos (id + content) + checked answer(s) for the current step / question
                // those data are updated by view and sent to common service as soon as they change
                this.currentQuestionPaperData = PlayerDataSharing.setCurrentQuestionPaperData(question);// CommonService.getCurrentQuestionPaperData(question);
                this.question = question;
                this.canSeeFeedback = canSeeFeedback;
                
                 console.log('question');
                 console.log(question);
                 /*console.log('this.currentQuestionPaperData');
                 console.log(this.currentQuestionPaperData);
                 */
                if (this.currentQuestionPaperData.hints.length > 0) {
                    // init used hints display
                    for (var i = 0; i < this.currentQuestionPaperData.hints.length; i++) {
                        this.getHintData(this.currentQuestionPaperData.hints[i]);
                    }
                }
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
             * Called on each jsPlumbConnectionEvent
             * We need to share those informations with parent controllers
             * For that purpose we use a shared service
             */
            this.updateStudentData = function () {
                // build answers string : for each connection idSource,idTarget;idSource2,idTarget2 etc...                
                // rebuild all the string each time
                var answer = '';
                for (var i = 0; i < this.connections.length; i++) {
                    answer += this.connections[i].source + ',' + this.connections[i].target;
                    if (i + 1 < this.connections.length) {
                        answer += ';';
                    }
                }
                this.currentQuestionPaperData.answer[0] = answer;
                PlayerDataSharing.setStudentData(this.question, this.currentQuestionPaperData);
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
                            // hide button
                            angular.element('#hint-' + id).hide();
                        }.bind(this));
            };

            this.getHintData = function (id) {
                var promise = QuestionService.getHint(id);
                promise.then(function (result) {
                    this.usedHints.push(result.data);

                }.bind(this));
            };

            this.showFeedback = function () {
                // get question answers and feedback ONLY IF NEEDED
                var promise = QuestionService.getQuestionSolutions(this.question.id);
                promise.then(function (result) {
                    this.feedbackIsVisible = true;
                    this.solutions = result.solutions;
                    this.questionFeedback = result.feedback;
                }.bind(this));
            };

            /**
             * Listen to show-feedback event (broadcasted by ExercisePlayerCtrl)
             */
            $scope.$on('show-feedback', function (event, data) {
                this.showFeedback();
            }.bind(this));

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


            /* JSPLUMB */
            this.detachAll = function () {
                jsPlumb.detachEveryConnection();
                this.connections = [];
                this.updateStudentData();
            };

            /**
             * DOM has to be ready before calling this method...
             */
            this.addPreviousConnections = function () {

                if (this.currentQuestionPaperData.answer && this.currentQuestionPaperData.answer.length > 0) {
                    // init previously given answer
                    var sets = this.currentQuestionPaperData.answer[0].split(';');
                    for (var i = 0; i < sets.length; i++) {
                        var set = sets[i].split(',');
                        jsPlumb.connect({source: "draggable_" + set[0], target: "droppable_" + set[1]});
                    }
                }
            };

            /*
             * Each time a connection is done
             * @param {type} data jsPlumb data
             * @returns {undefined}
             */
            this.handleBeforDrop = function (data) {
                var jsPlumbConnection = jsPlumb.getConnections(data.connection);
                // avoid drawing the same connection multiple times
                if (jsPlumbConnection.length > 0 && data.sourceId === jsPlumbConnection[0].sourceId && data.targetId === jsPlumbConnection[0].targetId) {
                    jsPlumb.detach(jsPlumbConnection);
                    return false;
                } else {
                    var sourceId = data.sourceId.replace('draggable_', '');
                    var targetId = data.targetId.replace('droppable_', '');
                    var connection = {
                        source: sourceId,
                        target: targetId
                    };
                    this.connections.push(connection);
                }
                this.updateStudentData();
                return true;
            };

            /**
             * Remove one connection
             * @param {type} data
             * @returns {undefined}
             */
            this.removeConnection = function (data) {
                var sourceId = data.sourceId.replace('draggable_', '');
                var targetId = data.targetId.replace('droppable_', '');
                jsPlumb.detach(data);
                for (var i = 0; i < this.connections.length; i++) {
                    if (this.connections[i].source === sourceId && this.connections[i].target === targetId) {
                        this.connections.splice(i, 1);
                    }
                }
                this.updateStudentData();
            };


            this.disableDrag = function (idDrag, droppableElement) {
                var draggableDropped = droppableElement.children(".dragDropped").children(idDrag);
                console.log('find it ?');
                console.log(idDrag);
                console.log(droppableElement.find(".dragDropped").children(idDrag).children().last());
                // removes a drag dropped
                $(droppableElement).find(".dragDropped").children(idDrag).children().last().click(function () {
                    console.log('trash clicked');
                    if (droppableElement.find(".dragDropped").children().length <= 1) {
                        droppableElement.removeClass("state-highlight");
                    }
                    // update the response table
                    $(this).parent().remove();
                    // resets of draggable
                    $(idDrag).draggable("enable");
                    $(idDrag).fadeTo(100, 1);
                    //removeDragTable(idDrag, draggableDropped);
                });
            }
        }
    ]);
})();