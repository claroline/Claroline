'use strict';

portfolioApp
    .factory("widgetsManager", ["widgetsConfig", function(widgetsConfig){
        return {
            widgets: {},
            forms: [],
            init: function(widgets) {
                this.initWidgets(widgets);
            },
            initWidgets: function(widgets) {
                angular.forEach(widgetsConfig.getTypes(), function(type) {
                    this.widgets[type] = widgets[type] ? [widgets[type]] : [];
                }, this);
            },
            edit: function(widget) {
                widget.setEditMode(true);
                this.loadForm(widget);
            },
            loadForm: function(widget) {
                if (this.forms[widget.getType()]) {
                    widget.setForm(this.forms[widget.getType()]);
                    return true;
                }

                this.forms[widget.getType()] = {};
            }
        };
    }]);