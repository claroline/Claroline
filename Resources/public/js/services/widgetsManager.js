'use strict';

portfolioApp
    .factory("widgetsManager", ["$http", "widgetsConfig", "widgetFactory", function($http, widgetsConfig, widgetFactory){
        return {
            widgets: [],
            emptyWidgets: [],
            forms:   [],
            editing: [],
            init: function(widgets) {
                angular.forEach(widgetsConfig.getTypes(), function(type) {
                    this.widgets[type] = widgets[type] ? widgets[type] : [];
                }, this);
            },
            edit: function(widget) {
                if (!widget.isEditing()) {
                    this.addEditing(widget);
                    widget.setEditMode(true);
                    this.loadForm(widget);
                }
            },
            loadForm: function(widget) {
                if (this.forms[widget.getType()]) {
                    widget.setFormView(this.forms[widget.getType()]);
                    return true;
                }
                var $this = this;

                return $http.get(widget.getFormUrl()).success(function(formViewData) {
                    widget.setFormView(formViewData.form);
                    $this.forms[widget.getType()] = formViewData.form;
                });
            },
            addEditing: function(widget) {
                this.editing.push(widget);
            },
            cancelEditing: function(widget) {
                widget.setEditMode(false);

                this.editing.remove(widget);

                if (widget.isNew()) {
                    this.widgets[widget.getType()].remove(widget);
                }
            },
            save: function(widget) {
                var $this = this;
                var success = function() {
                    widget.setNewMode(false);
                    $this.cancelEditing(widget);
                };
                var failed = function(error) {
                    console.error('Error occured while saving widget');
                    console.log(error);
                }

                return widget.isNew() ? widget.$save(success, failed) : widget.$update(success, failed);
            },
            create: function(portfolioId, type) {
                var istypeUnique = widgetsConfig.config[type].isUnique;

                if (istypeUnique && 0 < this.widgets[type].length) {
                    this.edit(this.widgets[type][0]);
                }
                else {
                    var widget = this.createEmptyWidget(portfolioId, type);
                    if (istypeUnique) {
                        this.widgets[type] = [widget];
                    } else {
                        this.widgets[type].push(widget);
                    }
                }
            },
            createEmptyWidget: function(portfolioId, type) {
                var newWidget;
                var widget = widgetFactory.getResource(portfolioId, type);

                if (this.emptyWidgets[type]) {
                    newWidget = new widget(angular.copy(this.emptyWidgets[type]));
                    this.edit(newWidget);
                }
                else {
                    newWidget = widget.create();
                    var $this = this;
                    newWidget.$promise.then(function() {
                        $this.emptyWidgets[type] = angular.copy(newWidget);
                        $this.edit(newWidget);
                    });
                }

                return newWidget;
            },
            isDeletable: function(widget) {
                return widgetsConfig.isDeletable(widget.getType());
            },
            delete: function(widget) {
                if (this.isDeletable(widget)) {
                    var $this = this;
                    var success = function() {
                        $this.widgets[widget.getType()].remove(widget);
                    };
                    var failed = function(error) {
                        console.error('Error occured while deleting widget');
                        console.log(error);
                    }
                    return widget.$delete(success, failed);
                }
            }
        };
    }]);