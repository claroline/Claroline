(function () {
  'use strict';

  angular
    .module('app')
    .factory('WebsiteTreeNode', WebsiteTreeNode);

  WebsiteTreeNode.$inject = [ '$http', '$q', '$sce', 'utilityFunctions', 'website.data' ];
  function WebsiteTreeNode($http, $q, $sce, utilityFunctions, websiteData) {

    var baseUrl = Routing.generate('icap_website_view', {websiteId: websiteData.id}) + "/page";

    function WebsitePageNode(websitePage, parent, tree) {
      this.id = this.richText = this.url = this.resourceNode = null;
      this.isHomepage = false;
      this.description = this.title = '';
      this.new = this.visible = true;
      this.isSection = false;
      this.visible = true;
      this.type = "blank";
      this.parent = utilityFunctions.isDefinedNotNull(parent) ? parent : null;
      this.tree = utilityFunctions.isDefinedNotNull(tree) ? tree : null;
      this.children = [];
      if (utilityFunctions.isDefinedNotNull(parent)) {
        if (this.tree == null) {
          this.tree = parent.tree;
        }
        if (this.tree != null && !this.new) {
          this.tree.addNewPageNode(this);
        }
      }
      if (utilityFunctions.isDefinedNotNull(websitePage)) {
        this.id = websitePage.id;
        this.title = ((websitePage.title != null) ? websitePage.title : '');
        this.type = websitePage.type;
        this.description = ((websitePage.description != null) ? websitePage.description : '');
        this.visible = websitePage.visible;
        this.isSection = websitePage.isSection;
        this.isHomepage = (websitePage.isHomepage != null) ? websitePage.isHomepage : false;
        if (this.isHomepage) this.tree.homepage = this;
        this.richText = websitePage.richText;
        this.url = websitePage.url;
        this.resourceNode = websitePage.resourceNode;
        this.resourceNodeType = websitePage.resourceNodeType;
        this.resourceNodeName = websitePage.resourceNodeName;
        this.resourceNodeWorkspace = websitePage.resourceNodeWorkspace;
        this.new = false;
        if (websitePage.children.length > 0) this.createChildrenPages(websitePage.children);
      }
    };

    WebsitePageNode.prototype.getTrustedUrl = function () {
      return $sce.trustAsResourceUrl(this.url);
    };

    WebsitePageNode.prototype.generateResourceUrl = function () {
      if (this.resourceNode != null && this.resourceNodeType != null) {
        var url = Routing.generate('claro_resource_open', {
          resourceType: this.resourceNodeType,
          node: this.resourceNode
        });
        return $sce.trustAsResourceUrl(url);
      }
      return null;
    };

    WebsitePageNode.prototype.trustedContent = function () {
      return $sce.trustAsHtml(this.richText);
    };

    WebsitePageNode.prototype.appendChildPage = function (websitePage) {
      var newChildNode = new WebsitePageNode(websitePage, this);
      this.insertNode(newChildNode, -1);

      return newChildNode;
    };

    WebsitePageNode.prototype.createChildrenPages = function (children) {
      if (children.length > 0) {
        for (var i = 0; i < children.length; i++) {
          var childPage = children[ i ];
          this.appendChildPage(childPage);
        }
      }
    };
    WebsitePageNode.prototype.appendNode = function (node) {
      this.insertNode(node, -1);
    };
    WebsitePageNode.prototype.insertNode = function (node, index) {
      node.parent = this;
      if (index == -1) {
        this.children.push(node);
      }
      else {
        this.children.splice(index, 0, node);
      }
    };
    WebsitePageNode.prototype.removeChildPageNode = function (node, index) {
      if (!angular.isDefined(index)) {
        index = -1;
        for (var i = 0; i < this.children.length; i++) {
          var childNode = this.children[ i ];
          if (childNode.id == node.id) {
            index = i;
            break;
          }
        }
      }
      this.children.splice(index, 1);
      var childrenLength = this.children.length;

      if (childrenLength > index) {
        return this.children[ index ];
      } else if (childrenLength > 0) {
        return this.children[ index - 1 ];
      } else {
        return this;
      }
    };
    WebsitePageNode.prototype.save = function () {
      if (this.new) {
        return this.create();
      } else {
        return this.update();
      }
    };
    WebsitePageNode.prototype.create = function () {
      var pageNode = this;
      return $http.post(baseUrl + "/" + this.parent.id, this.jsonSerialize())
        .then(function (response) {
          if (typeof response.data === 'object') {
            pageNode.id = response.data.id;
            pageNode.parent.appendNode(pageNode);
            pageNode.new = false;
            pageNode.tree.changeCurrentPageNode(pageNode);

            return pageNode;
          } else {
            return $q.reject(response.data);
          }
        }, function (response) {
          return $q.reject(response.data);
        });
    };
    WebsitePageNode.prototype.update = function () {
      var pageNode = this;
      return $http.put(baseUrl + "/" + this.id, this.jsonSerialize())
        .then(function (response) {
          if (typeof response.data === 'object') {
            return pageNode;
          } else {
            return $q.reject(response.data);
          }
        }, function (response) {
          return $q.reject(response.data);
        });
    };
    WebsitePageNode.prototype.setHomepage = function () {
      var pageNode = this;
      return $http.put(baseUrl + "/" + this.id + "/setHomepage")
        .then(function (response) {
          if (typeof response.data === 'object') {
            pageNode.isHomepage = true;
            var oldHomepage = pageNode.tree.homepage;
            if (oldHomepage != null) oldHomepage.isHomepage = false;
            pageNode.tree.homepage = pageNode;

            return pageNode;
          } else {
            return $q.reject(response.data);
          }
        }, function (response) {
          return $q.reject(response.data);
        });
    };
    WebsitePageNode.prototype.delete = function () {
      var pageNode = this;
      return $http.delete(baseUrl + "/" + this.id, this.jsonSerialize())
        .then(function (response) {
          if (typeof response.data === 'object') {
            var newCurrentNode = pageNode.parent.removeChildPageNode(pageNode);
            newCurrentNode.tree.changeCurrentPageNode(newCurrentNode);

            return pageNode;
          } else {
            return $q.reject(response.data);
          }
        }, function (response) {
          return $q.reject(response.data);
        });
    };
    WebsitePageNode.prototype.move = function (oldParent, newParent, oldIndex, newIndex) {
      var pageNode = this;
      var previousSiblingId = 0;
      if (newIndex > 0) {
        previousSiblingId = newParent.children[ newIndex - 1 ].id;
      }
      return $http.put(baseUrl + "/" + this.id + "/" + newParent.id + "/" + previousSiblingId, {})
        .then(function (response) {
          if (typeof response.data === 'object') {
            pageNode.tree.changeCurrentPageNode(pageNode);
            return true;
          } else {
            newParent.removeChildPageNode(pageNode, newIndex);
            oldParent.insertNode(pageNode, oldIndex);
            pageNode.tree.changeCurrentPageNode(pageNode);
            return false;
          }
        }, function (response) {
          return $q.reject(response.data);
        });
    }

    WebsitePageNode.prototype.jsonSerialize = function () {
      return {
        title: this.title,
        description: this.description,
        visible: this.visible,
        isSection: this.isSection,
        type: this.type,
        richText: this.richText,
        url: this.url,
        resourceNode: this.resourceNode,
        resourceNodeType: this.resourceNodeType
      }
    }

    return WebsitePageNode;
  };
})();