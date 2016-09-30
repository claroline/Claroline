import angular from 'angular/index'

export default class UserAPIService {
  constructor($http, url) {
    this.$http = $http
    this.UrlService = url
  }

  removeFromCsv(formData) {
    return this.$http.post(
      this.UrlService('api_csv_remove_user'),
      formData,
      {
        transformRequest: angular.identity,
        headers: {
          'Content-Type': undefined
        }
      }
    )
  }

  importCsvFacets(formData) {
    return this.$http.post(
      this.UrlService('api_csv_import_facets'),
      formData,
      {
        transformRequest: angular.identity,
        headers: {
          'Content-Type': undefined
        }
      }
    )
  }
}
