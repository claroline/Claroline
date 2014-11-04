'use strict';

angular.module('websiteApp').run([
    '$templateCache',
    function($templateCache) {
        $templateCache.put('tree_renderer.tpl',
            '<div class="tree-node tree-node-content menu-item-container"  ng-class="{\'selected\':menu.currentPageNode.id==node.id}" ng-click="menu.changeCurrentPageNode(node)">'+
                '<a class="btn btn-danger btn-xs" data-nodrag ng-click="confirmNodeDelete(node); $event.stopPropagation();"><span class="fa fa-trash-o"></span></a>'+
                '<a class="btn btn-primary btn-xs" data-nodrag ng-click="createNewPageForm(node); $event.stopPropagation();"><span class="fa fa-plus"></span></a>'+
                '<span ng-style="{\'margin-left\':((depth()-1)*14)+\'px\',}"><a class="btn btn-success btn-xs" ng-if="node.children && node.children.length > 0" data-nodrag ng-click="toggle(this)"><span class="fa" ng-class="{\'fa-chevron-right\': collapsed, \'fa-chevron-down\': !collapsed}"></span></a> {{node.title}}</span>'+
            '</div>'+
            '<ul ui-tree-nodes ng-model="node.children" sortable="sortOptions" ng-class="{hidden: collapsed}">'+
                '<li id="nd-{{node.id}}" ng-repeat="node in node.children" ui-tree-node ui-tree-handle ng-include="\'tree_renderer.tpl\'"></li>'+
            '</ul>'
        )
    }
]);