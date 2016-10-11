
 class StepOneCtrl {
  /**
   * Constructor.
   *
   * @param {object}  Translator
   * @param {object}  DashboardService
   */
   constructor(Translator, DashboardService) {
     this.Translator = Translator
     this.DashboardService = DashboardService
     this.filtered = []
     this.filters = [
       {
         value: 'MY',
         name:  Translator.trans('workspaces_filter_my', {}, 'dashboard')
       },
       {
         value: 'FOLLOWING',
         name: Translator.trans('workspaces_filter_attended', {}, 'dashboard')
       }
     ]

     this.selectedFilter = ((this.dashboard.workspace !== undefined && this.dashboard.workspace.creatorId === this.user.id) || this.dashboard.workspace === undefined) ? 'MY':'FOLLOWING'
     this.filterList()
   }

   /**
   * Filter workspace list depending on filter selected (MY / FOLLOWING)
   */
   filterList(){
     // 'MY' Workspaces means workspaces where I am the creator or Manager
     if(this.selectedFilter === 'MY'){
       // the user will be able to see the stats for all user belonging to this workspace
       this.filtered = this.workspaces.filter(el => el.creatorId === this.user.id)
     } else {
       // the user will be able to see it's own stats only
       this.filtered = this.workspaces.filter(el => el.creatorId !== this.user.id)
     }
   }

   selectedWorkspaceChanged(workspace){
     this.dashboard.workspace = workspace
   }
}

 export default StepOneCtrl
