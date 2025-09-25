import {getActions as getPluginsActions, getDefaultAction as getPluginsDefaultAction} from '#/main/app/plugins'

const ACTION_SET_NAME = 'workspace_evaluation'

function getActions(evaluations, refresher, path, currentUser, withDefault = false) {
  return getPluginsActions(ACTION_SET_NAME, evaluations, refresher, path, currentUser, withDefault)
}

function getDefaultAction(evaluation, refresher, path, currentUser = null) {
  return getPluginsDefaultAction(ACTION_SET_NAME, evaluation, refresher, path, currentUser)
}

function getEvaluationActions(evaluations, refresher, path, currentUser, withDefault = false) {
  return getPluginsActions(ACTION_SET_NAME, evaluations, refresher, path, currentUser, withDefault)
}

function getEvaluationDefaultAction(evaluation, refresher, path, currentUser = null) {
  return getPluginsDefaultAction(ACTION_SET_NAME, evaluation, refresher, path, currentUser)
}

function getCertificateActions(evaluations, refresher, path, currentUser, withDefault = false) {
  return getPluginsActions('workspace_certificate', evaluations, refresher, path, currentUser, withDefault)
}

function getCertificateDefaultAction(evaluation, refresher, path, currentUser = null) {
  return getPluginsDefaultAction('workspace_certificate', evaluation, refresher, path, currentUser)
}

export {
  getActions,
  getDefaultAction,
  getEvaluationActions,
  getEvaluationDefaultAction,
  getCertificateActions,
  getCertificateDefaultAction
}
