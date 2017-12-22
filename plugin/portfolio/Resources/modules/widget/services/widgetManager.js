import angular from 'angular/index'

/* global Routing */

export default function ($http, $q, widgetFactory){
  return {
    widgets: [],
    emptyWidget: [],
    form: [],
    init: function () {
      var deferred = $q.defer()
      var self = this
      $http.get(Routing.generate('icap_portfolio_internal_widget'))
        .success(function (data) {
          angular.forEach(data, function (rawWidget) {
            var widget = widgetFactory.getWidget(rawWidget.type)
            var newWidget = new widget(rawWidget)
            newWidget.isNew = false
            self.widgets.push(newWidget)
          })
          deferred.resolve(self.widgets)
        }).error(function (msg) {
          deferred.reject(msg)
        })
      return deferred.promise

    },
    create: function (type) {
      var newWidget
      var widget = widgetFactory.getWidget(type)
      if (this.emptyWidget[type]) {
        newWidget = new widget(angular.copy(this.emptyWidget[type]))
        newWidget.id = new Date().getTime()
        this.edit(newWidget)
      }
      else {
        newWidget = widget.create()
        var self = this
        newWidget.$promise.then(function () {
          self.emptyWidget[type] = angular.copy(newWidget)
          self.edit(newWidget)
        })
      }

      newWidget.isEditing = true

      this.widgets.push(newWidget)
    },
    edit: function (widget) {
      widget.copy = angular.copy(widget)
      if (!widget.isEditing) {
        widget.isEditing = true
        widget.isCollapsed = false
        this.loadForm(widget)
      }
    },
    loadForm: function (widget) {
      if (this.form[widget.type]) {
        widget.setFormView(this.form[widget.type])
        return true
      }
      var self = this

      return $http.get(widget.getFormUrl()).success(function (formViewData) {
        widget.setFormView(formViewData.form)
        self.form[widget.type] = formViewData.form
      })
    },
    cancelEditing: function (widget, rollback) {
      if (rollback) {
        angular.copy(widget.copy, widget)
      }
      widget.isEditing = false

      if (widget.isNew) {
        this.widgets.remove(widget)
      }
    },
    delete: function (widget) {
      widget.isDeleting = true
      var self = this
      var success = function () {
        self.widgets.remove(widget)
      }
      var failed = function () {
      }
      widget.$delete(success, failed).then(function () {
        widget.isDeleting = false
      })
    },
    save: function (widget) {
      widget.isUpdating = true
      delete widget.copy

      widget.deleteChildren()

      var self = this
      var success = function () {
        widget.isNew = false
        self.cancelEditing(widget)
        widget.isUpdating = false
      }
      var failed = function () {
      }

      if (widget.isNew) {
        delete widget.id
      }

      return widget.isNew ? widget.$save(success, failed) : widget.$update(success, failed)
    }
  }
}
