'use strict';

/**
 * Page 404 Controller
 */
function Page404Ctrl($scope) {
    $scope.pathListUrl = Routing.generate('claro_workspace_open_tool', {workspaceId: EditorApp.workspaceId, toolName: 'innova_path'});
}