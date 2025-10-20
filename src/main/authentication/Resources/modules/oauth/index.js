
import {getOauthApp, getOauthApps} from '#/main/authentication/oauth/registry'
import invariant from 'invariant'

function declareOauthApp(appDefinition) {
  invariant(appDefinition.name, 'Oauth app definition must have a "name".')

  return appDefinition
}

export {
  getOauthApp,
  getOauthApps,
  declareOauthApp
}
