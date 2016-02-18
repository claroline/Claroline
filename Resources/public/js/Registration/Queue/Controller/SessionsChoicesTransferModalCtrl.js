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

    angular.module('CursusRegistrationModule').controller('SessionsChoicesTransferModalCtrl', [
        '$http',
        '$uibModal',
        '$uibModalStack',
        'queueId',
        'courseId',
        'callback',
        function ($http, $uibModal, $uibModalStack, queueId, courseId, callback) {
            var vm = this;
            var queueId = queueId;
            var courseId = courseId;
            var callback = callback;
            this.sessions = [];
            this.selectedSession = null;
            this.errorMessage = '';
            
            var columns = [
                {
                    name: 'checkboxes',
                    headerRenderer: function () {
                        
                        return '<b></b>';
                    },
                    cellRenderer: function(scope) {
                        
                        return '<span><input type="radio" name="session-selection" value="' +
                            scope.$row['id'] +
                            '" ng-model="sctmc.selectedSession"></span>';
                    }
                },
                {
                    name: 'name',
                    prop: 'name',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('name', {}, 'platform') + '</b>';
                    }
                },
                {
                    name: 'sessionStatus',
                    headerRenderer: function () {
                        
                        return '<b>' + Translator.trans('status', {}, 'platform') + '</b>';
                    },
                    cellRenderer: function(scope) {
                        var status = scope.$row['session_status'];
                        var cell = '<span>';
                        
                        if (status === 0) {
                            cell += Translator.trans('session_not_started', {}, 'cursus');
                        } else if (status === 1) {
                            cell += Translator.trans('session_open', {}, 'cursus');
                        } else if (status === 2) {
                            cell += Translator.trans('session_closed', {}, 'cursus');
                        }
                        cell += '</span>';
                        
                        return cell;
                    }
                }
            ];
            
            this.dataTableOptions = {
                scrollbarV: false,
                columnMode: 'force',
                headerHeight: 50,
                selectable: true,
                multiSelect: true,
                checkboxSelection: true,
                resizable: true,
                columns: columns
            };
            
            this.closeModal = function () {
                $uibModalStack.dismissAll();
            };
            
            this.confirmModal = function () {
                
                if (vm.selectedSession) {
                    var route = Routing.generate(
                        'api_post_course_queued_user_transfer',
                        {'queue': queueId, session: vm.selectedSession}
                    );
                    $http.post(route).then(function (datas) {
                        
                        if (datas['status'] === 200) {
                            var results = datas['data'];
                            
                            if (results['status'] === 'success') {
                                callback(courseId, queueId);
                                vm.closeModal();
                            } else {
                                vm.errorMessage = Translator.trans(
                                    'session_not_enough_place_msg',
                                    {
                                        sessionName: results['datas']['sessionName'],
                                        courseTitle: results['datas']['courseTitle'],
                                        courseCode: results['datas']['courseCode'],
                                        remainingPlaces: results['datas']['remainingPlaces']
                                    },
                                    'cursus'
                                );
                            }
                        }
                    });
                }
            };
            
            this.deleteErrorMessage = function () {
                vm.errorMessage = '';
            };
            
            function getAvailableSessions()
            {
                var route = Routing.generate(
                    'api_get_available_sessions_by_course',
                    {course: courseId}
                );
                $http.get(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        vm.sessions = datas['data'];
                    }
                });
            }
            
            getAvailableSessions();
        }
    ]);
})();