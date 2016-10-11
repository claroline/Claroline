
 class AdminDashboardCtrl {
  /**
   * Constructor.
   * This controller is used for to cases :
   * - While creating a new dashboard
   * - While updating an existing one
   * @param {object}  $location
   * @param {object}  DashboardService
   * @param {object}  workspaces
   * @param {object}  user
   * @param {object}  nbDashboards
   * @param {object}  dashboard
   * @param {object}  workspaces
   */
   constructor($location, Translator, DashboardService, workspaces, user, nbDashboards, dashboard) {
     this.$location = $location
     this.Translator = Translator
     this.DashboardService = DashboardService
     this.workspaces = workspaces
     this.user = user
     this.dashboard = dashboard
     this.isEditMode = this.dashboard.id !== undefined ? true:false
     // add infos to default dashboard if we are creating a new one
     if(!this.isEditMode){
       this.dashboard.name += '-' + Translator.trans('view', {}, 'dashboard') + (nbDashboards + 1)
       this.dashboard.creatorId = this.user.id
     }
     // data computed for the view configuration...
     // used when dashboard configuration is finished and we want to preview the computed data before saving the dashboard
     this.computed = {}
     this.currentStep = 1
     this.nbSteps = 1
     this.isLastStep = this.currentStep > this.nbSteps
     this.isPreview = false
   }

   setStep(number){
     this.currentStep = number
     this.isLastStep = this.currentStep > this.nbSteps
     if(this.isLastStep){
       this.getDashboardDataForPreview(this.dashboard.workspace.id)
     } else {
       this.isPreview = false
     }
   }

   getDashboardDataForPreview(id){
     let promise = this.DashboardService.getDashboardDataByWorkspace(id)
     promise.then(function(result){
       this.computed = result
       this.isPreview = true
       this.isLastStep = false
     }.bind(this))
   }

   saveDashboard(){
     if(this.dashboard.id){
       let promise = this.DashboardService.update(this.dashboard)
       promise.then(function(){
         // redirect to list
         this.$location.path('/')
       }.bind(this))
     } else {
       let promise = this.DashboardService.create(this.dashboard)
       promise.then(function(){
         // redirect to list
         this.$location.path('/')
       }.bind(this))
     }
   }
}


 export default AdminDashboardCtrl
