/**
 * Step Service
 */

import angular from 'angular/index'

export default class StepService {
  /**
   *
   * @param $http
   * @param $filter
   * @param {IdentifierService} IdentifierService
   * @param {ResourceService} ResourceService
   */
  constructor($http, $filter, Translator, url, IdentifierService, ResourceService) {
    this.$http = $http
    this.$filter = $filter
    this.Translator = Translator
    this.UrlGenerator = url
    this.IdentifierService = IdentifierService
    this.ResourceService = ResourceService
  }

  /**
   * Generates a new empty step.
   *
   * @param   {object} [parentStep]
   *
   * @returns {object}
   */
  newStep(parentStep) {
    const lvl = parentStep ? parentStep.lvl + 1 : 0

    const newStep = {
      id                : this.IdentifierService.generateUUID(),
      lvl               : lvl,
      name              : parentStep ? 'Step '+lvl+'.'+(parentStep.children.length + 1) : this.Translator.trans('root_default_name', {}, 'path_wizards'),
      description       : ' ',
      children          : [],
      activityId        : null,
      resourceId        : null,
      activityHeight    : null,
      primaryResource   : [],
      resources         : [],
      excludedResources : [],
      withTutor         : false,
      who               : null,
      where             : null,
      duration          : null,
      evaluationType    : null
    }

    if (parentStep) {
      // Append new child to its parent
      if (!parentStep.children instanceof Array) {
        parentStep.children = []
      }

      parentStep.children.push(newStep)
    }

    return newStep
  }

  /**
   * Check if we are in the range of the step accessiblity dates.
   *
   * @param {object} step
   *
   * @returns {boolean}
   */
  isBetweenAccessibilityDates(step) {
    const now = this.$filter('date')(new Date(), 'yyyy-MM-dd HH:mm:ss')

    let from = null
    if (step.accessibleFrom != null && step.accessibleFrom.length !== 0) {
      from = step.accessibleFrom
    }

    let until = null
    if (step.accessibleUntil != null && step.accessibleUntil.length !== 0) {
      until = step.accessibleUntil
    }

    let accessible = false
    if ( (null === from || now >= from) && (null === until || now <= until) ) {
      accessible = true
    }

    return accessible
  }

  /**
   * Load Activity data from ID.
   *
   * @param {object} step
   * @param {number} activityId
   */
  loadActivity(step, activityId) {
    this.$http
      .get(this.UrlGenerator('innova_path_load_activity', { nodeId: activityId }))
      .success((data) => this.setActivity(step, data))
  }

  /**
   * Injects the Activity data into step
   *
   * @param step
   * @param activity
   */
  setActivity(step, activity) {
    if (angular.isDefined(activity) && angular.isObject(activity)) {
      // Populate step
      step.activityId  = activity['id']
      step.name        = activity['name']
      step.description = activity['description']
      step.primaryResource = []
      step.resources = []

      // Primary resource
      if (angular.isDefined(activity['primaryResource']) && angular.isObject(activity['primaryResource'])) {
        // Initialize a new Resource object (parameters : claro type, mime type, id, name)
        const primaryResource = this.ResourceService.newResource(
          activity['primaryResource']['type'],
          activity['primaryResource']['mimeType'],
          activity['primaryResource']['resourceId'],
          activity['primaryResource']['name']
        )
        this.addResource(step.primaryResource, primaryResource)
      }

      // Secondary resources
      if (angular.isDefined(activity['resources']) && angular.isObject(activity['resources'])) {
        for (let i = 0; i < activity['resources'].length; i++) {
          let current = activity['resources'][i]

          let resource = this.ResourceService.newResource(current['type'], current['mimeType'], current['resourceId'], current['name'])
          this.addResource(step.resources, resource)
        }
      }

      // Parameters
      step.withTutor      = activity['withTutor']
      step.who            = activity['who']
      step.where          = activity['where']
      step.duration       = activity['duration']
      step.evaluationType = activity['evaluationType']
    }
  }

  addResource(resourcesList, resource) {
    if (!this.ResourceService.exists(resourcesList, resource)) {
      // Resource is not in the list => add it
      resourcesList.push(resource)
    }
  }
}
