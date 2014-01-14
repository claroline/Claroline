'use strict';

/**
 * Main Controller
 */
function MainCtrl($scope, $http, $window, $location, $modal, HistoryFactory, ClipboardFactory, PathFactory, StepFactory, AlertFactory) {
    // Store symfony base partials route
    $scope.webDir = EditorApp.webDir;
    
    // Tiny MCE options
    if (typeof(configTinyMCE) != 'undefined' && null != configTinyMCE && configTinyMCE.length != 0) {
        // App as a config for tinyMCE => use it
        $scope.tinymceOptions = configTinyMCE;
    } 
    else {
        var home = window.Claroline.Home;

        var language = home.locale.trim();
        var contentCSS = home.asset + 'bundles/clarolinecore/css/tinymce/tinymce.css';
        
        // If no config, add default tiny
        $scope.tinymceOptions = {
            relative_urls: false,
            theme: 'modern',
            language: language,
            browser_spellcheck : true,
            autoresize_min_height: 100,
            autoresize_max_height: 500,
            content_css: contentCSS,
            plugins: [
                'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars fullscreen',
                'insertdatetime media nonbreaking save table directionality',
                'template paste textcolor emoticons code'
            ],
            toolbar1: 'styleselect | bold italic | alignleft aligncenter alignright alignjustify | preview fullscreen resourcePicker',
            toolbar2: 'undo redo | forecolor backcolor emoticons | bullist numlist outdent indent | link image media print code',
            paste_preprocess: function (plugin, args) {
                var link = $('<div>' + args.content + '</div>').text().trim(); //inside div because a bug of jquery
                var url = link.match(/^(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})$/);

                if (url) {
                    args.content = '<a href="' + link + '">' + link + '</a>';
                    home.generatedContent(link, function (data) {
                        insertContent(data);
                    }, false);
                }
            }
        };
    }

    $scope.alerts = AlertFactory.getAlerts();
    $scope.path = null;

    $scope.pathName = {};
    $scope.pathName.isUnique = true;
    
    $scope.initPath = function(path) {
        if (typeof path == 'undefined' || null == path || path.length === 0) {
            var path = PathFactory.generateNewPath();
        }
        
        if (typeof(path.steps) == 'undefined' || undefined == path.steps || null == path.steps || path.steps.length === 0) {
            var newPath = jQuery.extend(true, {}, path);
            // Missing root step => add it
            var rootStep = StepFactory.generateNewStep();
            rootStep.name = path.name;
            
            newPath.steps = [];
            newPath.steps.push(rootStep);
        }
        else {
            newPath = path;
        }
        
        $scope.path = newPath;

        // Update History if needed
        if (-1 === HistoryFactory.getHistoryState()) {
            HistoryFactory.update($scope.path);
        }
    };

    /**
     * Copy data into clipboard
     * @returns void
     */
    $scope.copy = function(data) {
        ClipboardFactory.copy(data);
    };

    /**
     * Paste current clipboard content
     * @returns void
     */
    $scope.paste = function(step) {
        ClipboardFactory.paste(step);
        HistoryFactory.update($scope.path);
    };

    /**
     * Undo last user modifications
     * @returns void
     */
    $scope.undo = function() {
        HistoryFactory.undo();
        $scope.path = PathFactory.getPath();
    };

    /**
     * Redo last history modifications
     * @returns void
     */
    $scope.redo = function() {
        HistoryFactory.redo();
        $scope.path = PathFactory.getPath();
    };

    /**
     * Check if path name is unique for current user and current workspace
     */
    $scope.checkNameIsUnique = function() {
        $http({
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            url: Routing.generate('innova_path_check_unique_name'),
            data: 'pathName=' + $scope.path.name + '&workspaceId=' + EditorApp.workspaceId
        })
        .success(function (data) {
            $scope.pathName.isUnique = data == 'true' ? true : false;
        });
    };
    
    /**
     * Save Path modifications in DB
     * @returns void
     */
    $scope.save = function(path) {
        var method = null;
        var route = null;

        if (undefined == path.name || null == path.name || path.name.length == 0) {
            // Path name is empty
            AlertFactory.addAlert('danger', 'Path can not be saved : path name is empty.');
        }
        else if (!$scope.pathName.isUnique) {
            // Name is not unique
            AlertFactory.addAlert('danger', 'Path can not be saved : path name is already used in this workspace.');
        }
        else {
            // All is fine => process save
            var data = 'pathName=' + path.name + '&path=' + angular.toJson(path) + '&workspaceId=' + EditorApp.workspaceId;

            if (undefined != path.description && null != path.description && path.description.length !== 0) {
                data += '&pathDescription=' + path.description;
            }
            
            if (EditorApp.pathId) {
                // Update existing path
                method = 'PUT';
                route = Routing.generate('innova_path_edit_path', {id: EditorApp.pathId});
            }
            else {
                // Create new path
                method = 'POST';
                route = Routing.generate('innova_path_add_path');
            }

            $http({
                method: method,
                url: route,
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                data: data
            })
            .success(function (data) {
                if (EditorApp.pathId) {
                    // Update success
                    AlertFactory.addAlert('success', 'Path updated.');
                }
                else {
                    EditorApp.pathId = data;
                    
                    // Create success
                    AlertFactory.addAlert('success', 'Path created.');
                    
                    var newRoute = $location.protocol() + '://' + $location.host();
                    
                    // Get symfony route
                    newRoute += Routing.generate('innova_path_editor', {workspaceId: EditorApp.workspaceId, pathId: EditorApp.pathId});
                    
                    // Add angular part
                    newRoute += '#' + $location.path();
                    
                    // TODO : find a way to not reload page
                    $window.location = newRoute;
                }
            })
            .error(function(data, status) {
                AlertFactory.addAlert('danger', 'Error while saving Path.');
            });

            // Clear history to avoid possibility to get a history state without path ID
            HistoryFactory.clear();
        }
    };
}