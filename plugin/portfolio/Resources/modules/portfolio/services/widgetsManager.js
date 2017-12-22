import angular from 'angular/index'

export default function ($http, widgetsConfig, widgetFactory, $q, urlInterpolator){
  return {
    portfolioWidgets: [],
    init: function (portfolioWidgets) {
      angular.forEach(portfolioWidgets, function (rawWidget) {
        var widget = widgetFactory.getWidget(rawWidget.portfolio_id, rawWidget.widget_type, rawWidget.widget_id)
        var newPortfolioWidget= new widget(rawWidget)
        newPortfolioWidget.isNew = false
        this.portfolioWidgets.push(newPortfolioWidget)
      }, this)
    },
    addWidgets: function (portfolioWidgets) {
      for (var i = 0; i < portfolioWidgets.length; i++) {
        this.addWidget(portfolioWidgets[i])
      }
    },
    addWidget: function (portfolioWidget) {
      var widget = widgetFactory.getWidget(portfolioWidget.portfolio_id, portfolioWidget.widget_type, portfolioWidget.widget_id)
      var newPortfolioWidget= new widget(portfolioWidget)
      newPortfolioWidget.isNew = true
      this.portfolioWidgets.push(newPortfolioWidget)
      this.save(newPortfolioWidget)
    },
    delete: function (portfolioWidget) {
      portfolioWidget.isDeleting = true
      var self = this
      var success = function () {
        self.portfolioWidgets.remove(portfolioWidget)
      }
      var failed = function () {
      }
      portfolioWidget.$delete(success, failed).then(function () {
        portfolioWidget.isDeleting = false
      })
    },
    getAvailableWidgetsByTpe: function (portfolioId, type) {
      var url = urlInterpolator.interpolate('/{{portfolioId}}/{{type}}', {portfolioId: portfolioId, type: type})

      var deferred = $q.defer()
      $http.get(url)
            .success(function (data) {
              deferred.resolve(data)
            }).error(function (msg) {
              deferred.reject(msg)
            })
      return deferred.promise
    },
    save: function (widget) {
      if (null == widget.row) {
        widget.row = 0
      }
      if (null == widget.col) {
        widget.col = 0
      }

      widget.isUpdating = true

      var $this = this
      var success = function () {
        widget.isNew = false
        $this.cancelEditing(widget)
        widget.isUpdating = false
      }
      var failed = function () {
      }

      if (widget.isNew) {
        delete widget.id
      }
      return widget.isNew ? widget.$save(success, failed) : widget.$update(success, failed)
    },
    cancelEditing: function (widget) {
      widget.isEditing = false

      if (widget.isNew) {
        this.portfolioWidgets.remove(widget)
      }
    },
    edit: function (widget) {
      widget.isEditing = true
    }
  }
}
