

/**
 * Exercise Service
 * @param {Object} $http
 * @param {Object} $q
 * @constructor
 */
class DashboardService {

  constructor($http, $q, url){
    this.$http = $http
    this.$q    = $q
    this.UrlService = url
  }

  create(data){
    const deferred = this.$q.defer()
    this.$http
      .post(this.UrlService('create_dashboard', {}), data)
      .success(response => {
        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  update(data){
    const deferred = this.$q.defer()
    this.$http
      .put(this.UrlService('update_dashboard', {'dashboardId': data.id}), data)
      .success(response => {
        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  delete(id){
    const deferred = this.$q.defer()
    this.$http
      .delete(this.UrlService('delete_dashboard', {'dashboardId':id}))
      .success(response => {
        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  getAll(){
    const deferred = this.$q.defer()
    this.$http
      .get(this.UrlService('get_dashboards', {}))
      .success(response => {
        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  getOne(id){
    const deferred = this.$q.defer()
    this.$http
      .get(this.UrlService('get_dashboard', {'dashboardId':id}))
      .success(response => {
        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  getDashboardData(id){
    const deferred = this.$q.defer()
    this.$http
      .get(this.UrlService('get_dashboard_spent_times', {'dashboardId':id}))
      .success(response => {
        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  getDashboardDataByWorkspace(id){
    const deferred = this.$q.defer()
    this.$http
      .get(this.UrlService('get_dashboard_spent_times_by_workspace', {'workspaceId':id}))
      .success(response => {
        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })

    return deferred.promise
  }

  countDashboards(){
    const deferred = this.$q.defer()
    this.$http
      .get(this.UrlService('get_nb_dashboards', {}))
      .success(response => {
        deferred.resolve(response)
      })
      .error(() => {
        deferred.reject([])
      })
    return deferred.promise
  }
}

export default DashboardService
