import {getApps} from '#/main/app/plugins'

function getAnalytics(resourceNode) {
  const apps = getApps('analytics.resource')

  return Promise.all(
    Object.keys(apps).map(key => apps[key]())
  ).then(
    (loadedAnalytics) => loadedAnalytics.map(module => module.default(resourceNode)).filter(analytic => analytic.displayed)
  )
}

export {
  getAnalytics
}
