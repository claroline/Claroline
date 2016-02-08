/**
 * Paper details directive controller
 * 
 */
(function () {
    'use strict';

    angular.module('Correction').controller('CorrectionGraphicCtrl', [
        function () {

            this.question = {};
            this.paper = {};

            this.init = function (question, paper) {
                this.question = question;
                this.paper = paper;
            };

            this.getAssetsDir = function () {
                return AngularApp.webDir;
            };



            this.createElements = function () {
                // create correction elements (backgrounds)
                for (var i = 0; i < this.question.solutions.length; i++) {
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
                    var coords = solution.value.split(',');
                    var imgPosition = $('#document-img-' + this.question.id).position();
                    var topValue = imgPosition.top + parseFloat(coords[1]);
                    var leftValue = imgPosition.left + parseFloat(coords[0]);

                    style += 'top:' + topValue.toString() + 'px;';
                    style += 'left:' + leftValue.toString() + 'px;';
                    style += 'background-color:' + solution.color + ';';
                    elem.setAttribute('style', style);
                    document.getElementById('document-img-container-' + this.question.id).appendChild(elem);
                }
                // add crosshairs
                for (var i = 0; i < this.paper.questions.length; i++) {
                    var answers = this.paper.questions[i].answer;
  
                    if (this.paper.questions[i].id.toString() === this.question.id.toString()) {
                        for (var j = 0; j < answers.length; j++) {
                            var elem = document.createElement('img');
                            elem.setAttribute('src', this.getAssetsDir() + '/bundles/ujmexo/images/graphic/answer.png');
                            var coords = answers[j].split('-');
                            if (coords[0] !== 'a' && coords[1] !== 'a') {
                                var imgPosition = $('#document-img-' + this.question.id).position();
                                var topValue = imgPosition.top + parseFloat(coords[1]);
                                var leftValue = imgPosition.left + parseFloat(coords[0]);
                                var style = '';
                                style += 'position:absolute;';
                                style += 'top:' + topValue.toString() + 'px;';
                                style += 'left:' + leftValue.toString() + 'px;';
                                elem.setAttribute('style', style);
                                document.getElementById('document-img-container-' + this.question.id).appendChild(elem);
                            }
                        }
                    }
                }
            };
        }
    ]);
})();