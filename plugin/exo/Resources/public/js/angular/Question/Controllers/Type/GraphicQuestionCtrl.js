/**
 * Graphic Question Controller
 * @param {FeedbackService} FeedbackService
 * @constructor
 */
var GraphicQuestionCtrl = function GraphicQuestionCtrl(FeedbackService) {
    AbstractQuestionCtrl.apply(this, arguments);

    // Initialize answer if needed
    if (null === this.questionPaper.answer || typeof this.questionPaper.answer === 'undefined') {
        this.questionPaper.answer = [];
    }

    // init coord answer array in any case
    // id order is totally arbitrary
    for (var i = 0; i < this.question.coords.length; i++) {
        this.coords.push({
            id: this.question.coords[i].id,
            x: 'a', // to ensure ujm compatibility
            y: 'a'
        });
    }

    // init draggable elements with answers if any
    if (this.questionPaper.answer && this.questionPaper.answer.length > 0) {
        for (var i = 0; i < this.questionPaper.answer.length; i++) {
            var answerArray = this.questionPaper.answer[i].split('-');
            if (answerArray[0] !== 'a' || answerArray[1] !== 'a') {
                var coord = this.coords[i];
                coord.x = answerArray[0] !== 'a' ? parseFloat(answerArray[0]):0;
                coord.y = answerArray[1] !== 'a' ? parseFloat(answerArray[1]):0;
            }

        }
    }
};

// Extends AbstractQuestionCtrl
GraphicQuestionCtrl.prototype = Object.create(AbstractQuestionCtrl.prototype);

// Set up dependency injection (get DI from parent too)
GraphicQuestionCtrl.$inject = AbstractQuestionCtrl.$inject;

/**
 *
 * @type {Array}
 */
GraphicQuestionCtrl.prototype.coords = []; // student answers

/**
 *
 * @type {Array}
 */
GraphicQuestionCtrl.prototype.original = []; // original position of crosshairs images

/**
 *
 * @type {Array}
 */
GraphicQuestionCtrl.prototype.notFoundZones = [];

/**
 *
 */
GraphicQuestionCtrl.prototype.initPreviousAnswers = function initPreviousAnswers() {
    for (var i = 0; i < this.coords.length; i++) {
        // console.log('yep');
        // ensure that we are not in default values
        if (this.coords[i].x !== 'a' && this.coords[i].y !== 'a') {
            // crosshair
            var $crosshair = $('#crosshair_' + this.coords[i].id);
            /*
             * compute crosshair coords
             * the method is a bit complex and I'm not sure it's the better one...
             * when we record the student answer in database we have to values that are relative to the document image container
             * The point coordinates are the top left corner so we need to center the crosshair with $crosshair[0].width / 2
             * assuming corosshair image is a square
             *
             * if we user center-text class on col elements this does not work anymore...
             */
            var coordX = $('#document-img').offset().left + this.coords[i].x - $crosshair.offset().left + ($crosshair[0].width / 2);
            var coordY = $('#document-img').offset().top + this.coords[i].y - $crosshair.offset().top + ($crosshair[0].width / 2);
            $('#crosshair_' + this.coords[i].id).css('top', coordY);
            $('#crosshair_' + this.coords[i].id).css('position', 'relative');
            $('#crosshair_' + this.coords[i].id).css('left', coordX);
            $('#crosshair_' + this.coords[i].id).css('z-index', i + 1);
        }
    }
};

/**
 *
 */
GraphicQuestionCtrl.prototype.initDragAndDrop = function initDragAndDrop() {
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
            // get dragged element id
            var draggedId = $(this).attr('id').replace('crosshair_', '');
            // get dragged coordonates with offset instead of position
            var coordX = $(this).offset().left - $('#document-img').offset().left;
            var coordY = $(this).offset().top - $('#document-img').offset().top;

            // update this.coords
            for (var i = 0; i < self.coords.length; i++) {
                if (self.coords[i].id === draggedId) {
                    self.coords[i].x = coordX;
                    self.coords[i].y = coordY;
                }
            }
            // update student data in shared service
            self.updateStudentData();
        }
    });
};

/**
 *
 */
GraphicQuestionCtrl.prototype.reset = function reset() {
    for (var i = 0; i < this.coords.length; i++) {
        $('#crosshair_' + this.coords[i].id).css('top', 0);
        $('#crosshair_' + this.coords[i].id).css('bottom', 'auto');
        $('#crosshair_' + this.coords[i].id).css('right', 'auto');
        $('#crosshair_' + this.coords[i].id).css('left', 0);
        $('#crosshair_' + this.coords[i].id).css('position', 'relative');
    }

    this.coords.splice(0, this.coords.length);
    for (var i = 0; i < this.question.coords.length; i++) {
        this.coords.push({
            id: this.question.coords[i].id,
            x: 0,
            y: 0
        });
    }

    this.updateStudentData();
};

/**
 *
 * @returns {*|null}
 */
GraphicQuestionCtrl.prototype.getAssetsDir = function getAssetsDir() {
    return AngularApp.webDir;
};

/**
 *
 */
GraphicQuestionCtrl.prototype.showRightAnswerZones = function showRightAnswerZones() {
    var pointX = 0;
    var pointY = 0;
    var startX = 0;
    var startY = 0;
    var start;
    var centerX = 0;
    var centerY = 0;

    for (var i = 0; i < this.question.solutions.length; i++) {
        for (var j=0; j<$(".crosshair").length; j++) {
            var firstElementId = $(".crosshair")[j].id;
            var firstElementNumId = firstElementId.replace("crosshair_", "");
            var topElementsHeight = $("#" + firstElementId).parent().parent().parent().prop("offsetHeight");
            pointX = $("#" + firstElementId).prop("x");
            pointY = $("#" + firstElementId).prop("y") - topElementsHeight;
            start = this.question.solutions[i].value.split(",");
            startX = parseFloat(start[0]) +26;
            startY = parseFloat(start[1]) +12;
            centerX = startX + this.question.solutions[i].size/2;
            centerY = startY + this.question.solutions[i].size/2;
            var endX = startX + this.question.solutions[i].size;
            var endY = startY + this.question.solutions[i].size;

            var distance = Math.sqrt((centerX-pointX)*(centerX-pointX) + (centerY-pointY)*(centerY-pointY));
            distance = Math.round(distance);

            if (((this.question.solutions[i].size >= distance*2 && this.question.solutions[i].shape === "circle") || (this.question.solutions[i].shape === "square" && pointX > startX && pointX < endX && pointY > startY && pointY < endY)) && this.notFoundZones.indexOf(this.question.solutions[i]) !== -1) {
                var rightPointY = pointY + topElementsHeight;
                $("#" + firstElementId).replaceWith("<i id='crosshair_valid_" + firstElementNumId + "' class='text-success feedback-info fa fa-check' data-toggle='tooltip' style='top: " + rightPointY + "px; left: " + pointX + "px; position: absolute; z-index: 3;' title='" + (this.question.solutions[i].feedback ? this.question.solutions[i].feedback : '') + "' ></i>");

                var solution = this.question.solutions[i];
                var elem = document.createElement('div');
                var style = '';
                style += 'position:absolute;';
                style += 'border:1px solid #eee;';
                style += 'opacity:0.6;';
                style += 'height:' + solution.size.toString() + 'px;';
                style += 'width:' + solution.size.toString() + 'px;';

                if (solution.shape === "circle") {
                    style += 'border-radius:50%;';
                }

                style += 'top:' + startY.toString() + 'px;';
                style += 'left:' + startX.toString() + 'px;';
                style += 'background-color:' + solution.color + ';';
                elem.setAttribute('style', style);
                var className = "answerField";
                elem.setAttribute('class', className);
                document.getElementsByClassName('droppable-container')[0].appendChild(elem);

                this.notFoundZones.splice(this.notFoundZones.indexOf(this.question.solutions[i]), 1);
            }
        }
    }
};

/**
 *
 */
GraphicQuestionCtrl.prototype.setWrongFeedback = function setWrongFeedback() {
    var elements = $(".crosshair");
    for (var i = 0; i < elements.length; i++) {
        var id = elements[i].id;
        var numId = id.replace("crosshair_", "");
        var rightPointY = $("#" + id).prop("y");
        var pointX = $("#" + id).prop("x");
        $("#" + id).replaceWith("<i id='crosshair_invalid_" + numId + "' class='text-danger fa fa-close feedback-info crosshair_invalid' data-toggle='tooltip' style='top: " + rightPointY + "px; left: " + pointX + "px; position: absolute; z-index: 3;' title='" + (this.question.solutions[i].feedback ? this.question.solutions[i].feedback : '') + "' ></i>");
    }
};

/**
 *
 */
GraphicQuestionCtrl.prototype.disableDraggable = function disableDraggable() {
    for (var i = 0; i < this.question.coords.length; i++) {
        $("#crosshair_" + this.question.coords[i].id).draggable('disable');
    }
};

/**
 *
 */
GraphicQuestionCtrl.prototype.enableDraggable = function enableDraggable() {
    for (var i = 0; i < this.question.coords.length; i++) {
        $("#crosshair_" + this.question.coords[i].id).draggable();
        $("#crosshair_" + this.question.coords[i].id).draggable('enable');
    }
};

/**
 *
 */
GraphicQuestionCtrl.prototype.onFeedbackShow = function onFeedbackShow() {
    for (var i=0; i < this.question.solutions.length; i++) {
        this.notFoundZones.push(this.question.solutions[i]);
    }

    this.disableDraggable();
    this.showRightAnswerZones();
    this.setWrongFeedback();
};

/**
 *
 */
GraphicQuestionCtrl.prototype.onFeedbackHide = function onFeedbackHide() {
    this.hideWrongFeedbacks();
    this.enableDraggable();
};

/**
 *
 */
GraphicQuestionCtrl.prototype.hideWrongFeedbacks = function hideWrongFeedbacks() {
    var elements = $(".crosshair_invalid");
    for (var i = 0; i < elements.length; i++) {
        var former_id = elements[i].id;
        var id = elements[i].id.replace("_invalid", "");
        var top = $("#" + former_id).css('top');
        var left = $("#" + former_id).css('left');
        $("#" + former_id).replaceWith("<img id='" + id + "' class='crosshair draggable ui-draggable ui-draggable-handle' data-ng-src='" + this.getAssetsDir() + "bundles/ujmexo/images/graphic/answer.png' src='" + this.getAssetsDir() + "bundles/ujmexo/images/graphic/answer.png' style='top:" + top + "; position: relative; left: " + left + "; z-index: 1;'/>");
    }
};

/**
 * Called on each checkbox / radiobutton click
 * We need to share those information with parent controllers
 * For that purpose we use a shared service
 */
GraphicQuestionCtrl.prototype.updateStudentData = function updateStudentData() {
    this.questionPaper.answer.splice(0, this.questionPaper.answer.length);
    for (var i = 0; i < this.coords.length; i++) {
        var answerString = this.coords[i].x.toString() + '-' + this.coords[i].y.toString();
        this.questionPaper.answer.push(answerString);
    }
};

// Register controller into AngularJS
angular.module('Question')
    .controller('GraphicQuestionCtrl', GraphicQuestionCtrl);