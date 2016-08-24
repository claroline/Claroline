/**
 * Conditions edit controller
 */

export default class ConditionEditCtrl {
  /**
   *
   * @param {ConfirmService} ConfirmService
   * @param {StepConditionsService} StepConditionsService
   */
  constructor(ConfirmService, StepConditionsService) {
    this.ConfirmService = ConfirmService
    this.ConditionService = StepConditionsService
  }

  /**
   * Create a condition for the step
   */
  addCondition() {
    this.ConditionService.initialize(this.step)
  }

  /**
   * Remove the condition
   */
  removeCondition() {
    this.ConfirmService.open({
      title:         Translator.trans('condition_delete_title',   {}, 'path_wizards'),
      message:       Translator.trans('condition_delete_confirm', {}, 'path_wizards'),
      confirmButton: Translator.trans('condition_delete',         {}, 'path_wizards')
    },
    // Confirm success callback
    () => {
      //remove the condition (needs to be step.condition to trigger change and allow path save)
      this.step.condition = null
    })
  }
}
