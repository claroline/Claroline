import get from 'lodash/get'

import {
  getApp,
  getActions as getPluginsActions,
  getDefaultAction as getPluginsDefaultAction
} from '#/main/app/plugins'

function getActions(evaluations, refresher, path, currentUser, withDefault = false) {
  return getPluginsActions('resource_evaluation', evaluations, refresher, path, currentUser, withDefault)
}

function getDefaultAction(evaluation, refresher, path, currentUser = null) {
  return getPluginsDefaultAction('resource_evaluation', evaluation, refresher, path, currentUser)
}

function getAttemptActions(evaluations, refresher, path, currentUser, withDefault = false) {
  return getPluginsActions('resource_attempt', evaluations, refresher, path, currentUser, withDefault)
}

function getAttemptDefaultAction(evaluation, refresher, path, currentUser = null) {
  return getPluginsDefaultAction('resource_attempt', evaluation, refresher, path, currentUser)
}

/**
 * Get the resource type component to display in the attempt details.
 */
function getAttempt(attempt) {
  return getApp('evaluation', get(attempt, 'resourceNode.meta.type'))()
}

export {
  getAttempt,
  getActions,
  getDefaultAction,
  getAttemptActions,
  getAttemptDefaultAction
}
