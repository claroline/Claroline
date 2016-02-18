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

    angular.module('CursusRegistrationModule').controller('CursusQueueManagementCtrl', [
        '$http',
        '$uibModal',
        function ($http, $uibModal) {
            var vm = this;
            this.connectedUser = {id: 0};
            this.courses = [];
            this.coursesQueues = [];
            this.sessionsQueues = [];
            this.search = '';
            this.tempSearch = '';
            this.isAdmin = false;
            
            var coursesColumns = [
                {
                    name: 'firstName',
                    prop: 'firstName',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('first_name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'lastName',
                    prop: 'lastName',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('last_name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'applicationDate',
                    prop: 'applicationDate',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('application_date', {}, 'cursus') + '</b>';
                    },
                    cellRenderer: function(scope) {
                        
                        return '<span>' + scope.$row['applicationDate'] + '</span>';
                    }
                },
                {
                    name: 'sessionName',
                    prop: 'sessionName',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('session', {}, 'cursus') + '</b>';
                    },
                    cellRenderer: function() {
                        
                        return '<span>[' + Translator.trans('to_define', {}, 'cursus') + ']</span>';
                    }
                },
                {
                    name: 'status',
                    prop: 'status',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('status', {}, 'platform') + '</b>';
                    },
                    cellRenderer: function(scope) {
                        var status = scope.$row['status'];
                        var userValidation = (status & 2) === 2;
                        var validatorValidation = (status & 4) === 4;
                        var cell = '';
                        
                        if (status === 1) {
                            cell += '<span class="label label-warning" data-toggle="tooltip" data-placement="top" data-container="body" data-title="' +
                                Translator.trans('waiting_for_session_transfer', {}, 'cursus') +
                                '">' +
                                '<i class="fa fa-clock-o"></i>&nbsp;' +
                                Translator.trans('session', {}, 'cursus') +
                                '</span><br>';
                        } else {
                            
                            if (userValidation) {
                                cell += '<span class="label label-primary" data-toggle="tooltip" data-placement="top" data-container="body" data-title="' +
                                    Translator.trans('waiting_user_validation', {}, 'cursus') +
                                    '">' +
                                    '<i class="fa fa-clock-o"></i>&nbsp;' +
                                    Translator.trans('user', {}, 'platform') +
                                    '</span><br>';
                            }

                            if (validatorValidation) {
                                cell += '<span class="label label-success" data-toggle="tooltip" data-placement="top" data-container="body" data-title="' +
                                    Translator.trans('waiting_validator_validation', {}, 'cursus') +
                                    '">' +
                                    '<i class="fa fa-clock-o"></i>&nbsp;' +
                                    Translator.trans('validator', {}, 'cursus') +
                                    '</span><br>';

                            }
                        }
                        
                        return cell;
                    }
                },
                {
                    name: 'actions',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('actions', {}, 'platform') + '</b>';
                    },
                    cellRenderer: function (scope) {
                        var status = scope.$row['status'];
                        var isValidated = (status === 1);
                        var userValidation = (status & 2) === 2;
                        var disabled = !vm.isAdmin && userValidation;
                        var cell = '';
                        
                        if (isValidated) {
                            cell += '<button class="btn btn-success btn-sm" ng-click="cqmc.transferCourseQueue(' +
                                scope.$row['id'] + ', ' + scope.$row['courseId'] +
                                ')">';
                                cell += '<i class="fa fa-sign-in"></i></button>&nbsp;';
                        } else {
                            cell += disabled ?
                                '<button class="btn btn-success btn-sm disabled" data-toggle="tooltip" data-placement="top" data-container="body" data-title="' +
                                Translator.trans('waiting_user_validation', {}, 'cursus') +
                                '">' :
                                '<button class="btn btn-success btn-sm" ng-click="cqmc.validateCourseQueue(' +
                                scope.$row['id'] +
                                ')">';
                                cell += '<i class="fa fa-check"></i></button>&nbsp;';
                        }
                        cell += '<button class="btn btn-danger btn-sm" ng-click="cqmc.declineCourseQueue(' +
                            scope.$row['id'] +
                            ')">' +
                            '<i class="fa fa-times"></i>' +
                            '</button>';
                    
                        return cell;
                    }
                }
            ];
            
            var sessionsColumns = [
                {
                    name: 'firstName',
                    prop: 'firstName',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('first_name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'lastName',
                    prop: 'lastName',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('last_name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'applicationDate',
                    prop: 'applicationDate',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('application_date', {}, 'cursus') + '</b>';
                    },
                    cellRenderer: function(scope) {
                        
                        return '<span>' + scope.$row['applicationDate'] + '</span>';
                    }
                },
                {
                    name: 'sessionName',
                    prop: 'sessionName',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('session', {}, 'cursus') + '</b>';
                    }
                },
                {
                    name: 'status',
                    prop: 'status',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('status', {}, 'platform') + '</b>';
                    },
                    cellRenderer: function(scope) {
                        var status = scope.$row['status'];
                        var userValidation = (status & 2) === 2;
                        var validatorValidation = (status & 4) === 4;
                        var cell = '<span>';
                        
                        if (userValidation) {
                            cell += '<span class="label label-primary" data-toggle="tooltip" data-placement="top" data-container="body" data-title="' +
                                Translator.trans('waiting_user_validation', {}, 'cursus') +
                                '">' +
                                '<i class="fa fa-clock-o"></i>&nbsp;' +
                                Translator.trans('user', {}, 'platform') +
                                '</span><br>';
                        }
                        
                        if (validatorValidation) {
                            cell += '<span class="label label-success" data-toggle="tooltip" data-placement="top" data-container="body" data-title="' +
                                Translator.trans('waiting_validator_validation', {}, 'cursus') +
                                '">' +
                                '<i class="fa fa-clock-o"></i>&nbsp;' +
                                Translator.trans('validator', {}, 'cursus') +
                                '</span><br>';
                            
                        }
                        cell += '</span>';
                        
                        return cell;
                    }
                },
                {
                    name: 'actions',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('actions', {}, 'platform') + '</b>';
                    },
                    cellRenderer: function (scope) {
                        var status = scope.$row['status'];
                        var userValidation = (status & 2) === 2;
                        var disabled = !vm.isAdmin && userValidation;
                        
                        var cell = disabled ?
                            '<button class="btn btn-success btn-sm disabled" data-toggle="tooltip" data-placement="top" data-container="body" data-title="' +
                            Translator.trans('waiting_user_validation', {}, 'cursus') +
                            '">' :
                            '<button class="btn btn-success btn-sm" ng-click="cqmc.validateSessionQueue(' +
                            scope.$row['id'] +
                            ')">';
                        cell += '<i class="fa fa-check"></i></button>&nbsp;';
                        cell += '<button class="btn btn-danger btn-sm" ng-click="cqmc.declineSessionQueue(' +
                            scope.$row['id'] +
                            ')">' +
                            '<i class="fa fa-times"></i>' +
                            '</button>';
                        
                        return cell;
                    }
                }
            ];
            
            this.coursesDataTableOptions = {
                scrollbarV: false,
                columnMode: 'force',
                headerHeight: 50,
                selectable: true,
                multiSelect: true,
                checkboxSelection: true,
                resizable: true,
                columns: coursesColumns
            };
            
            this.sessionsDataTableOptions = {
                scrollbarV: false,
                columnMode: 'force',
                headerHeight: 50,
                selectable: true,
                multiSelect: true,
                checkboxSelection: true,
                resizable: true,
                columns: sessionsColumns
            };
            
            this.searchDatas = function () {
                vm.search = vm.tempSearch;
                
                if (vm.search === '') {
                    getAllDatas();
                } else {
                    getSearchedDatas(vm.search);
                }
            };
            
            this.isValidator = function (courseId, sessionId) {
                var isValidator = false;
                var sessionIdInt = parseInt(sessionId);
                
                if (vm.sessions[courseId]) {
                    var sessions = vm.sessions[courseId];
                    
                    for (var i = 0; i < sessions.length; i++) {
                        
                        if (sessions[i]['id'] === sessionIdInt) {
                            var validators = sessions[i]['validators'];
                            
                            for (var j = 0; j < validators.length; j++) {
                                
                                if (validators[j]['id'] === vm.connectedUser['id']) {
                                    isValidator = true;
                                    break;
                                }
                            }
                            break;
                        }
                    }
                }
                
                return isValidator;
            };
            
            this.declineCourseQueue = function (queueId) {
                var route = Routing.generate(
                    'api_delete_course_queue',
                    {queue: queueId}
                );
                $http.delete(route).then(
                    function (datas) {
                        
                        if (datas['status'] === 200) {
                            var queueDatas = datas['data'];
                            removeCourseQueue(queueDatas['courseId'], queueDatas['id']);
                        }
                    },
                    function (datas) {
                        
                        if (datas['status'] === 403) {
                            console.log('Cannot decline');
                            console.log(datas['data']);
                        }
                    }
                );
                
            };
                    
            this.declineSessionQueue = function (queueId) {
                var route = Routing.generate(
                    'api_delete_session_queue',
                    {queue: queueId}
                );
                $http.delete(route).then(
                    function (datas) {
                        
                        if (datas['status'] === 200) {
                            var queueDatas = datas['data'];
                            removeSessionQueue(queueDatas['courseId'], queueDatas['id']);
                        }
                    },
                    function (datas) {
                        
                        if (datas['status'] === 403) {
                            console.log('Cannot decline');
                            console.log(datas['data']);
                        }
                    }
                );
            };
            
            this.validateCourseQueue = function (queueId) {
                var route = Routing.generate(
                    'api_put_course_queue_validate',
                    {queue: queueId}
                );
                $http.put(route).then(
                    function (datas) {
                        
                        if (datas['status'] === 200) {
                            var queueDatas = datas['data'];
                            
                            if (queueDatas['type'] === 'validated') {
                                updateCourseQueue(
                                    queueDatas['courseId'], 
                                    queueDatas['id'], 
                                    queueDatas['status']
                                );
                            }
                        }
                    },
                    function (datas) {
                        
                        if (datas['status'] === 403) {
                            console.log('Cannot validate');
                            console.log(datas['data']);
                        }
                    }
                );
            };
            
            this.validateSessionQueue = function (queueId) {
                var route = Routing.generate(
                    'api_put_session_queue_validate',
                    {queue: queueId}
                );
                $http.put(route).then(
                    function (datas) {
                        
                        if (datas['status'] === 200) {
                            var queueDatas = datas['data'];
                            
                            if (queueDatas['type'] === 'validated') {
                                updateSessionQueue(
                                    queueDatas['courseId'], 
                                    queueDatas['id'], 
                                    queueDatas['status']
                                );
                            } else if (queueDatas['type'] === 'registered') {
                                removeSessionQueue(queueDatas['courseId'], queueDatas['id']);
                            }
                        }
                    },
                    function (datas) {
                        
                        if (datas['status'] === 403) {
                            console.log('Cannot validate');
                            console.log(datas['data']);
                        }
                    }
                );
            };
            
            this.transferCourseQueue = function (queueId, courseId) {
                $uibModal.open({
                    templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Queue/Partial/sessions_choices_transfer_modal.html',
                    controller: 'SessionsChoicesTransferModalCtrl',
                    controllerAs: 'sctmc',
                    resolve: {
                        queueId: function () { return queueId; },
                        courseId: function () { return courseId; },
                        callback: function () { return removeCourseQueue;}
                    }
                });
            };
            
            function getAllDatas()
            {
                var route = Routing.generate('api_get_registration_queues_datas');
                $http.get(route).then(function (datas) {

                    if (datas['status'] === 200) {
                        vm.courses = datas['data']['courses'];
                        vm.coursesQueues = datas['data']['coursesQueues'];
                        vm.sessionsQueues = datas['data']['sessionsQueues'];
                    }
                });
            }
            
            function getSearchedDatas(search)
            {
                var route = Routing.generate('api_get_registration_queues_datas_by_search', {search: search});
                $http.get(route).then(function (datas) {

                    if (datas['status'] === 200) {
                        vm.courses = datas['data']['courses'];
                        vm.coursesQueues = datas['data']['coursesQueues'];
                        vm.sessionsQueues = datas['data']['sessionsQueues'];
                    }
                });
            }
            
            function checkAdminRole()
            {
                if (vm.connectedUser['roles']) {
                    var roles = vm.connectedUser['roles'];
                    
                    for (var i = 0; i < roles.length; i++) {
                        
                        if (roles[i]['name'] === 'ROLE_ADMIN') {
                            vm.isAdmin = true;
                            break;
                        }
                    }
                }
            }
            
            var removeCourseQueue = function (courseId, queueId) {
                
                if (vm.coursesQueues[courseId]) {
                
                    for (var i = 0; i < vm.coursesQueues[courseId].length; i++) {
                        var queueDatas = vm.coursesQueues[courseId][i];
                        
                        if (queueDatas['id'] === queueId) {
                            vm.coursesQueues[courseId].splice(i, 1);
                            break;
                        }
                    }
                }
            };
            
            var removeSessionQueue = function (courseId, queueId) {
            
                if (vm.sessionsQueues[courseId]) {
                
                    for (var i = 0; i < vm.sessionsQueues[courseId].length; i++) {
                        var queueDatas = vm.sessionsQueues[courseId][i];
                        
                        if (queueDatas['id'] === queueId) {
                            vm.sessionsQueues[courseId].splice(i, 1);
                            break;
                        }
                    }
                }
            };
            
            var updateCourseQueue = function (courseId, queueId, status) {
            
                if (vm.coursesQueues[courseId]) {
                
                    for (var i = 0; i < vm.coursesQueues[courseId].length; i++) {
                        var queueDatas = vm.coursesQueues[courseId][i];
                        
                        if (queueDatas['id'] === queueId) {
                            vm.coursesQueues[courseId][i]['status'] = status;
                            break;
                        }
                    }
                }
            };
            
            var updateSessionQueue = function (courseId, queueId, status) {
            
                if (vm.sessionsQueues[courseId]) {
                
                    for (var i = 0; i < vm.sessionsQueues[courseId].length; i++) {
                        var queueDatas = vm.sessionsQueues[courseId][i];
                        
                        if (queueDatas['id'] === queueId) {
                            vm.sessionsQueues[courseId][i]['status'] = status;
                            break;
                        }
                    }
                }
            };
            
            function initialize()
            {
                var userRoute = Routing.generate('claroline_core_api_user_api_connecteduser');
                $http.get(userRoute).then(function (datas) {

                    if (datas['status'] === 200) {
                        vm.connectedUser = datas['data'];
                        checkAdminRole();
                        
                        var coursesRoute = Routing.generate('api_get_registration_queues_datas');
                        $http.get(coursesRoute).then(function (datas) {

                            if (datas['status'] === 200) {
                                vm.courses = datas['data']['courses'];
                                vm.coursesQueues = datas['data']['coursesQueues'];
                                vm.sessionsQueues = datas['data']['sessionsQueues'];
                            }
                        });
                    }
                });
            }
            
            initialize();
        }
    ]);
})();