
 class DashboardCtrl {
  /**
   * Constructor.
   * @param {object}           user
   * @param {object}           dashboards
   */
   constructor(Translator, WorkspaceService, DashboardService, user, dashboard, data) {
     this.WorkspaceService = WorkspaceService
     this.Translator = Translator
     this.DashboardService = DashboardService
     this.user = user
     this.dashboard = dashboard
     this.data = data
   }
}


 export default DashboardCtrl
