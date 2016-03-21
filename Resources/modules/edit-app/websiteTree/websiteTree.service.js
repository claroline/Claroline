(function () {
  'use strict';

  angular
    .module('app')
    .factory('websiteTree', websiteTree);

  websiteTree.$inject = [ 'WebsiteTreeNode', 'utilityFunctions', 'website.data' ];
  function websiteTree(WebsiteTreeNode, utilityFunctions, websiteData) {
    var service = {
      root: null,
      homepage: null,
      currentPageNode: null,
      currentEditPageNode: null,
      confirmDelete: false,
      removeCurrentPageNode: removeCurrentPageNode,
      createEmptyPageNode: createEmptyPageNode,
      changeCurrentPageNode: changeCurrentPageNode,
      confirmNodeDelete: confirmNodeDelete,
      saveCurrentEditPageNode: saveCurrentEditPageNode,
      movePageNode: movePageNode
    };

    init();

    return service;
    ///////////////////////

    function init() {
      service.root = new WebsiteTreeNode(websiteData.pages[ 0 ], null, service);
      service.currentPageNode = (service.root.children.length > 0) ? service.root.children[ 0 ] : service.root;
      service.currentEditPageNode = (service.root.children.length > 0) ? service.root.children[ 0 ] : null;
    }

    function removeCurrentPageNode() {
      service.confirmDelete = false;
      return service.currentPageNode.delete();
    }

    function createEmptyPageNode(parentPageNode) {
      parentPageNode = utilityFunctions.isDefinedNotNull(parentPageNode) ? parentPageNode : service.root;
      service.currentPageNode = parentPageNode;
      service.currentEditPageNode = new WebsiteTreeNode(null, parentPageNode, service);
    }

    function changeCurrentPageNode(node) {
      service.currentPageNode = this.currentEditPageNode = node;
    }

    function confirmNodeDelete(node) {
      service.changeCurrentPageNode(node);
      service.confirmDelete = true;
    }

    function saveCurrentEditPageNode() {
      return service.currentEditPageNode.save();
    }

    function movePageNode(node, oldParent, newParent, oldIndex, newIndex) {
      if (oldParent == null) oldParent = service.root;
      if (newParent == null) newParent = service.root;
      return node.move(oldParent, newParent, oldIndex, newIndex);
    }
  };
})();