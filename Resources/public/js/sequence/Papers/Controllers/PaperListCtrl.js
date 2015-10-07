(function () {
    'use strict';

    angular.module('PapersApp').controller('PaperListCtrl', [
        '$routeParams',
        '$window',
        'PapersService',
        'CommonService',
        function ($routeParams, $window, PapersService, CommonService) {

            this.papers = [];          
            this.exoId = $routeParams.eid;
            if(this.exoId){
                 var promise = PapersService.getAll(this.exoId);
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