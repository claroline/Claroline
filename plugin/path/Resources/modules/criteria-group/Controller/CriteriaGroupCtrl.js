
export default class CriteriaGroupCtrl {
  /**
   * @param {ConfirmService} ConfirmService
   * @param {CriteriaGroupService} CriteriaGroupService
   * @param {CriterionService} CriterionService
   */
  constructor(Translator, ConfirmService, CriteriaGroupService, CriterionService) {
    this.Translator = Translator
    this.ConfirmService = ConfirmService
    this.CriteriaGroupService = CriteriaGroupService
    this.CriterionService = CriterionService
  }

  addGroup() {
    this.CriteriaGroupService.newGroup(this.criteriaGroup)
  }

  removeGroup() {
    this.ConfirmService.open({
      title:         this.Translator.trans('criteriagroup_delete_title',   {}, 'path_wizards'),
      message:       this.Translator.trans('criteriagroup_delete_confirm', {}, 'path_wizards'),
      confirmButton: this.Translator.trans('criteriagroup_delete',         {}, 'path_wizards')
    },
      // Confirm success callback
      () => {
        this.CriteriaGroupService.removeGroup(this.step, this.criteriaGroup)
      })
  }

  addCriterion() {
    this.CriterionService.newCriterion(this.criteriaGroup)
  }
}
