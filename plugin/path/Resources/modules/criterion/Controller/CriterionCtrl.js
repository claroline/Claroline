/**
 * Criterion controller
 */
export default class CriterionCtrl {
  /**
   *
   * @param {ConfirmService} ConfirmService
   * @param {CriterionService} CriterionService
   */
  constructor(ConfirmService, CriterionService) {
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
        title:         Translator.trans('criterion_delete_title',   {}, 'path_wizards'),
        message:       Translator.trans('criterion_delete_confirm', {}, 'path_wizards'),
        confirmButton: Translator.trans('criterion_delete',         {}, 'path_wizards')
      },
      // Confirm success callback
      () => {
        this.CriterionService.removeCriterion(this.step, this.criterion)
      })
  }
}