/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
    
(function () {
    'use strict';

    angular.module('CursusRegistrationModule').controller('CursusRegistrationSearchCtrl', [
        '$routeParams',
        '$http',
        '$uibModal',
        function ($routeParams, $http, $uibModal) {
            var vm = this;
            this.initialized = false;
            this.cursusList = [];
            this.cursusRoots = [];
            this.hierarchy = [];
            this.search = $routeParams['search'];
            this.tempSearch = $routeParams['search'];
            this.selectedCursusId = null;
            this.hoveredCursusId = 0;
            
            var columns = [
                {
                    name: 'title',
                    prop: 'title',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('title', {}, 'platform') + '</b>';
                    },
                    cellRenderer: function (scope) {
                        
                        return '<a class="pointer-hand" ng-class="(crsc.selectedCursusId === ' +
                            scope.$row['id'] +
                            ') ? \'claroline-tag-highlight\' : \'\'" ng-click="crsc.getHierarchy(' + 
                            scope.$row['id'] +
                            ')">' + 
                            scope.$row['title'] + 
                            '</a>';
                    }
                },
                {
                    name: 'code',
                    prop: 'code',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('code', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'desciption',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('description', {}, 'platform') + '</b>';
                    },
                    cellRenderer: function (scope) {
                        var description = scope.$row['description'];
                        var courseDescription = scope.$row['courseDescription'];
                        var hasDescription = (description !== null && description !== '') ||
                            (courseDescription !== null && courseDescription !== '');
                        
                        var descriptionElement = hasDescription ?
                            '<i class="fa fa-eye pointer-hand" ng-click="crsc.showDescription(' + 
                            scope.$row['id'] + 
                            ')"></i>' :
                            '-';
                        
                        return '<span>' + descriptionElement + '</span>';
                    }
                },
                {
                    name: 'cursus',
                    prop: 'root',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('cursus', {}, 'cursus') + '</b>';
                    },
                    cellRenderer: function (scope) {
                        var rootTitle = '-';
                        var rootId = scope.$row['root'];
                        
                        if (vm.cursusRoots[rootId]) {
                            rootTitle = vm.cursusRoots[rootId]['title'];
                        }
                        
                        return '<span>' + rootTitle + '</span>';
                    }
                },
                {
                    name: 'type',
                    headerRenderer: function () {
                        
                        return '<b>' + translator.trans('type', {}, 'platform') + '</b>';
                    },
                    cellRenderer: function (scope) {
                        var type = translator.trans('cursus', {}, 'cursus');                        
                        var courseId = scope.$row['course'];
                        
                        if (courseId) {
                            type = translator.trans('course', {}, 'cursus');
                        }
                        
                        return '<span>' + type + '</span>';
                    }
                }
            ];
            
            this.dataTableOptions = {
                scrollbarV: false,
                columnMode: 'force',
                headerHeight: 50,
                resizable: true,
                columns: columns
            };
            
            function initialize()
            {
                
                if (!vm.initialized) {
                    var route = Routing.generate(
                        'api_get_datas_for_searched_cursus_registration',
                        {search: vm.tempSearch}
                    );
                    $http.get(route).then(function (datas) {

                        if (datas['status'] === 200) {
                            vm.cursusList = datas['data']['searchedCursus'];
                            vm.cursusRoots = datas['data']['roots'];
                            vm.initialized = true;
                        }
                    });
                }
            };
            
            function getCursusInfos(cursusId)
            {
                var infos = null;
                
                for (var i = 0; i < vm.cursusList.length; i++) {
                    
                    if (vm.cursusList[i]['id'] === cursusId) {
                        infos = vm.cursusList[i];
                        break;
                    }
                }
                
                return infos;
            }
            
            this.getHierarchy = function (cursusId) {
                vm.selectedCursusId = cursusId;
                
                var route = Routing.generate(
                    'api_get_datas_for_cursus_hierarchy',
                    {cursus: cursusId}
                );
                $http.get(route).then(function (datas) {

                    if (datas['status'] === 200) {
                        vm.hierarchy = datas['data'];
                    }
                });
            };
            
            this.showDescription = function (cursusId) {
                var infos = getCursusInfos(cursusId);
                var description = infos['courseDescription'];
                
                if (description === null || description === '') {
                    description = infos['description'];
                }
                
                if (infos !== null) {
                    $uibModal.open({
                        templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Cursus/Partial/cursus_description_modal.html',
                        controller: 'CursusDescriptionModalCtrl',
                        controllerAs: 'cdmc',
                        resolve: {
                            title: function () { return infos['title']; },
                            description: function () { return description; }
                        }
                    });
                }
            };
            
            initialize();
        }
    ]);
})();