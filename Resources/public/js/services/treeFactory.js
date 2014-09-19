var treeApp = angular.module('treeApp', []);

treeApp.factory('Tree', ['$filter', 'TreeNode', function($filter, TreeNode){
	function Tree (data, label, useFirstItemAsRoot) {
		this.label = label;
		this.root = null;
		this.allNodes = [];
		if (useFirstItemAsRoot) {
			this.root = new TreeNode(data[0].id, data[0].label, null, data[0].children, data[0].attributes, this);
		}  
		else {
			this.root = new TreeNode(0, label, null, data, {}, this);
		}
		this.currentNode = this.root;
	};
	Tree.prototype.getNodeById = function (id) {
		return this.allNodes[id];
	};
	Tree.prototype.getRootNode = function () {
		return this.root;
	};
	Tree.prototype.addNewNode = function (node) {
		this.allNodes[node.id] = node;
	};
	Tree.prototype.removeNode = function (node) {
		if(!this.allNodes.hasOwnProperty(node.id))
		{
			return;
		}
		delete this.allNodes[node.id];
		console.log(this.allNodes);
		
		return node;
	};
	Tree.prototype.changeCurrentNode = function (newId) {
		this.currentNode = this.getNodeById(newId);
	};
	Tree.prototype.appendChildToCurrentNode = function (id, label, children, attributes) {
		this.currentNode.appendChild(id, label, children, attributes);
	};
	Tree.prototype.destroyCurrentNode = function () {
		this.currentNode.destroy();
	};
	
	return Tree;
}]);

treeApp.factory('TreeNode', ['UtilityFunctions', function(UtilityFunctions){
	function TreeNode (id, label, parent, children, attributes, tree) {
		this.id = UtilityFunctions.isDefinedNotNull(id)?id:Math.rand();
		this.label = UtilityFunctions.isDefinedNotNull(label)?label:"Child";
		this.parent = UtilityFunctions.isDefinedNotNull(parent)?parent:null;
		this.tree = UtilityFunctions.isDefinedNotNull(tree)?tree:parent.tree;
		this.tree.addNewNode(this);
		this.attributes = UtilityFunctions.isDefinedNotNull(attributes)?attributes:{};
		this.children = [];
		if (UtilityFunctions.isDefinedNotNull(children) && children.length>0) this.createChildren(children);
	};	
	TreeNode.prototype.appendChild = function (id, label, children, attributes) {
		var newChild = new TreeNode(id, label, this, children, attributes);
		this.insertNode(newChild, -1);
		
		return newChild;
	};
	TreeNode.prototype.appendEmptyChild = function () {
		var newChild = this.appendChild((new Date()).getTime(), 'New node '+(this.children.length+1), this, [], {});
		this.tree.currentNode = newChild;
	};
	TreeNode.prototype.prependChild = function (id, label, children, attributes) {
		var newChild = new TreeNode(id, label, this, children, attributes);
		this.insertNode(newChild, 0);
	};
	TreeNode.prototype.createChildren = function (children) {
		if (children.length>0) {
			for (var i = 0; i < children.length; i++) {
				var child = children[i];
				this.appendChild(child.id, child.label, child.children, child.attributes);
			}
		}
	};
	TreeNode.prototype.appendNode = function (node) {
		this.insertNode(node, -1);
	};
	TreeNode.prototype.insertNode = function (node, index) {
		if (index==-1) this.children.push(node);
		else this.children.splice(index, 0, node);
	};
	
	return TreeNode;
}]);