
export default class CriteriaGroupService {
  /**
   *
   * @param {object} $q
   * @param {CriterionService} CriterionService
   */
  constructor($q, CriterionService) {
    this.$q = $q
    this.CriterionService = CriterionService
  }

  /**
   * Create a new CriteriaGroup.
   *
   * @param {object} [parent] the parent CriteriaGroup
   * @returns {object}
   */
  newGroup(parent) {
    const newGroup = {
      // id of the criteriagroup in entity
      cgid: null,
      lvl: parent ? parent.lvl + 1 : 0,
      //contains array of criterion and/or criteria group
      criterion: [],
      criteriagroup: []
    }

    if (parent) {
      parent.criteriagroup.push(newGroup)
    }

    return newGroup
  }

  /**
   * Test Criteria Group.
   *
   * @param {object} step
   * @param {object} criteriaGroup
   */
  testGroup(step, criteriaGroup) {
    const deferred = this.$q.defer()

    let errorList = []
    const criteria = []

    // Tests criteria
    for (let i = 0; i < criteriaGroup.criterion.length; i++) {
      criteria.push(
        this.CriterionService
          .testCriterion(step, criteriaGroup.criterion[i])
          .then(errors => {
            errorList = errorList.concat(errors)
          })
      )
    }
      
    // Tests sub-groups
    for (let i = 0; i < criteriaGroup.criteriagroup.length; i++) {
      criteria.push(
        this
          .testGroup(step, criteriaGroup.criteriagroup[i])
          .then(errors => {
            errorList = errorList.concat(errors)
          })
      )
    }

    // Wait all criteria and sub-groups are resolved before sending the error list
    this.$q
      .all(criteria)
      .then(() => {
        deferred.resolve(errorList)
      })

    return deferred.promise
  }

  /**
   * Remove a criteria group from a step.
   *
   * @param {object}  step
   * @param {object} groupToDelete
   */
  removeGroup(step, groupToDelete) {
    this.browseGroups(step.condition.criteriagroups, function (parent, group) {
      let deleted = false

      // if current group is the one to be deleted
      if (group === groupToDelete) {
        if (typeof parent !== 'undefined' && null !== parent) {
          const pos = parent.criteriagroup.indexOf(groupToDelete)
          if (-1 !== pos) {
            parent.criteriagroup.splice(pos, 1)

            deleted = true
          }
        } else {
          // We are deleting the root criteria group
          const pos = step.condition.criteriagroups.indexOf(groupToDelete)
          if (-1 !== pos) {
            step.condition.criteriagroups.splice(pos, 1)

            deleted = true
          }
        }
      }

      return deleted
    })
  }

  /**
   * Loop over all criteria groups of a condition and execute callback
   * Iteration stops when callback returns true
   *
   * @param {array}    criteriaGroups    - an array of criteria groups to browse
   * @param {function} callback - a callback to execute on each criteria groups (called with args `parentGroup`, `currentGroup`)
   */
  browseGroups(criteriaGroups, callback) {
    /**
     * Recursively loop through the criteria groups to execute callback on each criteria group
     *
     * @param   {object} parentGroup
     * @param   {object} currentGroup
     * @returns {boolean}
     */
    function recursiveLoop(parentGroup, currentGroup) {
      let terminated = false

      // Execute callback on current criteria group
      if (typeof callback === 'function') {
        terminated = callback(parentGroup, currentGroup)
      }

      if (!terminated && typeof currentGroup.criteriagroup !== 'undefined' && currentGroup.criteriagroup.length !== 0) {
        for (let i = 0; i < currentGroup.criteriagroup.length; i++) {
          terminated = recursiveLoop(currentGroup, currentGroup.criteriagroup[i])
        }
      }

      return terminated
    }

    if (typeof criteriaGroups !== 'undefined' && criteriaGroups.length !== 0) {
      for (let j = 0; j < criteriaGroups.length; j++) {
        const terminated = recursiveLoop(null, criteriaGroups[j])

        if (terminated) {
          break
        }
      }
    }
  }
}
