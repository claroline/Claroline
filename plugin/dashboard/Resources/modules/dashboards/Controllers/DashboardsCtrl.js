
 class DashboardsCtrl {
  /**
   * Constructor.
   * @param {object}           user
   * @param {object}           dashboards
   */
   constructor(user, dashboards, DashboardService) {
     this.user = user
     this.dashboards = dashboards
     this.DashboardService = DashboardService
   }

   delete(id){
     let promise = this.DashboardService.delete(id)
     promise.then(function(){
       // remove from local collection
       const deleted = this.dashboards.find(el => el.id === id)
       this.dashboards.splice(this.dashboards.indexOf(deleted), 1)
     }.bind(this))
   }
}


 export default DashboardsCtrl
