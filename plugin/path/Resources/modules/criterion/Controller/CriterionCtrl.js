/**
 * Criterion controller
 */
export default class CriterionCtrl {
  /**
   * @param {Translator} Translator
   * @param {ConfirmService} ConfirmService
   * @param {CriterionService} CriterionService
   */
  constructor(Translator, ConfirmService, CriterionService) {
    this.Translator = Translator
    this.ConfirmService = ConfirmService
    this.CriterionService = CriterionService

    this.platformGroups = []
    this.CriterionService.getType('usergroup').getPlatformGroups().then((result) => {
      this.platformGroups = result
    })

    this.workspaceTeams = []
    this.CriterionService.getType('userteam').getWorkspaceTeams().then((result) => {
      this.workspaceTeams = result
    })

    this.activityStatuses = []
    this.CriterionService.getType('activitystatus').getStatuses().then((result) => {
      this.activityStatuses = result
    })
  }

  removeCriterion() {
    this.ConfirmService.open({
      title:         this.Translator.trans('criterion_delete_title',   {}, 'path_wizards'),
      message:       this.Translator.trans('criterion_delete_confirm', {}, 'path_wizards'),
      confirmButton: this.Translator.trans('criterion_delete',         {}, 'path_wizards')
    },
      // Confirm success callback
      () => {
        this.CriterionService.removeCriterion(this.step, this.criterion)
      })
  }
}