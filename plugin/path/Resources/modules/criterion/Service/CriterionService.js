
export default class CriterionService {
  constructor(ActivityAttemptCriterion, ActivityStatusCriterion, GroupCriterion, TeamCriterion) {
    /**
     * Available types of criterion
     * @type {BaseCriterion[]}
     */
    this.criterionTypes = {
      activityrepetition: ActivityAttemptCriterion,
      activitystatus: ActivityStatusCriterion,
      usergroup: GroupCriterion,
      userteam: TeamCriterion
    }
  }

  getTypes() {
    return this.criterionTypes
  }

  getType(type) {
    if (this.criterionTypes[type]) {
      return this.criterionTypes[type]
    }

    return null
  }

  newCriterion(parentGroup) {
    const newCriterion = {
      // id of the criterion in entity
      critid: null,
      // type : activity status, user group, primary resource repeat
      type: null,
      data: null
    }

    if (parentGroup) {
      parentGroup.criterion.push(newCriterion)
    }

    return newCriterion
  }

  testCriterion(step, criterion) {
    return this.criterionTypes[criterion.type].test(step, criterion.data)
  }

  /**
   * Remove a criterion from a step.
   *
   * @param step
   * @param criterionToDelete
   */
  removeCriterion(step, criterionToDelete) {
    this.browseCriteria(step.condition.criteriagroups, function (criteriaGroup, criterion) {
      let deleted = false

      // if current group is the one to be deleted
      if (criterion === criterionToDelete) {
        const pos = criteriaGroup.criterion.indexOf(criterionToDelete)
        if (-1 !== pos) {
          criteriaGroup.criterion.splice(pos, 1)

          deleted = true
        }
      }

      return deleted
    })
  }

  /**
   * Loop over a list of criteria and execute callback
   * Iteration stops when callback returns true
   *
   * @param {array}    criteriaGroups    - an array of criteria groups to browse
   * @param {function} callback - a callback to execute on each criterion (called with args `parentCriterion`, `currentCriterion`)
   */
  browseCriteria(criteriaGroups, callback) {
    /**
     * Recursively loop through the criteria groups to execute callback on each criteria group
     *
     * @param   {object} criteriaGroup
     * @returns {boolean}
     */
    function recursiveLoop(criteriaGroup) {
      let terminated = false

      // Execute callback on current criterion
      if (typeof callback === 'function') {
        for (let i = 0; i < criteriaGroup.criterion.length; i++) {
          terminated = callback(criteriaGroup, criteriaGroup.criterion[i])
          if (terminated) {
            break
          }
        }
      }

      if (!terminated && typeof criteriaGroup.criteriagroup !== 'undefined' && criteriaGroup.criteriagroup.length !== 0) {
        for (let i = 0; i < criteriaGroup.criteriagroup.length; i++) {
          terminated = recursiveLoop(criteriaGroup.criteriagroup[i])
        }
      }

      return terminated
    }

    if (typeof criteriaGroups !== 'undefined' && criteriaGroups.length !== 0) {
      for (let j = 0; j < criteriaGroups.length; j++) {
        const terminated = recursiveLoop(criteriaGroups[j])
        if (terminated) {
          break
        }
      }
    }
  }
}
