/**
 * StepConditions Service
 */

export default class StepConditionsService {
  /**
   *
   * @param $q
   * @param {CriteriaGroupService} CriteriaGroupService
   */
  constructor($q, CriteriaGroupService) {
    this.$q = $q
    this.CriteriaGroupService = CriteriaGroupService
  }

  /**
   * Generates a new empty conditions
   *
   * @param {object} step
   * @returns {object}
   */
  initialize(step) {
    const newCriteriaGroup = this.CriteriaGroupService.newGroup()

    const newCondition = {
      // ID of the StepCondition entity
      scid: null,
      // list of criteria groups
      criteriagroups: [newCriteriaGroup]
    }

    step.condition = newCondition

    return newCondition
  }

  /**
   * Test the condition of a step
   *
   * @param step
   */
  testCondition(step) {
    const deferred = this.$q.defer()

    let errorList = []
    const groups = []

    if (step.condition) {
      for (let i = 0; i < step.condition.criteriagroups.length; i++) {
        groups.push(
          this.CriteriaGroupService
            .testGroup(step, step.condition.criteriagroups[i])
            .then(errors => {
              errorList = errorList.concat(errors)
            })
        )
      }

      // Wait all criteria groups are resolved before sending the error list
      this.$q
        .all(groups)
        .then(() => {
          deferred.resolve(errorList)
        })
    } else {
      deferred.resolve(errorList)
    }

    return deferred.promise
  }
}
