/**
 * Match Question Controller
 * @param {FeedbackService} FeedbackService
 * @constructor
 */
var MatchQuestionCtrl = function MatchQuestionCtrl(FeedbackService, $scope, QuestionService, DataSharing, ExerciseService, MatchQuestionService, $timeout) {
    AbstractQuestionCtrl.apply(this, arguments);

    this.$scope = $scope;
    this.QuestionService = QuestionService;
    this.DataSharing = DataSharing;
    this.ExerciseService = ExerciseService;
    this.MatchQuestionService = MatchQuestionService;
    this.$timeout = $timeout;

    this.savedAnswers = [];
    for (var i=0; i<this.dropped.length; i++) {
        this.savedAnswers.push(this.dropped[i]);
    }
};

// Extends AbstractQuestionCtrl
MatchQuestionCtrl.prototype = Object.create(AbstractQuestionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
MatchQuestionCtrl.$inject = AbstractQuestionCtrl.$inject.concat([
    '$scope',
    'QuestionService',
    'DataSharing',
    'ExerciseService',
    'MatchQuestionService',
    '$timeout'
]);

MatchQuestionCtrl.prototype.connections = []; // for toBind questions

MatchQuestionCtrl.prototype.dropped = []; // for to drag questions

MatchQuestionCtrl.prototype.orphanAnswers = [];

MatchQuestionCtrl.prototype.orphanAnswersAreChecked = false;

MatchQuestionCtrl.prototype.savedAnswers = [];

/**
 * Reset answer
 */
MatchQuestionCtrl.prototype.reset = function reset() {
    if (this.question.toBind) {
        jsPlumb.detachEveryConnection();
        this.connections = [];
    } else if (this.question.toDrag) {
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
 * find all orphan answers and set them in an array
 */
MatchQuestionCtrl.prototype.setOrphanAnswers = function () {
    var hasSolution;
    for (var i = 0; i < this.question.secondSet.length; i++) {
        hasSolution = false;
        for (var j = 0; j < this.question.solutions.length; j++) {
            if (this.question.secondSet[i].id === this.solutions.solutions[j].secondId) {
                hasSolution = true;
            }
        }
        if (!hasSolution) {
            this.orphanAnswers.push(this.question.secondSet[i]);
        }
    }
};

/**
 * Called on each jsPlumbConnectionEvent or jquery-ui drop event
 * also called at init
 * We need to share those informations with parent controllers
 * For that purpose we use a shared service
 */
MatchQuestionCtrl.prototype.updateStudentData = function () {
    // build answers
    this.questionPaper.answer = [];
    if (this.question.toBind) {
        for (var i = 0; i < this.connections.length; i++) {
            if (this.connections[i] !== '' && this.connections[i].source && this.connections[i].target) {
                var answer = this.connections[i].source + ',' + this.connections[i].target;
                this.questionPaper.answer.push(answer);
            }
        }

    } else { // toDrag
        for (var i = 0; i < this.dropped.length; i++) {
            if (this.dropped[i] !== '' && this.dropped[i].source && this.dropped[i].target) {
                var answer = this.dropped[i].source + ',' + this.dropped[i].target;
                this.questionPaper.answer.push(answer);
            }
        }
    }
    /*this.DataSharing.setStudentData(this.question, this.currentQuestionPaperData);*/
};

MatchQuestionCtrl.prototype.showFeedback = function () {
    this.savedAnswers = [];
    for (var i=0; i<this.dropped.length; i++) {
        this.savedAnswers.push(this.dropped[i]);
    }

    // get question answers and feedback ONLY IF NEEDED
    var promise = this.QuestionService.getSolutions(this.question);
    promise.then(function (result) {
        this.feedbackIsVisible = true;
        this.solutions = result.solutions;
        this.setScore();
        this.questionFeedback = result.feedback;
        if (!this.question.toBind) {
            $('.draggable').draggable("disable");
            $('.draggable').fadeTo(100, 0.3);
        }
        else {
            //$('.endPoints').draggable("disable");
        }
    }.bind(this));
};

MatchQuestionCtrl.prototype.hideFeedback = function () {
    this.feedbackIsVisible = false;
    if (!this.question.toBind) {
        $('.draggable').draggable('enable');
        $('.draggable').fadeTo(100, 1);

        for (var i=0; i<this.dropped.length; i++) {
            $('#draggable_' + this.dropped[i].source).draggable("disable");
            $('#draggable_' + this.dropped[i].source).fadeTo(100, 0.3);
        }
    }
};

MatchQuestionCtrl.prototype.answerIsSaved = function (item) {
    if (this.savedAnswers.indexOf(item) === -1) {
        return false;
    }
    else {
        return true;
    }
};

/**
 * Check if all answers are good and complete
 * and colours the panel accordingly
 * @param {type} label
 * @returns {Boolean}
 */
MatchQuestionCtrl.prototype.checkAnswerValidity = function (label) {
    var answers;
    if (this.question.toBind) {
        answers = this.connections;
    }
    else {
        answers = this.dropped;
    }

    // set the orphan answers list
    // (runs only once)
    if (!this.orphanAnswersAreChecked) {
        var hasSolution;
        for (var i=0; i<this.question.secondSet.length; i++) {
            hasSolution = false;
            for (var j=0; j<this.solutions.length; j++) {
                if (this.question.secondSet[i].id === this.solutions[j].secondId) {
                    hasSolution = true;
                }
            }
            if (!hasSolution) {
                this.orphanAnswers.push(this.question.secondSet[i]);
            }
        }
        this.orphanAnswersAreChecked = true;
    }

    /**
     * Check if all the right answers are selected by the student
     */
    var valid = true;
    var subvalid;
    for (var i = 0; i < this.solutions.length; i++) {
        if (this.solutions[i].secondId === label.id) {
            subvalid = false;
            for (var j=0; j<answers.length; j++) {
                if (this.solutions[i].firstId === answers[j].source && this.solutions[i].secondId === answers[j].target) {
                    subvalid = true;
                }
            }
            if (subvalid === false) {
                valid = false;
            }
        }
    }

    /**
     * Check if there are wrong answers selected by the student
     */
    var valid3 = true;
    for (var i=0; i<answers.length; i++) {
        if (answers[i].target === label.id) {
            subvalid = false;
            for (var j=0; j<this.solutions.length; j++) {
                if (this.solutions[j].firstId === answers[i].source && this.solutions[j].secondId === answers[i].target) {
                    subvalid = true;
                }
            }
            if (subvalid === false) {
                valid3 = false;
            }
        }
    }

    /**
     * Check if this label is an orphan, and if so,
     * check if the student left it unconnected
     */
    var valid2 = false;
    for (var i=0; i<this.orphanAnswers.length; i++) {
        if (this.orphanAnswers[i].id === label.id) {
            valid2 = true;
            for (var j=0; j<answers.length; j++) {
                if (this.orphanAnswers[i].id === answers[j].target) {
                    valid2 = false;
                }
            }
        }
    }

    if (valid2) {
        return true;
    }
    else {
        return valid && valid3;
    }
};

/**
 * Get the student's answers for this label
 * @param {type} label
 * @returns {Array}
 */
MatchQuestionCtrl.prototype.getStudentAnswersWithIcons = function (label) {
    var answers_to_check;
    if (this.question.toBind) {
        answers_to_check = this.connections;
    }
    else {
        answers_to_check = this.dropped;
    }
    var answers = [];
    for (var i=0; i<answers_to_check.length; i++) {
        if (answers_to_check[i].target === label.id) {
            for (var j=0; j<this.question.firstSet.length; j++) {
                if (this.question.firstSet[j].id === answers_to_check[i].source) {
                    answers.push(this.question.firstSet[j].data);
                }
            }
        }
    }
    var correctAnswers = [];
    for (var i=0; i<this.solutions.length; i++) {
        if (this.solutions[i].secondId === label.id) {
            for (var j=0; j<this.question.firstSet.length; j++) {
                if (this.question.firstSet[j].id === this.solutions[i].firstId) {
                    correctAnswers.push(this.question.firstSet[j].data);
                }
            }
        }
    }

    for (var i=0; i<answers.length; i++) {
        var selected = false;
        for (var j=0; j<correctAnswers.length; j++) {
            if (correctAnswers[j] === answers[i]) {
                answers[i] = answers[i] + " <i class='feedback-icon fa fa-check text-success'></i>";
                selected = true;
            }
        }
        if (!selected) {
            answers[i] = answers[i] + " <i class='feedback-icon fa fa-close text-danger'></i>";
        }
    }

    return answers;
};

/**
 * Get the student's answers for this label
 * @param {type} label
 * @returns {Array}
 */
MatchQuestionCtrl.prototype.getStudentAnswers = function (label) {
    var answers_to_check;
    if (this.question.toBind) {
        answers_to_check = this.connections;
    }
    else {
        answers_to_check = this.dropped;
    }
    var answers = [];
    for (var i=0; i<answers_to_check.length; i++) {
        if (answers_to_check[i].target === label.id) {
            for (var j=0; j<this.question.firstSet.length; j++) {
                if (this.question.firstSet[j].id === answers_to_check[i].source) {
                    answers.push(this.question.firstSet[j].data);
                }
            }
        }
    }
    return answers;
};

/**
 * Get the correct answers for this label
 * @param {type} label
 * @returns {Array}
 */
MatchQuestionCtrl.prototype.getCorrectAnswers = function (label) {
    var correctAnswers = [];
    var answersToShow = [];
    for (var i=0; i<this.solutions.length; i++) {
        if (this.solutions[i].secondId === label.id) {
            for (var j=0; j<this.question.firstSet.length; j++) {
                if (this.question.firstSet[j].id === this.solutions[i].firstId) {
                    correctAnswers.push(this.question.firstSet[j].data);
                }
            }
        }
    }

    /**
     * -----------------------------------------------------------------
     * Reste un souci dans la liste des réponses correctes, à corriger
     * -----------------------------------------------------------------
     */

    var studentAnswers = this.getStudentAnswers(label);

    for (var i=0; i<correctAnswers.length; i++) {
        var selected = false;
        for (var j=0; j<studentAnswers.length; j++) {
            if (correctAnswers[i] === studentAnswers[j]) {
                selected = true;
            }
        }
        if (selected) {
            answersToShow.push(correctAnswers[i]);
        }
    }

    return answersToShow;
};

MatchQuestionCtrl.prototype.getCurrentItemFeedBack = function (label) {
    for (var i=0; i<this.solutions.length; i++) {
        if (this.solutions[i].secondId === label.id) {
            return this.solutions[i].feedback;
        }
    }
};

MatchQuestionCtrl.prototype.dropIsValid = function (item) {
    for (var i=0; i<this.solutions.length; i++) {
        if (item.source === this.solutions[i].firstId && item.target === this.solutions[i].secondId) {
            return 1;
        }
    }

    return 2;
};

MatchQuestionCtrl.prototype.getDropFeedback = function (item) {
    for (var i=0; i<this.solutions.length; i++) {
        if (item.source === this.solutions[i].firstId && item.target === this.solutions[i].secondId) {
            return this.solutions[i].feedback;
        }
    }
};

MatchQuestionCtrl.prototype.bindEvents = function () {
    $timeout(function () {
        jsPlumb.bind("beforeDrop", function (info) {
            return this.handleBeforeDrop(info);
        });

        // remove one connection
        jsPlumb.bind("click", function (connection) {
            this.removeConnection(connection);
        });
    }.bind(this));
};

MatchQuestionCtrl.prototype.unbindEvents = function () {
    jsPlumb.unbind("beforeDrop");
    jsPlumb.unbind("click");
};

/* JSPLUMB */

MatchQuestionCtrl.prototype.initMatchQuestionJsPlumb = function (type) {
    if (type === 'bind') {
        this.MatchQuestionService.initBindMatchQuestion();
    } else if (type === 'drag') {
        this.MatchQuestionService.initDragMatchQuestion();
    }
};

/**
 * init previous answers given for a toBind Match question
 * DOM has to be ready before calling this method...
 * problem when updating a previously given answer
 */
MatchQuestionCtrl.prototype.addPreviousConnections = function () {
    if (this.questionPaper.answer && this.questionPaper.answer.length > 0) {
        // init previously given answer
        var sets = this.questionPaper.answer;
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

MatchQuestionCtrl.prototype.addPreviousDroppedItems = function () {
    this.dropped = [];
    if (this.questionPaper.answer && this.questionPaper.answer.length > 0) {
        // init previously given answer
        var sets = this.questionPaper.answer;
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
                this.savedAnswers.push(item);
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
MatchQuestionCtrl.prototype.handleBeforeDrop = function (data) {
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
MatchQuestionCtrl.prototype.removeConnection = function (data) {
    var sourceId = data.sourceId.replace('draggable_', '');
    var targetId = data.targetId.replace('droppable_', '');
    // connection is removed from dom even with this commented...
    // If not commented, code stops at this methods...
    // jsPlumb.detach(data);

    for (var i = 0; i < this.connections.length; i++) {
        if (this.connections[i].source === sourceId && this.connections[i].target === targetId) {
            this.connections.splice(i, 1);
        }
    }
    this.updateStudentData();
};

/**
 * Each time an item is drop we need to refresh data in DataSharing
 * Only for toDrag Match question
 * @returns {undefined}
 */
MatchQuestionCtrl.prototype.handleDragMatchQuestionDrop = function (event, ui) {
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
    this.$scope.$apply(function () {
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
 * Each time an item is removed from drop container we need to refresh data in DataSharing
 * @param {type} sourceId
 * @param {type} targetId
 * @returns {undefined}
 */
MatchQuestionCtrl.prototype.removeDropped = function (sourceId, targetId) {
    // remove from local array (this.dropped)
    for (var i = 0; i < this.dropped.length; i++) {
        if (this.dropped[i].source === sourceId && this.dropped[i].target === targetId) {
            this.dropped.splice(i, 1);
        }
    }
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

// Register controller into AngularJS
angular
    .module('Question')
    .controller('MatchQuestionCtrl', MatchQuestionCtrl);
