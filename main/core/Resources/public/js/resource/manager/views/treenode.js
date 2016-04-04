/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global Translator */
/* global ModalWindow */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};
    var views = Claroline.ResourceManager.Views;

    views.Treenode = Backbone.View.extend({
        tagName: "li",
        events: {
            "click": "nodeClicked",
            "click a.add-content": "addContent"
        },
        initialize: function (parameters, dispatcher, data) {
            this.iconClasses = {"workspace": "fa-book", "tab": "fa-list-alt", "widget": "fa-cog"};
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.loaded = false;
            this.isEmpty = data == null;
            this.isLeaf = this.isEmpty || false;
            this.wrapper = null;
            this.node = data;
            this.buildElement();
            if (!this.isEmpty && !this.isLeaf) {
                var parents = _.values(data.parents);
                parents.push(data.id);
                this.dispatcher.on(
                    this.node.acceptsType + '-list-returned-' + _.values(parents).join("-"),
                    this.nodesReturned,
                    this
                );
            }
        },
        buildElement: function () {
            this.wrapper = this.$el;
            if (!this.isEmpty) {
                this.isLeaf = _.isNull(this.node.acceptsType);
                this.node.iconClass = this.iconClasses[this.node.type];
                var parentClass = "";
                if (this.node.acceptsType) {
                    parentClass= "widget-tree-parent-node"
                }
                this.el.className = 'widget-tree-node '+this.node.type+'-node '+parentClass;
                this.el.id = this.node.type+'-node-'+this.node.id;
            }
            this.render();
        },
        nodeClicked: function (event) {
            //If node is not loaded and is not leaf, load children
            if (!this.loaded && !this.isLeaf) {
                var eventData = this.node.parents;
                eventData[this.node.type] = this.node.id;
                this.dispatcher.trigger('get-'+this.node.acceptsType+'-list', eventData);
            }
            //If noad is not leaf but has been loaded, then collapse/expand
            else if (this.loaded && !this.isLeaf) {
                var children = this.$el.find('>ul');
                if (children.is(":visible")) {
                    children.hide('fast');
                } else {
                    children.show('fast');
                }
            }
            event.stopPropagation();
        },
        addContent: function (event) {
            if (this.isLeaf) {
                this.parameters.pickerCallback([this.node], null, true);
                this.dispatcher.trigger('close-picker-' + this.parameters.viewName);
            }
            event.stopPropagation();
        },
        nodesReturned: function (data) {
            this.subViews = {
                treelist: new views.Treelist(this.parameters, this.dispatcher, data)
            };

            this.wrapper.append(this.subViews.treelist.el);
            this.loaded = true;
        },
        render: function () {
            var twigParams = {isEmpty: this.isEmpty};
            if (!this.isEmpty) {
                twigParams = {
                    isEmpty: this.isEmpty,
                    name: this.node.name,
                    iconClass: this.node.iconClass,
                    isLeaf: this.isLeaf
                };
            }
            this.$el.html(Twig.render(ResourceManagerTreeNode, twigParams));
        }
    });
})();
