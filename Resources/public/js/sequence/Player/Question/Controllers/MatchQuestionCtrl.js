(function () {
    'use strict';

    angular.module('Question').controller('MatchQuestionCtrl', [
        '$ngBootbox',
        '$scope',
        'CommonService',
        'QuestionService',
        'PlayerDataSharing',
        'ExerciseService',
        'MatchQuestionService',
        function ($ngBootbox, $scope, CommonService, QuestionService, PlayerDataSharing, ExerciseService, MatchQuestionService) {
            this.question = {};
            this.currentQuestionPaperData = {};
            this.connections = []; // for toBind questions
            this.dropped = []; // for to drag questions
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
                this.currentQuestionPaperData = PlayerDataSharing.setCurrentQuestionPaperData(question);
                this.question = question;
                this.canSeeFeedback = canSeeFeedback;

                PlayerDataSharing.setStudentData(question);

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
             * Called on each jsPlumbConnectionEvent or jquery-ui drop event
             * also called at init
             * We need to share those informations with parent controllers
             * For that purpose we use a shared service
             */
            this.updateStudentData = function () {
                // build answers
                this.currentQuestionPaperData.answer = [];
                if (this.question.subType === 'toBind') {
                    for (var i = 0; i < this.connections.length; i++) {
                        if (this.connections[i] !== '' && this.connections[i].source && this.connections[i].target) {
                            var answer = this.connections[i].source + ',' + this.connections[i].target;
                            this.currentQuestionPaperData.answer.push(answer);
                        }
                    }

                } else if (this.question.subType === 'toDrag') {
                    for (var i = 0; i < this.dropped.length; i++) {
                        if (this.dropped[i] !== '' && this.dropped[i].source && this.dropped[i].target) {
                            var answer = this.dropped[i].source + ',' + this.dropped[i].target;
                            this.currentQuestionPaperData.answer.push(answer);
                        }
                    }
                }
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

            this.initMatchQuestionJsPlumb = function (type) {
                if (type === 'bind') {
                    MatchQuestionService.initBindMatchQuestion();
                } else if (type === 'drag') {
                    MatchQuestionService.initDragMatchQuestion();
                }
            };

            /**
             * Only for toBind type
             * @returns {undefined}
             */
            this.reset = function (type) {
                if (type === 'bind') {
                    jsPlumb.detachEveryConnection();
                    this.connections = [];
                } else if (type === 'drag') {
                    // init all proposals ui
                    $(".origin").each(function () {
                        if ($(this).find('.draggable').attr('style')) {
                            $(this).find('.draggable').removeAttr('style');
                            $(this).find('.draggable').removeAttr('aria-disabled');
                            $(this).find('.draggable').draggable("enable");
                            var idProposal = $(this).attr("id");
                            idProposal = idProposal.replace('div_', '');
                        }
                    });
                    // init all drop containers ui
                    $(".droppable").each(function () {
                        if ($(this).find(".dragDropped").children()) {
                            $(this).removeClass('state-highlight');
                            $(this).find(".dragDropped").children().remove();
                        }
                    });
                    // init array of dropped items
                    this.dropped = [];
                }
                this.updateStudentData();
            };

            /**
             * init previous answers given for a toBind Match question
             * DOM has to be ready before calling this method...
             * problem when updating a previously given answer
             */
            this.addPreviousConnections = function () {
                //console.log('addPreviousConnections see previous answers');
                //console.log(this.currentQuestionPaperData.answer);
                if (this.currentQuestionPaperData.answer && this.currentQuestionPaperData.answer.length > 0) {
                    // init previously given answer
                    var sets = this.currentQuestionPaperData.answer;
                    for (var i = 0; i < sets.length; i++) {
                        if (sets[i] && sets[i] !== '') {
                            var items = sets[i].split(',');
                            jsPlumb.connect({source: "draggable_" + items[0], target: "droppable_" + items[1]});
                            var connection = {
                                source: items[0],
                                target: items[1]
                            };
                            this.connections.push(connection);
                        }
                    }
                }
                this.updateStudentData();
            };

            this.addPreviousDroppedItems = function () {
                this.dropped = [];
                if (this.currentQuestionPaperData.answer && this.currentQuestionPaperData.answer.length > 0) {
                    // init previously given answer
                    var sets = this.currentQuestionPaperData.answer;
                    for (var i = 0; i < sets.length; i++) {
                        if (sets[i] && sets[i] !== '') {
                            var items = sets[i].split(',');
                            // disable corresponding draggable item
                            $('#draggable_' + items[0]).draggable("disable");
                            // ui update
                            $('#draggable_' + items[0]).fadeTo(100, 0.3);
                            $('#droppable_' + items[1]).addClass("state-highlight");
                            var label = $('#draggable_' + items[0])[0].innerHTML;
                            var item = {
                                source: items[0],
                                target: items[1],
                                label: label
                            };
                            this.dropped.push(item);
                        }
                    }
                }
                this.updateStudentData();
            };

            /*
             * Only for toBind Match question
             * Each time a connection is done update student data
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
             * Each time a connection is removed update student data
             * Only for toBind Match question
             * Remove one connection
             * @param {type} data
             * @returns {undefined}
             */
            this.removeConnection = function (data) {
                var sourceId = data.sourceId.replace('draggable_', '');
                var targetId = data.targetId.replace('droppable_', '');
            //    jsPlumb.detach(data);
                for (var i = 0; i < this.connections.length; i++) {
                    if (this.connections[i].source === sourceId && this.connections[i].target === targetId) {
                        this.connections.splice(i, 1);
                    }
                }
                this.updateStudentData();
            };

            /**
             * Each time an item is drop we need to refresh data in PlayerDataSharingService
             * Only for toDrag Match question
             * @returns {undefined}
             */
            this.handleDragMatchQuestionDrop = function (event, ui) {
                // get dropped element id
                var sourceId = ui.draggable[0].id;
                var label = ui.draggable[0].innerHTML;
                // get the container in which the element has been dropped
                var targetId = event.target.id;

                // add the pair to the answer                 
                var entry = {
                    source: sourceId.replace('draggable_', ''),
                    target: targetId.replace('droppable_', ''),
                    label: label
                };

                // ugly but... no choice ?
                $scope.$apply(function () {
                    this.dropped.push(entry);
                }.bind(this));
                    
                this.updateStudentData();

                // disable draggable element
                $('#' + sourceId).draggable("disable");
                // ui update
                $('#' + sourceId).fadeTo(100, 0.3);
                $('#' + targetId).addClass("state-highlight");
            };

            /**
             * Each time an item is removed from drop container we need to refresh data in PlayerDataSharingService
             * @param {type} sourceId
             * @param {type} targetId
             * @returns {undefined}
             */
            this.removeDropped = function (sourceId, targetId) {
                // remove from local array (this.dropped)
                for (var i = 0; i < this.dropped.length; i++) {
                    if (this.dropped[i].source === sourceId && this.dropped[i].target === targetId) {                        
                        this.dropped.splice(i, 1);
                    }
                }
                // remove element from drop container -> since we use angular no use to do that ?
                // $('#dropped_' + sourceId).remove();
                // reactivate source draggable element
                $('#draggable_' + sourceId).draggable("enable");
                // visual changes for reactivated draggable element 
                $('#draggable_' + sourceId).fadeTo(100, 1);

                // ui update
                if ($('#droppable_' + targetId).find(".dragDropped").children().length <= 1) {
                    $('#droppable_' + targetId).removeClass("state-highlight");
                }

                // update student data
                this.updateStudentData();
            };
        }
    ]);
})();