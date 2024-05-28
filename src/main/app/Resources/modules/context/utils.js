import merge from 'lodash/merge'

import {getActions as getPluginsActions} from '#/main/app/plugins'

function getActions(contextName, contexts, contextRefresher = {}, path, currentUser = null, withDefault = false) {
  return Promise.all([
    getPluginsActions('context', contexts.map(context => merge({}, context, {type: contextName})), contextRefresher, path, currentUser, withDefault),
    getPluginsActions(contextName, contexts.map(context => merge({}, context, {type: contextName})), contextRefresher, path, currentUser, withDefault)
  ]).then((loadedActions) => loadedActions.reduce((current, acc) => acc.concat(current), []))
}

function getDefaultAction(contextName, context, contextRefresher = {}, path, currentUser = null) {
  return getActions(contextName, [context], contextRefresher, path, currentUser, true)
    // only get the default one
    .then(actions => actions.find(action => action.default))
}

export {
  getActions,
  getDefaultAction
}
