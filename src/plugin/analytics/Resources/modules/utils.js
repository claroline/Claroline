import get from 'lodash/get'
import {getApps} from '#/main/app/plugins'

function getResourceAnalytics(resourceNode) {
  return loadAnalytics('resource', resourceNode)
}

function getWorkspaceAnalytics(workspace) {
  return loadAnalytics('workspace', workspace)
}

function getAdministrationAnalytics() {
  return loadAnalytics('administration')
}

function loadAnalytics(appType, data = null) {
  const apps = getApps('analytics.'+appType)

  return Promise.all(
    Object.keys(apps).map(key => apps[key]())
  ).then(
    (loadedAnalytics) => loadedAnalytics
      .map(module => module.default(data))
      .filter(analytic => undefined === analytic.displayed || analytic.displayed)
      .sort((a, b) => {
        if (get(a, 'meta.order') < get(b, 'meta.order')) {
          return -1
        } else if (get(a, 'meta.order') > get(b, 'meta.order')) {
          return 1
        }

        return 0
      })
  )
}

export {
  getResourceAnalytics,
  getWorkspaceAnalytics,
  getAdministrationAnalytics
}
