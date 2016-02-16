/**
 * Path Service
 */
(function () {
    'use strict';

    angular.module('PathModule').factory('PathService', [
        '$http',
        '$q',
        '$timeout',
        '$location',
        'AlertService',
        'StepService',
        function PathService($http, $q, $timeout, $location, AlertService, StepService) {
            /**
             * ID of the Path
             * @type {Number}
             */
            var id = null;

            /**
             * Data of the Path
             * @type {object}
             */
            var path = null;

            /**
             * Maximum depth of a Path
             * @type {number}
             */
            var maxDepth = 8;

            /**
             * State of the Path summary
             * @type {object}
             */
            var summary = {
                opened: true
            };
            /**
             * list af all steps following the current step
             * @type {Array}
             */
            var arrnextall = [];
            var evaluations = [];

            var usergrouplist=[];
            var userteamlist=[];
            var evaluationstatuses=[];
            var useringroup=[];
            var userinteam=[];
            var deferred = $q.defer();

            //expose the promises
            var usergrouppromise = $http.get(Routing.generate('innova_path_criteria_usergroup',{}))
                .success(function(response){
                    usergrouplist = response;
                    deferred.resolve(response);
                });
            var evaluationstatusespromise = $http.get(Routing.generate('innova_path_criteria_activitystatuses', {}))
                .success(function (response) {
                    evaluationstatuses = response;
                    deferred.resolve(response);
                });
            var useringrouppromise = $http.get(Routing.generate('innova_path_criteria_groupsforuser', {}))
                .success(function (response) {
                    useringroup = response;
                    deferred.resolve(response);
                });
            var userinteampromise = $http.get(Routing.generate('innova_path_criteria_teamsforuser', {}))
                .success(function (response) {
                    userinteam = response;
                    deferred.resolve(response);
                });
            //userteampromise : different from above, because needs to pass the pathe_id parameter, not available here
            var userteampromise;
            return {
                usergrouppromise:usergrouppromise,
                evaluationstatusespromise:evaluationstatusespromise,
                useringrouppromise:useringrouppromise,
                userteampromise:function userteampromise(pathid) {
                    var deferred = $q.defer();
                    var params = {'id':id};
                    $http
                        .get(Routing.generate('innova_path_criteria_teamsforws', params))
                        .success(function (response) {
                            userteamlist = response;
                            deferred.resolve(response);
                        }.bind(this))
                        .error(function (response) {
                            deferred.reject(response);
                        });
                    return deferred.promise;
                },
                //create a get method for the variable to retrieve
                getUsergroupData:function getUsergroupData(){
                    return usergrouplist;
                },
                getEvaluationStatusesData:function getEvaluationStatusesData(){
                    return evaluationstatuses;
                },
                getUseringroupData:function getUseringroupData(){
                    return useringroup;
                },
                getUserteamData:function getUserteamData(){
                    return userteamlist;
                },
                getUserinteamData:function getUserinteamData(){
                    return userinteam;
                },
                /**
                 * get list of all child steps of a step
                 * @returns {array}
                 */
                getArrNextAll: function getArrNextAll(step) {
                    //flush the array from previous call
                    this.arrnextall = [];
                    //call the recursive function
                    this.getNextAll(step);
                    return this.arrnextall;
                },
                /**
                 * Get ID of the current Path
                 * @returns {Number}
                 */
                getId: function getId() {
                    return id;
                },
                /**
                 * Set ID of the current Path
                 * @param {Number} value
                 */
                setId: function setId(value) {
                    id = value;
                },

                /**
                 * Get current Path
                 * @returns {Object}
                 */
                getPath: function getPath() {
                    return path;
                },

                /**
                 * Set current Path
                 * @param {Object} value
                 */
                setPath: function setPath(value) {
                    path = value;
                },

                /**
                 * Get max depth of the Path
                 * @returns {Number}
                 */
                getMaxDepth: function getMaxDepth() {
                    return maxDepth;
                },

                /**
                 * Get summary state
                 * @returns {Object}
                 */
                getSummaryState: function getSummaryState() {
                    return summary;
                },

                /**
                 * Toggle summary state
                 */
                toggleSummaryState: function toggleSummaryState() {
                    summary.opened = !summary.opened;
                },

                /**
                 * Set summary state
                 * @param {Boolean} value
                 */
                setSummaryState: function setSummaryState(value) {
                    summary.opened = value;
                },

                /**
                 * Initialize a new Path structure
                 */
                initialize: function initialize() {
                    // Create a generic root step
                    var rootStep = StepService.new();

                    path.steps.push(rootStep);

                    // Set root step as current step
                    this.goTo(rootStep);
                },

                /**
                 * Initialize a new Path structure from a Template
                 */
                initializeFromTemplate: function initializeFromTemplate() {

                },
                conditionValidityCheck: function conditionValidityCheck() {
                    return true;
                },
                /**
                 * Save modification to DB
                 */
                save: function save() {
                    // Transform data to make it acceptable by Symfony
                    var dataToSave = {
                        innova_path: {
                            name:             path.name,
                            description:      path.description,
                            breadcrumbs:      path.breadcrumbs,
                            summaryDisplayed: path.summaryDisplayed,
                            structure:        angular.toJson(path)
                        }
                    };

                    var deferred = $q.defer();

                    $http
                        .put(Routing.generate('innova_path_editor_wizard_save', { id: id }), dataToSave)

                        .success(function (response) {
                            if ('ERROR_VALIDATION' === response.status) {
                                for (var i = 0; i < response.messages.length; i++) {
                                    AlertService.addAlert('error', response.messages[i]);
                                }

                                deferred.reject(response);
                            } else {
                                // Get updated data
                                angular.merge(path, response.data);

                                // Display confirm message
                                AlertService.addAlert('success', Translator.trans('path_save_success', {}, 'path_wizards'));

                                deferred.resolve(response);
                            }
                        }.bind(this))

                        .error(function (response) {
                            AlertService.addAlert('error', Translator.trans('path_save_error', {}, 'path_wizards'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                /**
                 * Publish path modifications
                 */
                publish: function publish() {
                    var deferred = $q.defer();

                    $http
                        .put(Routing.generate('innova_path_publish', { id: id }))

                        .success(function (response) {
                            if ('ERROR' === response.status) {
                                for (var i = 0; i < response.messages.length; i++) {
                                    AlertService.addAlert('error', response.messages[i]);
                                }

                                deferred.reject(response);
                            } else {
                                // Get updated data
                                angular.merge(path, response.data);

                                // Display confirm message
                                AlertService.addAlert('success', Translator.trans('publish_success', {}, 'path_wizards'));

                                deferred.resolve(response);
                            }
                        }.bind(this))

                        .error(function (response) {
                            AlertService.addAlert('error', Translator.trans('publish_error', {}, 'path_wizards'));

                            deferred.reject(response);
                        });

                    return deferred.promise;
                },

                /**
                 * Display the step
                 * @param step
                 */
                goTo: function goTo(step) {
                    // Ugly as fuck, but can't make it work without timeout
                    $timeout(function(){
                        if (angular.isObject(step)) {
                            $location.path('/' + step.id);
                        } else {
                            // User must be able to navigate to root step, so does not check authorization
                            $location.path('/');
                        }
                    }, 1);
                },

                /**
                 * Get the previous step
                 * @param step
                 * @returns {Object|Step}
                 */
                getPrevious: function getPrevious(step) {
                    var previous = null;

                    // If step is the root of the tree it has no previous element
                    if (angular.isDefined(step) && angular.isObject(step) && 0 !== step.lvl) {
                        var parent = this.getParent(step);
                        if (angular.isObject(parent) && angular.isObject(parent.children)) {
                            // Get position of the current element
                            var position = parent.children.indexOf(step);
                            if (-1 !== position && angular.isObject(parent.children[position - 1])) {
                                // Previous sibling found
                                var previousSibling = parent.children[position - 1];

                                // Get down to the last child of the sibling
                                var lastChild = this.getLastChild(previousSibling);
                                if (angular.isObject(lastChild)) {
                                    previous = lastChild;
                                } else {
                                    // Get the sibling
                                    previous = previousSibling;
                                }
                            } else {
                                // Get the parent as previous element
                                previous = parent;
                            }
                        }
                    }

                    return previous;
                },

                /**
                 * Get the last child of a step
                 * @param step
                 * @returns {Object|Step}
                 */
                getLastChild: function getLastChild(step) {
                    var lastChild = null;

                    if (angular.isDefined(step) && angular.isObject(step) && angular.isObject(step.children) && angular.isObject(step.children[step.children.length - 1])) {
                        // Get the element in children collection (children are ordered)
                        var child = step.children[step.children.length - 1];
                        if (!angular.isObject(child.children) || 0 >= child.children.length) {
                            // It is the last child
                            lastChild = child;
                        } else {
                            // Go deeper to search for the last child
                            lastChild = this.getLastChild(child);
                        }
                    }

                    return lastChild;
                },

                /**
                 * Get the next step
                 * @param step
                 * @returns {Object|Step}
                 */
                getNext: function getNext(step) {
                    var next = null;

                    if (angular.isDefined(step) && angular.isObject(step)) {
                        if (angular.isObject(step.children) && angular.isObject(step.children[0])) {
                            // Get the first child
                            next = step.children[0];
                        } else if (0 !== step.lvl) {
                            // Get the next sibling
                            next = this.getNextSibling(step);
                        }
                    }

                    return next;
                },
                /**
                 * Get all the steps following a step
                 * meaning : all children and parents next siblings
                 * @param step
                 */
                getNextAll: function getNextAll(step) {
                    if (angular.isDefined(step) && angular.isObject(step)) {
                        //If this is not final step
                        if (this.getNext(step)) {
                            this.arrnextall.push(this.getNext(step));
                            this.getNextAll(this.getNext(step));
                        } else {
                            this.arrnextall.push(step);
                        }
                    }
                    return true;
                },
                getAllEvaluationForSteps: function getAllEvaluationForSteps(path){
                    var steps = [];
                    var evaluations = [];
                    var activity = null;
                    var root = this.getRoot() ;
                    steps = this.getNextAll(root);
                    if (steps.length>0){
                        for(var i=0;i<steps.length;i++){
                            activity = steps[i].activityId;
                            evaluations.push({"step":steps[i].id,"eval":this.stepConditionsService.getActivityEvaluation(activity)});
                        }
                    }
                    return evaluations;
                },

                /**
                 * Retrieve all evaluation for a path
                 *
                 * @param path
                 */
                getAllEvaluationsForPath: function getAllEvaluationsForPath(path) {
                    var deferred = $q.defer();
                    var params = {'path':path};
                    $http
                        .get(Routing.generate('innova_path_evaluation', params))
                        .success(function (response) {
                            this.setEvaluation(response);
                            deferred.resolve(response);
                        }.bind(this))
                        .error(function (response) {
                            deferred.reject(response);
                        });
                    return deferred.promise;
                },
                setEvaluation: function setEvaluation(data){
                  this.evaluations = data;
                },

                /**
                 * Retrieve the next sibling of an element
                 * @param step
                 * @returns {Object|Step}
                 */
                getNextSibling: function getNextSibling(step) {
                    var sibling = null;

                    if (0 !== step.lvl) {
                        var parent = this.getParent(step);
                        if (angular.isObject(parent.children)) {
                            // Get position of the current element
                            var position = parent.children.indexOf(step);
                            if (-1 !== position && angular.isObject(parent.children[position + 1])) {
                                // Next sibling found
                                sibling = parent.children[position + 1];
                            }
                        }

                        if (null == sibling) {
                            // Sibling not found => try to ascend one level
                            sibling = this.getNextSibling(parent);
                        }
                    }

                    return sibling;
                },

                /**
                 * Get all parents of a Step (from the Root to the nearest step parent)
                 * @param step
                 * @param [reverse] - sort parents from the nearest parent to the Root
                 */
                getParents: function getParents(step, reverse) {
                    var parents = [];

                    var parent = this.getParent(step);
                    if (parent) {
                        // Add parent to the list
                        parents.push(parent);

                        // Get other parents
                        parents = parents.concat(this.getParents(parent));

                        // Reorder parent array
                        parents.sort(function (a, b) {
                            if (a.lvl < b.lvl) {
                                return -1;
                            } else if (a.lvl > b.lvl) {
                                return 1;
                            }

                            return 0;
                        });

                        if (reverse) {
                            parents.reverse();
                        }
                    }

                    return parents;
                },

                /**
                 * Get the parent of a step
                 * @param step
                 */
                getParent: function getParent(step) {
                    var parentStep = null;

                    this.browseSteps(path.steps, function (parent, current) {
                        if (step.id == current.id) {
                            parentStep = parent;

                            return true;
                        }

                        return false
                    });

                    return parentStep;
                },

                /**
                 * Loop over all steps of path and execute callback
                 * Iteration stops when callback returns true
                 * @param {array}    steps    - an array of steps to browse
                 * @param {function} callback - a callback to execute on each step (called with args `parentStep`, `currentStep`)
                 */
                browseSteps: function browseSteps(steps, callback) {
                    /**
                     * Recursively loop through the steps to execute callback on each step
                     * @param   {object} parentStep
                     * @param   {object} currentStep
                     * @returns {boolean}
                     */
                    function recursiveLoop(parentStep, currentStep) {
                        var terminated = false;

                        // Execute callback on current step
                        if (typeof callback === 'function') {
                            terminated = callback(parentStep, currentStep);
                        }

                        if (!terminated && typeof currentStep.children !== 'undefined' && currentStep.children.length !== 0) {
                            for (var i = 0; i < currentStep.children.length; i++) {
                                terminated = recursiveLoop(currentStep, currentStep.children[i]);
                            }
                        }
                        return terminated;
                    }

                    if (typeof steps !== 'undefined' && steps.length !== 0) {
                        for (var j = 0; j < steps.length; j++) {
                            var terminated = recursiveLoop(null, steps[j]);

                            if (terminated) {
                                break;
                            }
                        }
                    }
                },

                /**
                 * Recalculate steps level in tree
                 * @param {array} steps - an array of steps to reorder
                 */
                reorderSteps: function reorderSteps(steps) {
                    this.browseSteps(steps, function (parent, step) {
                        if (null !== parent) {
                            step.lvl = parent.lvl + 1;
                        } else {
                            step.lvl = 0;
                        }
                    });
                },

                addStep: function addStep(parent, displayNew) {
                    if (parent.lvl < maxDepth) {
                        // Create a new step
                        var step = StepService.new(parent);

                        if (displayNew) {
                            // Open created step
                            this.goTo(step);
                        }
                    }
                },

                /**
                 * Remove a step from the path's tree
                 * @param {array}  steps        - an array of steps to browse
                 * @param {object} stepToDelete - the step to delete
                 */
                removeStep: function removeStep(steps, stepToDelete) {
                    this.browseSteps(steps, function (parent, step) {
                        var deleted = false;
                        if (step === stepToDelete) {
                            if (typeof parent !== 'undefined' && null !== parent) {
                                var pos = parent.children.indexOf(stepToDelete);
                                if (-1 !== pos) {
                                    parent.children.splice(pos, 1);

                                    deleted = true;
                                }
                            } else {
                                // We are deleting the root step
                                var pos = steps.indexOf(stepToDelete);
                                if (-1 !== pos) {
                                    steps.splice(pos, 1);

                                    deleted = true;
                                }
                            }
                        }
                        return deleted;
                    });
                },

                /**
                 * Get the Root of the Path
                 * @returns {Object}
                 */
                getRoot: function getRoot() {
                    var root = null;

                    if (angular.isDefined(path) && angular.isObject(path) && angular.isObject(path.steps) && angular.isObject(path.steps[0])) {
                        root = path.steps[0];
                    }

                    return root;
                },

                /**
                 * Find a Step in the Path by its ID
                 * @param   {number} stepId
                 * @returns {object}
                 */
                getStep: function getStep(stepId) {
                    var step = null;

                    if (angular.isDefined(path) && angular.isObject(path)) {
                        this.browseSteps(path.steps, function searchStep(parent, current) {
                            if (current.id == stepId) {
                                step = current;

                                return true; // Kill the search
                            }

                            return false;
                        });
                    }

                    return step;
                },

                getStepInheritedResources: function getStepInheritedResources(steps, step) {
                    function retrieveInheritedResources(stepToFind, currentStep, inheritedResources) {
                        var stepFound = false;

                        if (stepToFind.id !== currentStep.id && typeof currentStep.children !== 'undefined' && null !== currentStep.children) {
                            // Not the step we search for => search in children
                            for (var i = 0; i < currentStep.children.length; i++) {
                                stepFound = retrieveInheritedResources(stepToFind, currentStep.children[i], inheritedResources);
                                if (stepFound) {
                                    if (typeof currentStep.resources !== 'undefined' && null !== currentStep.resources) {
                                        // Get all resources which must be sent to children
                                        for (var j = currentStep.resources.length - 1; j >= 0; j--) {
                                            if (currentStep.resources[j].propagateToChildren) {
                                                // Current resource must be available for children
                                                var resource = angular.copy(currentStep.resources[j]);
                                                resource.parentStep = {
                                                    id: currentStep.id,
                                                    lvl: currentStep.lvl,
                                                    name: currentStep.name
                                                };
                                                resource.isExcluded = stepToFind.excludedResources.indexOf(resource.id) != -1;
                                                inheritedResources.unshift(resource);
                                            }
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        else {
                            stepFound = true;
                        }

                        return stepFound;
                    }

                    var stepFound = false;
                    var inheritedResources = [];

                    if (steps && steps.length !== 0) {
                        for (var i = 0; i < steps.length; i++) {
                            var currentStep = steps[i];
                            stepFound = retrieveInheritedResources(step, currentStep, inheritedResources);
                            if (stepFound) {
                                break;
                            }
                        }
                    }

                    return inheritedResources;
                }
            }
        }
    ]);
})();