/**
 * Path Service
 */

import angular from 'angular/index'

export default class PathService {
  constructor($http, $q, $timeout, $location, Translator, url, AlertService, StepService, UserProgressionService) {
    this.$http = $http
    this.$q = $q
    this.$timeout = $timeout
    this.$location = $location
    this.Translator = Translator
    this.UrlGenerator = url
    this.AlertService = AlertService
    this.StepService = StepService
    this.UserProgressionService = UserProgressionService

    /**
     * Data of the Path
     * @type {object}
     */
    this.path = null

    /**
     * Maximum depth of a Path
     * @type {number}
     */
    this.maxDepth = 8

    /**
     * Total steps in path
     * @type {number}
     */
    this.totalSteps = 0
  }

  /**
   * Get ID of the current Path
   * @returns {Number}
   */
  getId() {
    return this.path.id
  }

  /**
   * Get current Path
   * @returns {Object}
   */
  getPath() {
    return this.path
  }

  /**
   * Set current Path
   * @param {Object} value
   */
  setPath(value) {
    this.path = value
    
    this.totalSteps = 0

    // Count the totalSteps of the Path on load
    this.browseSteps(this.path.steps, () => {
      this.totalSteps++
    })
  }

  /**
   * Get max depth of the Path
   * @returns {Number}
   */
  getMaxDepth() {
    return this.maxDepth
  }

  /**
   * Get the last Step seen by a User
   * @returns {Object|null}
   */
  getLastSeenStep() {
    let lastSeen = null
    this.browseSteps(this.path.steps, (parent, step) => {
      const progression = this.UserProgressionService.getForStep(step)
      if (progression) {
        lastSeen = step
      }
    })

    if (null === lastSeen && this.path.steps && 0 !== this.path.steps.length) {
      lastSeen = this.path.steps[0]
    }

    return lastSeen
  }

  /**
   * Get path total steps
   * @returns {number}
   */
  getTotalSteps() {
    return this.totalSteps
  }

  /**
   * Initialize a new Path structure
   */
  initialize() {
    // Create a generic root step
    const rootStep = this.StepService.newStep()

    this.path.steps.push(rootStep)
  }

  /**
   * Save modification to DB
   */
  save() {
    // Transform data to make it acceptable by Symfony
    const dataToSave = {
      innova_path: {
        name:             this.path.name,
        description:      this.path.description,
        breadcrumbs:      this.path.breadcrumbs,
        summaryDisplayed: this.path.summaryDisplayed,
        completeBlockingCondition: this.path.completeBlockingCondition,
        manualProgressionAllowed : this.path.manualProgressionAllowed,
        structure:        angular.toJson(this.path)
      }
    }

    const deferred = this.$q.defer()

    this.$http
      .put(this.UrlGenerator('innova_path_editor_wizard_save', { id: this.path.id }), dataToSave)
      .success((response) => {
        if ('ERROR_VALIDATION' === response.status) {
          // Display received error messages
          for (let i = 0; i < response.messages.length; i++) {
            this.AlertService.addAlert('error', response.messages[i])
          }

          // Reject the Promise
          deferred.reject(response)
        } else {
          // Get updated data
          angular.merge(this.path, response.data)

          // Display confirm message
          this.AlertService.addAlert('success', this.Translator.trans('path_save_success', {}, 'path_wizards'))

          // Resolve the Promise
          deferred.resolve(response)
        }
      })

      .error((response) => {
        // Display generic error for the User
        this.AlertService.addAlert('error', this.Translator.trans('path_save_error', {}, 'path_wizards'))

        // Reject the Promise
        deferred.reject(response)
      })

    return deferred.promise
  }

  /**
   * Publish path modifications
   */
  publish() {
    const deferred = this.$q.defer()

    this.$http
      .put(this.UrlGenerator('innova_path_publish_api', { id: this.path.id }))

      .success((response) => {
        if ('ERROR' === response.status) {
          // Store received errors in AlertService to display them to the User
          for (let i = 0; i < response.messages.length; i++) {
            this.AlertService.addAlert('error', response.messages[i])
          }

          // Reject the promise
          deferred.reject(response)
        } else {
          // Get updated data
          angular.merge(this.path, response.data)

          // Display confirm message
          this.AlertService.addAlert('success', this.Translator.trans('publish_success', {}, 'path_wizards'))

          // Resolve the promise
          deferred.resolve(response)
        }
      })

      .error((response) => {
        // Display generic error to the User
        this.AlertService.addAlert('error', this.Translator.trans('publish_error', {}, 'path_wizards'))

        // Reject the Promise
        deferred.reject(response)
      })

    return deferred.promise
  }

  /**
   * Display the step
   * @param step
   */
  goTo(step) {
    // Ugly as fuck, but can't make it work without timeout
    this.$timeout(() => {
      if (angular.isObject(step)) {
        this.$location.path('/' + step.id)
      } else {
        // User must be able to navigate to root step, so does not check authorization
        this.$location.path('/')
      }
    }, 1)
  }

  /**
   * Get the previous step
   * @param step
   * @returns {Object|Step}
   */
  getPrevious(step) {
    let previous = null

    // If step is the root of the tree it has no previous element
    if (angular.isDefined(step) && angular.isObject(step) && 0 !== step.lvl) {
      const parent = this.getParent(step)
      if (angular.isObject(parent) && angular.isObject(parent.children)) {
        // Get position of the current element
        const position = parent.children.indexOf(step)
        if (-1 !== position && angular.isObject(parent.children[position - 1])) {
          // Previous sibling found
          const previousSibling = parent.children[position - 1]

          // Get down to the last child of the sibling
          const lastChild = this.getLastChild(previousSibling)
          if (angular.isObject(lastChild)) {
            previous = lastChild
          } else {
            // Get the sibling
            previous = previousSibling
          }
        } else {
          // Get the parent as previous element
          previous = parent
        }
      }
    }

    return previous
  }

  /**
   * Get the last child of a step
   * @param step
   * @returns {Object|Step}
   */
  getLastChild(step) {
    let lastChild = null

    if (angular.isDefined(step) && angular.isObject(step) && angular.isObject(step.children) && angular.isObject(step.children[step.children.length - 1])) {
      // Get the element in children collection (children are ordered)
      const child = step.children[step.children.length - 1]
      if (!angular.isObject(child.children) || 0 >= child.children.length) {
        // It is the last child
        lastChild = child
      } else {
        // Go deeper to search for the last child
        lastChild = this.getLastChild(child)
      }
    }

    return lastChild
  }

  /**
   * Get the next step
   * @param step
   * @returns {Object|Step}
   */
  getNext(step) {
    let next = null

    if (angular.isDefined(step) && angular.isObject(step)) {
      if (angular.isObject(step.children) && angular.isObject(step.children[0])) {
        // Get the first child
        next = step.children[0]
      } else if (0 !== step.lvl) {
        // Get the next sibling
        next = this.getNextSibling(step)
      }
    }

    return next
  }

  /**
   * Retrieve the next sibling of an element
   * @param step
   * @returns {Object|Step}
   */
  getNextSibling(step) {
    let sibling = null

    if (0 !== step.lvl) {
      const parent = this.getParent(step)
      if (angular.isObject(parent.children)) {
        // Get position of the current element
        const position = parent.children.indexOf(step)
        if (-1 !== position && angular.isObject(parent.children[position + 1])) {
          // Next sibling found
          sibling = parent.children[position + 1]
        }
      }

      if (null === sibling) {
        // Sibling not found => try to ascend one level
        sibling = this.getNextSibling(parent)
      }
    }

    return sibling
  }

  /**
   * Get all parents of a Step (from the Root to the nearest step parent)
   * @param step
   * @param [reverse] - sort parents from the nearest parent to the Root
   */
  getParents(step, reverse) {
    let parents = []

    const parent = this.getParent(step)
    if (parent) {
      // Add parent to the list
      parents.push(parent)

      // Get other parents
      parents = parents.concat(this.getParents(parent))

      // Reorder parent array
      parents.sort((a, b) => {
        if (a.lvl < b.lvl) {
          return -1
        } else if (a.lvl > b.lvl) {
          return 1
        }

        return 0
      })

      if (reverse) {
        parents.reverse()
      }
    }

    return parents
  }

  /**
   * Get the parent of a step
   * @param step
   */
  getParent(step) {
    let parentStep = null

    this.browseSteps(this.path.steps, (parent, current) => {
      if (step.id == current.id) {
        parentStep = parent

        return true
      }

      return false
    })

    return parentStep
  }

  /**
   * Loop over all steps of path and execute callback
   * Iteration stops when callback returns true
   * @param {array}    steps    - an array of steps to browse
   * @param {function} callback - a callback to execute on each step (called with args `parentStep`, `currentStep`)
   */
  browseSteps(steps, callback) {
    /**
     * Recursively loop through the steps to execute callback on each step
     * @param   {object} parentStep
     * @param   {object} currentStep
     * @returns {boolean}
     */
    function recursiveLoop(parentStep, currentStep) {
      let terminated = false

      // Execute callback on current step
      if (typeof callback === 'function') {
        terminated = callback(parentStep, currentStep)
      }

      if (!terminated && typeof currentStep.children !== 'undefined' && currentStep.children.length !== 0) {
        for (let i = 0; i < currentStep.children.length; i++) {
          terminated = recursiveLoop(currentStep, currentStep.children[i])
        }
      }

      return terminated
    }

    if (typeof steps !== 'undefined' && steps.length !== 0) {
      for (let j = 0; j < steps.length; j++) {
        var terminated = recursiveLoop(null, steps[j])

        if (terminated) {
          break
        }
      }
    }
  }

  /**
   * Recalculate steps level in tree
   * @param {array} steps - an array of steps to reorder
   */
  reorderSteps() {
    this.browseSteps(this.path.steps, (parent, step) => {
      if (null !== parent) {
        step.lvl = parent.lvl + 1
      } else {
        step.lvl = 0
      }
    })
  }

  /**
   * Add a new child Step to the parent
   * @param {Object}  parent     - The parent step
   * @param {Boolean} displayNew - If true, the router will redirect to the created step
   */
  addStep(parent, displayNew) {
    if (parent.lvl < this.maxDepth) {
      // Create a new step
      const step = this.StepService.newStep(parent)

      if (displayNew) {
        // Open created step
        this.goTo(step)
      }
    }
  }

  /**
   * Remove a step from the path's tree
   * @param {object} stepToDelete - the step to delete
   */
  removeStep(stepToDelete, redirect) {
    this.browseSteps(this.path.steps, (parent, step) => {
      let deleted = false
      if (step === stepToDelete) {
        if (typeof parent !== 'undefined' && null !== parent) {
          const pos = parent.children.indexOf(stepToDelete)
          if (-1 !== pos) {
            parent.children.splice(pos, 1)

            deleted = true
          }
        } else {
          // We are deleting the root step
          const pos = this.path.steps.indexOf(stepToDelete)
          if (-1 !== pos) {
            this.path.steps.splice(pos, 1)

            deleted = true
          }
        }
      }

      return deleted
    })

    if (redirect) {
      if (this.path.steps[0]) {
        // Display root step
        this.goTo(this.path.steps[0])
      } else {
        // There is no longer steps into the path => hide step form
        this.goTo(null)
      }
    }
  }

  /**
   * Get the Root of the Path
   * @returns {Object}
   */
  getRoot() {
    let root = null

    if (angular.isDefined(this.path) && angular.isObject(this.path)
      && angular.isObject(this.path.steps) && angular.isObject(this.path.steps[0])) {
      root = this.path.steps[0]
    }

    return root
  }

  /**
   * Find a Step in the Path by its ID
   * @param   {number} stepId
   * @returns {object}
   */
  getStep(stepId) {
    let step = null

    if (angular.isDefined(this.path) && angular.isObject(this.path)) {
      this.browseSteps(this.path.steps, (parent, current) => {
        if (current.id == stepId) {
          step = current

          return true // Kill the search
        }

        return false
      })
    }

    return step
  }

  /**
   * Get inherited resources from `steps` of the Step
   * @param   {Array}  steps - The list of Steps in which we need to search the InheritedResources
   * @param   {Object} step  - The current Step
   * @returns {Array}
   */
  getStepInheritedResources(step) {
    function retrieveInheritedResources(stepToFind, currentStep, inheritedResources) {
      let stepFound = false

      if (stepToFind.id !== currentStep.id && typeof currentStep.children !== 'undefined' && null !== currentStep.children) {
        // Not the step we search for => search in children
        for (let i = 0; i < currentStep.children.length; i++) {
          stepFound = retrieveInheritedResources(stepToFind, currentStep.children[i], inheritedResources)
          if (stepFound) {
            if (typeof currentStep.resources !== 'undefined' && null !== currentStep.resources) {
              // Get all resources which must be sent to children
              for (let j = currentStep.resources.length - 1; j >= 0; j--) {
                if (currentStep.resources[j].propagateToChildren) {
                  // Current resource must be available for children
                  let resource = angular.copy(currentStep.resources[j])
                  resource.parentStep = {
                    id: currentStep.id,
                    lvl: currentStep.lvl,
                    name: currentStep.name
                  }
                  resource.isExcluded = stepToFind.excludedResources.indexOf(resource.id) != -1
                  inheritedResources.unshift(resource)
                }
              }
            }

            break
          }
        }
      } else {
        stepFound = true
      }

      return stepFound
    }

    let stepFound = false
    const inheritedResources = []

    if (this.path.steps && this.path.steps.length !== 0) {
      // Loop over first level of Steps and search recursivly in children for finding InheritedResources
      for (let i = 0; i < this.path.steps.length; i++) {
        let currentStep = this.path.steps[i]
        stepFound = retrieveInheritedResources(step, currentStep, inheritedResources)
        if (stepFound) {
          break
        }
      }
    }

    return inheritedResources
  }
}
