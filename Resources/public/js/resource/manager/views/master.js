/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global Twig */
/* global ModalWindow */

(function () {
    'use strict';

    window.Claroline = window.Claroline || {};
    window.Claroline.ResourceManager = window.Claroline.ResourceManager || {};
    window.Claroline.ResourceManager.Views = window.Claroline.ResourceManager.Views || {};
    var views = Claroline.ResourceManager.Views;

    views.Master = Backbone.View.extend({
        initialize: function (parameters, dispatcher) {
            this.parameters = parameters;
            this.dispatcher = dispatcher;
            this.isAppended = false;

            this.currentDirectory = {id: parameters.directoryId};

            this.build();
            this.dispatcher.on('directory-data-' + parameters.viewName, _.bind(this.render, this));
            this.dispatcher.on('open-picker-' + parameters.viewName,    _.bind(this.open, this));
        },
        build: function () {
            this.el.className = 'main resource-manager';
            this.wrapper = this.$el;
            this.subViews = {
                breadcrumbs: new views.Breadcrumbs(this.parameters, this.dispatcher),
                actions: new views.Actions(this.parameters, this.dispatcher),
                nodes: new views.Nodes(this.parameters, this.dispatcher)
            };

            if (this.parameters.isPickerMode) {
                this.el.className = 'picker resource-manager';
                this.el.id = 'picker-' + this.parameters.viewName;
                this.$el.html(Twig.render(ModalWindow, {
                    'modalId': 'picker-' + this.parameters.viewName,
                    'header' : 'Resource Picker',
                    'body': ''
                }));
                this.wrapper = this.$el.find('.modal-body');
            }
        },
        open: function () {
            this.dispatcher.trigger('open-directory', {
                directoryId: this.parameters.directoryId,
                view: this.parameters.viewName
            });
        },
        render: function (event) {
            var nodes = event.data.nodes;
            var path = event.data.path.length > 0 ? event.data.path : ['0']
            var creatableTypes = event.data.creatableTypes;
            var isSearchMode = false;
            var searchParameters = [];

//            this.currentDirectory = _.last(path);
//
//            // if directoryHistory is empty
//            if (this.parameters.directoryHistory.length === 0) {
//                this.parameters.directoryHistory = path;
//            } else {
//                var index = -1;
//
//                for (var i = 0; i < this.parameters.directoryHistory.length; i++) {
//                    if (this.parameters.directoryHistory[i].id === this.currentDirectory.id) {
//                        index = i;
//                    }
//                }
//
//                var directoriesToAdd = path.length - this.parameters.directoryHistory.length;
//                // compare path & directoryHistory
//                if (directoriesToAdd > 1) {
//                    // if path > directoryHistory, it mush come from the search
//                    //add the missing directories to the breadcrumbs
//                    var pathLength = path.length;
//                    var missingDirectories = directoriesToAdd;
//
//                    while (missingDirectories > 0) {
//                        this.parameters.directoryHistory.push(path[pathLength - missingDirectories]);
//                        missingDirectories--;
//                    }
//
//                } else {
//                    if (index === -1) {
//                        //if the directory isn't in the breadcrumbs yet'
//                        this.parameters.directoryHistory.push(this.currentDirectory);
//                    } else {
//                        this.parameters.directoryHistory.splice(index + 1);
//                    }
//                }
//            }

            if (!this.isAppended) {
                this.parameters.parentElement.append(this.$el);
                this.wrapper.append(
                    this.subViews.breadcrumbs.el,
                    this.subViews.actions.el,
                    this.subViews.nodes.el
                );
                this.isAppended = true;
            }

            this.subViews.breadcrumbs.render(this.parameters.directoryHistory);
            this.subViews.actions.render(this.currentDirectory, creatableTypes, isSearchMode, searchParameters);
            this.subViews.nodes.render(
                nodes,
                isSearchMode,
                this.currentDirectory.id,
                this.directoryHistory
            );

            if (this.parameters.isPickerMode) {
                this.$el.find('.modal').modal('show');
            }

//            if (!this.subViews.areAppended) {
//                this.wrapper.append(
//                    this.subViews.breadcrumbs.el,
//                    this.subViews.actions.el,
//                    this.subViews.nodes.el
//                );
//                this.subViews.areAppended = true;
//            }


//            if (!this.isAppended) {
//                this.parameters.parentElement.append(this.el);
//                this.isAppended = true;
//            }

//            if (this.parameters.isPickerMode) {
//                alert('Opened picker ' + this.parameters.viewName);
//
//                console.log('#' + this.el.id)
//                this.wrapper.modal('show');
//            }
        }
    });
})();
