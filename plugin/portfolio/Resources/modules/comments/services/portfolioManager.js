/* global Routing */

import angular from 'angular/index'

export default function ($http, $q, portfolioFactory){
  return {
    portfolios: [],
    getPortfolios: function () {
      var deferred = $q.defer()
      $http.get(Routing.generate('icap_portfolio_internal_portfolio'))
    .success(function (data) {
      var portfolios = []
      angular.forEach(data, function (rawPortfolio) {
        portfolios.push(new portfolioFactory(rawPortfolio))
      })
      portfolios.$resolved = true
      deferred.resolve(portfolios)
    }).error(function (msg) {
      deferred.reject(msg)
    })
      return deferred.promise
    },
    save: function (portfolio) {
      var success = function () {
      }
      var failed = function () {
      }

      return portfolio.$update(success, failed)
    },
    updateViewCommentsDate: function (portfolio) {
      portfolio.commentsViewAt = new Date()
      this.save(portfolio)
    }
  }
}
