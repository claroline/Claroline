var treeApp = angular.module('treeApp', []);

treeApp.factory('Tree', ['$filter', 'WebsitePageNode', 'UtilityFunctions', function($filter, WebsitePageNode, UtilityFunctions){
	function Tree(websiteRootPage) {
        this.root = null;
        this.allNodes = [];
        this.root = new WebsitePageNode(websiteRootPage, null, this);
        this.currentPageNode = (this.root.children.length>0)?this.root.children[0]:this.root;
        this.currentEditPageNode = (this.root.children.length>0)?this.root.children[0]:null;
        this.confirmDelete = false;
	};
	Tree.prototype.getPageNodeById = function(id) {
		return this.allNodes[id];
	};
	Tree.prototype.addNewPageNode = function(node) {
		this.allNodes[node.id] = node;
	};
	Tree.prototype.removePageNode = function(node) {
		if(!this.allNodes.hasOwnProperty(node.id))
		{
			return;
		}
		delete this.allNodes[node.id];

		return node;
	};
	Tree.prototype.removeCurrentPageNode = function() {
        this.confirmDelete = false;
		return this.currentPageNode.delete();
	};
    Tree.prototype.createEmptyPageNode = function(parentPageNode) {
        parentPageNode = UtilityFunctions.isDefinedNotNull(parentPageNode)?parentPageNode:this.root;
        this.currentPageNode = parentPageNode;
        this.currentEditPageNode = new WebsitePageNode(null, parentPageNode, this);
    }
    Tree.prototype.changeCurrentPageNode = function(node) {
        this.currentPageNode = this.currentEditPageNode = node;
    }
    Tree.prototype.confirmNodeDelete = function(node) {
        this.changeCurrentPageNode(node);
        this.confirmDelete = true;
    }
    Tree.prototype.saveCurrentEditPageNode = function() {
        return this.currentEditPageNode.save();
    }
    Tree.prototype.movePageNode = function(node, oldParent, newParent, oldIndex, newIndex) {
        if (oldParent == null) oldParent = this.root;
        if (newParent == null) newParent = this.root;
        return node.move(oldParent, newParent, oldIndex, newIndex);
    }
	
	return Tree;
}]);

treeApp.factory('WebsitePageNode', ['$http', '$q', '$sce', 'UtilityFunctions', function($http, $q, $sce, UtilityFunctions){
	function WebsitePageNode(websitePage, parent, tree) {
        this.id = this.title = this.richText = this.url = this.resourceNode = null;
        this.new = this.visible = true;
        this.isSection = false;
        this.visible = true;
        this.type = "blank";
        this.parent = UtilityFunctions.isDefinedNotNull(parent)?parent:null;
        this.tree = UtilityFunctions.isDefinedNotNull(tree)?tree:null;
        this.children = [];
        if (UtilityFunctions.isDefinedNotNull(parent)) {
            if (this.tree == null) {
                this.tree = parent.tree;
            }
            if (this.tree != null && !this.new) {
                this.tree.addNewPageNode(this);
            }
        }
        if (UtilityFunctions.isDefinedNotNull(websitePage)) {
            this.id = websitePage.id;
            this.title = websitePage.title;
            this.type = websitePage.type;
            this.visible = websitePage.visible;
            this.isSection = websitePage.isSection;
            this.richText = websitePage.richText;
            this.url = websitePage.url;
            this.resourceNode = websitePage.resourceNode;
            this.resourceNodeType = websitePage.resourceNodeType;
            this.new = false;
            if (websitePage.children.length>0) this.createChildrenPages(websitePage.children);
        }
	};

    WebsitePageNode.prototype.getTrustedUrl = function() {
      return $sce.trustAsResourceUrl(this.url);
    };

    WebsitePageNode.prototype.generateResourceUrl = function() {
        if (this.resourceNode!=null && this.resourceNodeType!=null) {
            var url = Routing.generate('claro_resource_open', {
                resourceType: this.resourceNodeType,
                node: this.resourceNode
            });
            return $sce.trustAsResourceUrl(url);
        }
        return null;
    };

    WebsitePageNode.prototype.trustedContent = function() {
        return $sce.trustAsHtml(this.richText);
    };

    WebsitePageNode.prototype.baseUrl = Routing.generate('icap_website_view', {websiteId: window.websiteId})+"/page";

    WebsitePageNode.prototype.appendChildPage = function(websitePage) {
		var newChildNode = new WebsitePageNode(websitePage, this);
		this.insertNode(newChildNode, -1);
		
		return newChildNode;
	};
    /*WebsitePageNode.prototype.appendEmptyPage = function () {
		var newChild = this.appendChildPage();
		this.tree.currentPageNode = newChild;
	};
	WebsitePageNode.prototype.prependChildPageNode = function (websitePage) {
		var newChild = new WebsitePageNode(websitePage);
		this.insertNode(newChild, 0);
	};*/
	WebsitePageNode.prototype.createChildrenPages = function(children) {
		if (children.length>0) {
			for (var i = 0; i < children.length; i++) {
				var childPage = children[i];
				this.appendChildPage(childPage);
			}
		}
	};
	WebsitePageNode.prototype.appendNode = function(node) {
		this.insertNode(node, -1);
	};
	WebsitePageNode.prototype.insertNode = function(node, index) {
        node.parent = this;
		if (index==-1) {
            this.children.push(node);
        }
		else {
            this.children.splice(index, 0, node);
        }
	};
    WebsitePageNode.prototype.removeChildPageNode = function(node, index) {
        if (!angular.isDefined(index)) {
            index = -1;
            for (var i=0; i<this.children.length; i++) {
                var childNode = this.children[i];
                if (childNode.id == node.id) {
                    index = i;
                    break;
                }
            }
        }
        this.children.splice(index, 1);
        var childrenLength = this.children.length;

        if (childrenLength>index) {
            return this.children[index];
        } else if (childrenLength>0) {
            return this.children[index-1];
        } else {
            return this;
        }
    };
    WebsitePageNode.prototype.save = function() {
        if (this.new) {
            return this.create();
        } else {
            return this.update();
        }
    };
    WebsitePageNode.prototype.create = function() {
        var pageNode = this;
        return $http.post(this.baseUrl+"/"+this.parent.id, this.jsonSerialize())
            .then(function(response) {
                if(typeof response.data === 'object'){
                    pageNode.id = response.data.id;
                    pageNode.parent.appendNode(pageNode);
                    pageNode.tree.addNewPageNode(pageNode);
                    pageNode.new = false;
                    pageNode.tree.changeCurrentPageNode(pageNode);

                    return pageNode;
                } else {
                    return $q.reject(response.data);
                }
            }, function(response) {
                return $q.reject(response.data);
            });
    };
    WebsitePageNode.prototype.update = function() {
        var pageNode = this;
        return $http.put(this.baseUrl+"/"+this.id, this.jsonSerialize())
            .then(function(response) {
                if(typeof response.data === 'object'){
                    return pageNode;
                } else {
                    return $q.reject(response.data);
                }
            }, function(response) {
                return $q.reject(response.data);
            });
    };
    WebsitePageNode.prototype.delete = function() {
        var pageNode = this;
        return $http.delete(this.baseUrl+"/"+this.id, this.jsonSerialize())
            .then(function(response) {
                if(typeof response.data === 'object'){
                    pageNode.tree.removePageNode(this);
                    var newCurrentNode = pageNode.parent.removeChildPageNode(pageNode);
                    newCurrentNode.tree.changeCurrentPageNode(newCurrentNode);

                    return pageNode;
                } else {
                    return $q.reject(response.data);
                }
            }, function(response) {
                return $q.reject(response.data);
            });
    };
    WebsitePageNode.prototype.move = function(oldParent, newParent, oldIndex, newIndex) {
        var pageNode = this;
        var previousSiblingId = 0;
        if (newIndex > 0) {
            previousSiblingId = newParent.children[newIndex-1].id;
        }
        return $http.put(this.baseUrl+"/"+this.id+"/"+newParent.id+"/"+previousSiblingId, {})
            .then(function(response) {
                if(typeof response.data === 'object'){
                    oldParent.removeChildPageNode(pageNode, oldIndex);
                    newParent.insertNode(pageNode, newIndex);
                    pageNode.tree.changeCurrentPageNode(pageNode);

                    return true;
                } else {
                    return false;
                }
            }, function(response) {
                return $q.reject(response.data);
            });
    }

    WebsitePageNode.prototype.jsonSerialize = function() {
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
}]);