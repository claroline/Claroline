'use strict'

import angular from 'angular/index'

angular.module('app').run([
  '$templateCache',
  function ($templateCache) {
    $templateCache.put('tree_renderer.tpl',
      '<div class="tree-node tree-node-content menu-item-container" data-ng-class="{\'selected\':vm.menu.currentPageNode.id==node.id}" ng-click="vm.menu.changeCurrentPageNode(node)"></span>' +
      '<span data-ng-style="{\'margin-left\':((depth()-1)*15)+\'px\',}" ui-tree-handle class="menu-dragndrop-icon"><a><span class="fa fa-arrows"></span></a></span>' +
      '<span class="menu-dropdown-icon" style="margin-left: 3px;"><a ng-if="node.children && node.children.length > 0" data-nodrag ng-click="toggle(this)"><span class="fa" ng-class="{\'fa-angle-right\': collapsed, \'fa-angle-down\': !collapsed}"></span></a></span>' +
      '<span class="menu-type-icon"><i class="fa" ng-class="{\'fa-file-code-o\':node.type==\'blank\', \'fa-folder\':node.type==\'resource\', \'fa-globe\':node.type==\'url\'}"></i><i data-ng-if="node.isHomepage" class="fa fa-home"></i></span>' +
      '<span data-ng-class="{\'tree-node-section\':node.isSection, \'tree-node-invisible\':!node.visible}">{{node.title}}</span>' +
      '</div>' +
      '<ul data-ui-tree-nodes data-ng-model="node.children" sortable="sortOptions" data-ng-class="{hidden: collapsed}">' +
      '<li id="nd-{{node.id}}" data-ng-repeat="node in node.children" data-ui-tree-node data-ng-include="\'tree_renderer.tpl\'"></li>' +
      '</ul>'
    )
  }
])
