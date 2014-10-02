'use strict';

portfolioApp
    .factory("widgetsManager", ["$http", "widgetsConfig", "widgetFactory", function($http, widgetsConfig, widgetFactory){
        return {
            widgets: [],
            emptyWidgets: [],
            forms:   [],
            editing: [],
            evaluating: [],
            init: function(portfolioId, widgets) {
                angular.forEach(widgets, function(rawWidget) {
                    var widget = widgetFactory.getWidget(portfolioId, rawWidget.type);
                    this.widgets.push(new widget(rawWidget).setNewMode(false));
                }, this);
            },
            edit: function(widget) {
                widget.copy = angular.copy(widget);
                if (!widget.isEditing()) {
                    this.loadForm(widget);
                    this.addEditing(widget);
                }
            },
            evaluate: function(widget) {
                if (!widget.isEvaluating()) {
                    this.addEvaluating(widget);
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
                widget.setEditMode(true);
                if (!this.editing.inArray(widget)) {
                    this.editing.push(widget);
                }
            },
            addEvaluating: function(widget) {
                widget.setEvaluateMode(true);
                if (!this.evaluating.inArray(widget)) {
                    this.evaluating.push(widget);
                }
            },
            cancelEditing: function(widget, rollback) {
                if (rollback) {
                    angular.copy(widget.copy, widget);
                }

                widget.setEditMode(false);

                this.editing.remove(widget);
                if (widget.isNew()) {
                    this.widgets.remove(widget);
                }
            },
            cancelEvaluating: function(widget) {
                widget.setEvaluateMode(false);

                this.evaluating.remove(widget);
            },
            save: function(widget) {
                delete widget.copy;

                widget.deleteChildren();

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
                var isTypeUnique = widgetsConfig.config[type].isUnique;
                if (isTypeUnique && 0 < this.findWidgetsByType(type).length) {
                    this.edit(this.findWidgetsByType(type)[0]);
                }
                else {
                    this.createEmptyWidget(portfolioId, type, isTypeUnique);
                }
            },
            findWidgetsByType: function(type) {
                var widgets = [];

                for (var index = 0; index < this.widgets.length; index++) {
                    if (type == this.widgets[index].type) {
                        widgets.push(this.widgets[index]);
                    }
                }

                return widgets;
            },
            createEmptyWidget: function(portfolioId, type, istypeUnique) {
                var newWidget;
                var widget = widgetFactory.getWidget(portfolioId, type);

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
                    this.addEditing(newWidget);
                }

                this.widgets.push(newWidget);
            },
            isDeletable: function(widget) {
                return widgetsConfig.isDeletable(widget.getType());
            },
            delete: function(widget) {
                if (this.isDeletable(widget)) {
                    widget.isDeleting = true;
                    var $this = this;
                    var success = function() {
                        $this.widgets.remove(widget);
                    };
                    var failed = function(error) {
                        console.error('Error occured while deleting widget');
                        console.log(error);
                    }
                    widget.$delete(success, failed).then(function() {
                        delete widget.isDeleting;
                    });
                }
            }
        };
    }]);