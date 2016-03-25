angular.module('Question').controller('GraphicQuestionCtrl', [
    '$ngBootbox',
    '$scope',
    'CommonService',
    'QuestionService',
    'DataSharing',
    function ($ngBootbox, $scope, CommonService, QuestionService, DataSharing) {
        this.question = {};
        // keep coord(s)
        this.coords = []; // student answers
        this.original = []; // original position of crosshairs images
        this.currentQuestionPaperData = {};
        this.usedHints = [];

        // instant feedback data
        this.canSeeFeedback = false;
        this.feedbackIsVisible = false;
        this.questionFeedback = "";

        this.notFoundZones = [];

        this.init = function (question, canSeeFeedback) {
            // those data are updated by view and sent to common service as soon as they change
            this.currentQuestionPaperData = DataSharing.setCurrentQuestionPaperData(question);
            this.question = question;
            this.canSeeFeedback = canSeeFeedback;
            // init student data question object
            DataSharing.setStudentData(question);

            if (this.currentQuestionPaperData.hints && this.currentQuestionPaperData.hints.length > 0) {
                // init used hints display
                for (var i = 0; i < this.currentQuestionPaperData.hints.length; i++) {
                    this.getHintData(this.currentQuestionPaperData.hints[i]);
                }
            }
            // init coords answer array in any case
            // id order is totally arbitrary
            for (var i = 0; i < this.question.coords.length; i++) {
                var coord = {
                    id: this.question.coords[i].id,
                    x: 'a', // to ensure ujm compatibility
                    y: 'a'
                };
                this.coords.push(coord);
            }
            // init draggable elements with answers if any
            if (this.currentQuestionPaperData.answer && this.currentQuestionPaperData.answer.length > 0) {
                for (var i = 0; i < this.currentQuestionPaperData.answer.length; i++) {
                    var answserArray = this.currentQuestionPaperData.answer[i].split('-');
                    if (answserArray[0] !== 'a' || answserArray[1] !== 'a') {
                        var coord = this.coords[i];
                        coord.x = answserArray[0] !== 'a' ? parseFloat(answserArray[0]):0;
                        coord.y = answserArray[1] !== 'a' ? parseFloat(answserArray[1]):0;
                    }

                }
            }
        };

        this.initPreviousAnswers = function () {
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

            this.setSolution();
        };

        this.setSolution = function () {
            var promise = QuestionService.getQuestionSolutions(this.question.id);
            promise.then(function (result) {
                this.solutions = result.solutions;
                for (var i=0; i<this.solutions.length; i++) {
                    this.notFoundZones.push(this.solutions[i]);
                }
                this.questionFeedback = result.feedback;
                this.showRightAnswerZones();
            }.bind(this));
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
                    // get dragged element id
                    var draggedId = $(this).attr('id').replace('crosshair_', '');
                    // get dragged coordonates with offset instead of position
                    var coordX = $(this).offset().left - $('#document-img').offset().left;
                    var coordY = $(this).offset().top - $('#document-img').offset().top;
                    // var coordX = $(this).offset().left - $('#document-img').offset().left;
                    // var coordY = $(this).offset().top - $('#document-img').offset().top;

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

        this.resetAnswers = function () {
            for (var i = 0; i < this.coords.length; i++) {
                $('#crosshair_' + this.coords[i].id).css('top', 0);
                $('#crosshair_' + this.coords[i].id).css('bottom', 'auto');
                $('#crosshair_' + this.coords[i].id).css('right', 'auto');
                $('#crosshair_' + this.coords[i].id).css('left', 0);
                $('#crosshair_' + this.coords[i].id).css('position', 'relative');
            }

            this.coords = [];
            for (var i = 0; i < this.question.coords.length; i++) {
                var coord = {
                    id: this.question.coords[i].id,
                    x: 0,
                    y: 0
                };
                this.coords.push(coord);
            }
            this.updateStudentData();
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
         * Listen to show-feedback event (broadcasted by ExercisePlayerCtrl)
         */
        $scope.$on('show-feedback', function (event, data) {
            this.showFeedback();
        }.bind(this));


        this.showFeedback = function () {
            // get question answers and feedback ONLY IF NEEDED
            /*var promise = QuestionService.getQuestionSolutions(this.question.id);
             promise.then(function (result) {*/
            this.feedbackIsVisible = true;
            this.disableDraggable();
            this.showRightAnswerZones();
            this.setWrongFeedback();
            //}.bind(this));
        };

        this.showRightAnswerZones = function () {

            /**
             * Create an array of already found/not found zones
             * And then, prevent that a pointer gets checked if
             * it is in a zone already found
             */

            var pointX = 0;
            var pointY = 0;
            var startX = 0;
            var startY = 0;
            var start;
            var centerX = 0;
            var centerY = 0;

            for (var i=0; i<this.solutions.length; i++) {
                for (var j=0; j<$(".crosshair").length; j++) {
                    var firstElementId = $(".crosshair")[j].id;
                    var firstElementNumId = firstElementId.replace("crosshair_", "");
                    var topElementsHeight = $("#" + firstElementId).parent().parent().parent().prop("offsetHeight");
                    pointX = $("#" + firstElementId).prop("x");
                    pointY = $("#" + firstElementId).prop("y") - topElementsHeight;
                    start = this.solutions[i].value.split(",");
                    startX = parseFloat(start[0]) +26;
                    startY = parseFloat(start[1]) +12;
                    centerX = startX + this.solutions[i].size/2;
                    centerY = startY + this.solutions[i].size/2;
                    var endX = startX + this.solutions[i].size;
                    var endY = startY + this.solutions[i].size;

                    var distance = Math.sqrt((centerX-pointX)*(centerX-pointX) + (centerY-pointY)*(centerY-pointY));
                    distance = Math.round(distance);

                    if (((this.solutions[i].size >= distance*2 && this.solutions[i].shape === "circle") || (this.solutions[i].shape === "square" && pointX > startX && pointX < endX && pointY > startY && pointY < endY)) && this.notFoundZones.indexOf(this.solutions[i]) !== -1) {
                        var rightPointY = pointY + topElementsHeight;
                        $("#" + firstElementId).replaceWith("<i id='crosshair_valid_" + firstElementNumId + "' class='color-success fa fa-check' data-toggle='tooltip' style='top: " + rightPointY + "px; left: " + pointX + "px; position: absolute; z-index: 3;' title='" + this.solutions[i].feedback + "' ></i>");

                        var solution = this.solutions[i];
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

                        this.notFoundZones.splice(this.notFoundZones.indexOf(this.solutions[i]), 1);
                    }
                }
            }
        };

        this.setWrongFeedback = function () {
            var elements = $(".crosshair");
            for (var i=0; i<elements.length; i++) {
                var id = elements[i].id;
                var numId = id.replace("crosshair_", "");
                var rightPointY = $("#" + id).prop("y");
                var pointX = $("#" + id).prop("x");
                $("#" + id).replaceWith("<i id='crosshair_invalid_" + numId + "' class='color-danger fa fa-close crosshair_invalid' data-toggle='tooltip' style='top: " + rightPointY + "px; left: " + pointX + "px; position: absolute; z-index: 3;' title='" + this.solutions[i].feedback + "' ></i>");
            }
        };

        this.disableDraggable = function () {
            for (var i=0; i<this.question.coords.length; i++) {
                $("#crosshair_" + this.question.coords[i].id).draggable('disable');
            }
        };

        this.enableDraggable = function () {
            for (var i=0; i<this.question.coords.length; i++) {
                $("#crosshair_" + this.question.coords[i].id).draggable();
                $("#crosshair_" + this.question.coords[i].id).draggable('enable');
            }
        };

        $scope.$on('hide-feedback', function (event, data) {
            this.hideFeedback();
        }.bind(this));

        this.hideFeedback = function () {
            this.feedbackIsVisible = false;
            this.hideWrongFeedbacks();
            this.enableDraggable();
        };

        this.hideWrongFeedbacks = function () {
            var elements = $(".crosshair_invalid");
            for (var i=0; i<elements.length; i++) {
                var former_id = elements[i].id;
                var id = elements[i].id.replace("_invalid", "");
                var top = $("#" + former_id).css('top');
                var left = $("#" + former_id).css('left');
                $("#" + former_id).replaceWith("<img id='" + id + "' class='crosshair draggable ui-draggable ui-draggable-handle' data-ng-src='" + this.getAssetsDir() + "bundles/ujmexo/images/graphic/answer.png' src='" + this.getAssetsDir() + "bundles/ujmexo/images/graphic/answer.png' style='top:" + top + "; position: relative; left: " + left + "; z-index: 1;'/>");
            }
        };

        /**
         * Checks if the question has meta
         * @returns {boolean}
         */
        this.questionHasOtherMeta = function () {
            return CommonService.objectHasOtherMeta(this.question);
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
            for (var i = 0; i < this.coords.length; i++) {
                var answerString = this.coords[i].x.toString() + '-' + this.coords[i].y.toString();
                answers.push(answerString);
            }
            this.currentQuestionPaperData.answer = answers;
            DataSharing.setStudentData(this.question, this.currentQuestionPaperData);
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