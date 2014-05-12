'use strict';

portfolioApp
    .factory("widgetsManager", ["$http", "widgetsConfig", function($http, widgetsConfig){
        return {
            widgets: [],
            forms:   [],
            editing: [],
            init: function(widgets) {
                angular.forEach(widgetsConfig.getTypes(), function(type) {
                    this.widgets[type] = widgets[type] ? [widgets[type]] : [];
                }, this);
            },
            edit: function(widget) {
                this.addEditing(widget);

                if (!widget.isEditing()) {
                    widget.setEditMode(true);
                    this.loadForm(widget);
                    widget
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
                if (!widget.isEditing()) {
                    this.editing.push(widget);
                }
            },
            cancelEditing: function(widget) {
                widget.setEditMode(false);

                var widgetIndex = this.editing.remove(widget);
            },
            save: function(widget) {
                var $this = this;
                var success = function() {
                    $this.cancelEditing(widget);
                };
                var failed = function(error) {
                    console.error('Error occured while saving widget');
                    console.log(error);
                }
                widget.$save(success, failed);
            }
        };
    }]);