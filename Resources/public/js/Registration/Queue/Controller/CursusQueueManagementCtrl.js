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
        function ($http) {
            var vm = this;
            this.connectedUser = {id: 0};
            this.courses = [];
            this.sessions = [];
            this.sessionsQueues = [];
            this.search = '';
            this.tempSearch = '';
            this.isAdmin = false;
            this.nbQueues = {};
            
            this.searchCourses = function () {
                vm.search = vm.tempSearch;
                
                if (vm.search === '') {
                    getAllCourses();
                } else {
                    getSearchedCourses(vm.search);
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
            
            this.validateSessionQueue = function (queueId) {
                var route = Routing.generate(
                    'api_put_session_queue_validate',
                    {queue: queueId}
                );
                $http.put(route).then(
                    function (datas) {
                        
                        if (datas['status'] === 200) {
                            var queue = datas['data'];
                            console.log(queue);
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
            
            function getAllCourses()
            {
                var route = Routing.generate('api_get_courses_datas');
                $http.get(route).then(function (datas) {

                    if (datas['status'] === 200) {
                        vm.courses = datas['data']['courses'];
                        vm.sessions = datas['data']['sessions'];
                        vm.sessionsQueues = datas['data']['sessionsQueues'];
                        console.log(vm.sessionsQueues);
                    }
                });
            }
            
            function getSearchedCourses(search)
            {
                var route = Routing.generate('api_get_searched_courses_datas', {search: search});
                $http.get(route).then(function (datas) {

                    if (datas['status'] === 200) {
                        vm.courses = datas['data']['courses'];
                        vm.sessions = datas['data']['sessions'];
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
            
            function generateNbQueues()
            {
                for (var i = 0; i < vm.courses.length; i++) {
                    var courseId = vm.courses[i]['id'];
                    vm.nbQueues[courseId] = getNbQueues(courseId);
                }
            }
            
            function getNbQueues(courseId) {
                var count = 0;
                var sessions = vm.sessionsQueues[courseId];
                
                for (var sessionId in sessions) {
                    
                    if (vm.isAdmin || vm.isValidator(courseId, sessionId)) {
                        count += sessions[sessionId].length;
                    }
                }
                
                return count;
            };
            
            function initialize()
            {
                var coursesRoute = Routing.generate('api_get_courses_datas');
                $http.get(coursesRoute).then(function (datas) {

                    if (datas['status'] === 200) {
                        vm.courses = datas['data']['courses'];
                        vm.sessions = datas['data']['sessions'];
                        vm.sessionsQueues = datas['data']['sessionsQueues'];
                        
                        var userRoute = Routing.generate('claroline_core_api_user_api_connecteduser');
                        $http.get(userRoute).then(function (datas) {

                            if (datas['status'] === 200) {
                                vm.connectedUser = datas['data'];
                                checkAdminRole();
                                generateNbQueues();
                            }
                        });
                    }
                });
            }
            
            initialize();
        }
    ]);
})();