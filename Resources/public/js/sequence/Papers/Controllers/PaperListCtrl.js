(function () {
    'use strict';

    angular.module('PapersApp').controller('PaperListCtrl', [
        '$scope',
        '$routeParams',
        '$window',
        'PapersService',
        'CommonService',
        function ($scope, $routeParams, $window, PapersService, CommonService) {

            this.papers = [];
            this.exoId = exoId;
            // exoId is given by paper.html.twig
            // for now no better solutions
            // when 'exercise_home_view' will be in angular, this data will be given by route
            if(exoId){
                 var promise = PapersService.getAll(exoId);
                    promise.then(function (result) {
                        this.papers = result.data;
                    }.bind(this), function (error) {
                        console.log('error');
                    }.bind(this));
            }
            else{
                // $window.location = url;
            }
            
            this.generateUrl = function(witch, _id){
                return CommonService.generateUrl(witch, _id);
            }

            
        }
    ]);
})();