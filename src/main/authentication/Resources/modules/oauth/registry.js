import {getApp, getApps} from '#/main/app/plugins'

const REGISTRY_NAME = 'oauth'

/**
 * Get context definitions in the plugin registry.
 */
function getOauthApps() {
  // get all oauth types declared in the app
  const oauth = getApps(REGISTRY_NAME)

  return Promise.all(
    // boot oauth applications
    Object.keys(oauth).map(oauthName => oauth[oauthName]())
  ).then(loadedOauth => loadedOauth
    .map(loadedOauth => loadedOauth.default)
  )
}

function getOauthApp(name) {
  return getApp(REGISTRY_NAME, name)()
}

export {
  getOauthApps,
  getOauthApp
}
