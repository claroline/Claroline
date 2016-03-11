/**
 * Paper details directive controller
 */
angular.module('Correction').controller('CorrectionCtrl', [
    'CommonService',
    'CorrectionService',
    function (CommonService, CorrectionService) {
        this.paper = {};
        this.questions = {};

        this.context = '';

        this.questionPanelsState = 'opened'; // all panels are open
        this.globalNote = 0.0;

        this.init = function (paper, questions) {
            this.paper = paper;
            this.questions = questions;
            this.globalNote = 0;//CommonService.getPaperScore(this.paper, this.questions);
        };

        /**
         * Hide / Show all question panels
         */
        this.toggleAllDetails = function () {
            // hide all panels
            if (this.questionPanelsState === 'opened') {
                $('.question-panel').each(function () {
                    var id = $(this).data('my-id');
                    $('#question-body-' + id).hide();
                    if (angular.element('#question-toggle-' + id).hasClass('fa-chevron-down')) {
                        angular.element('#question-toggle-' + id).removeClass('fa-chevron-down').addClass('fa-chevron-right');
                    }
                });
                this.questionPanelsState = 'closed';
            } else { // show all panels
                $('.question-panel').each(function () {
                    var id = $(this).data('my-id');
                    $('#question-body-' + id).show();
                    if ($('#question-toggle-' + id).hasClass('fa-chevron-right')) {
                        $('#question-toggle-' + id).removeClass('fa-chevron-right').addClass('fa-chevron-down');
                    }
                });
                this.questionPanelsState = 'opened';
            }
        };

        this.toggleDetails = function (id) {
            $('#question-body-' + id).toggle();

            if (angular.element('#question-toggle-' + id).hasClass('fa-chevron-down')) {
                angular.element('#question-toggle-' + id).removeClass('fa-chevron-down').addClass('fa-chevron-right');
            } else if (angular.element('#question-toggle-' + id).hasClass('fa-chevron-right')) {
                angular.element('#question-toggle-' + id).removeClass('fa-chevron-right').addClass('fa-chevron-down');
            }

            // check if all panels are in the same state to correctly handle show / hide all panels
            var countOpened = 0;
            $('.question-panel').each(function () {
                if ($('#question-toggle-' + id).hasClass('fa-chevron-down')) {
                    countOpened++;
                }
            });

            // if one or more panels are open then the show/hide all panel button should close all panels
            this.questionPanelsState = countOpened > 0 ? 'opened' : 'closed';
        };


        /**
         * Checks if for a given question student used at least one Hint
         * @param {type} question
         * @returns {Boolean}
         */
        this.oneOrMoreHintsAreUsed = function (question) {
            for (var i = 0; i < this.paper.questions.length; i++) {
                if (this.paper.questions[i].id === question.id.toString() && this.paper.questions[i].hints.length > 0) {
                    return true;
                }
            }
            return false;
        };

        /**
         * when rendering each question hint check if student used it
         * @param {type} question
         * @param {type} hint
         * @returns {boolean}
         */
        this.hintIsUsed = function (question, hint) {
            for (var i = 0; i < this.paper.questions.length; i++) {
                if (this.paper.questions[i].id === question.id.toString()) {
                    for (var j = 0; j < this.paper.questions[i].hints.length; j++) {
                        if (this.paper.questions[i].hints[j] === hint.id) {
                            return true;
                        }
                    }
                }
            }
            return false;
        };

        this.generateUrl = function (witch, _id) {
            return CommonService.generateUrl(witch, _id);
        };

        /**
         * compute the score for each question
         * @param {Object} question
         * @returns {String}
         */
        this.getQuestionScore = function (question) {
            for (var i = 0; i < this.questions.length; i++) {
                if (this.questions[i].id.toString() === question.id.toString()) {
                    if (question.type === 'application/x.match+json') {
                        return CorrectionService.getMatchQuestionScore(question, this.paper);
                    } else if (question.type === 'application/x.choice+json') {
                        return CorrectionService.getChoiceQuestionScore(question, this.paper);
                    } else if (question.type === 'application/x.cloze+json') {
                        return CorrectionService.getClozeQuestionScore(question, this.paper);
                    } else if (question.type === 'application/x.short+json') {
                        return CorrectionService.getShortQuestionScore(question, this.paper);
                    } else if (question.type === 'application/x.graphic+json') {
                        return CorrectionService.getGraphicQuestionScore(question, this.paper);
                    }
                }
            }

            return 'Not implemented for this type of question';
        };
    }
]);