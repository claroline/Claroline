(function () {
    'use strict';

    angular.module('PapersApp').controller('PaperDetailsCtrl', [
        'paperPromise',
        'paperExercise',
        'user',
        function (paperPromise, paperExercise, user) {           
            this.paper = paperPromise.paper;
            this.questions = paperPromise.questions;
            this.exercise = paperExercise;            
            this.user = user;
            
            
        }
    ]);
})();