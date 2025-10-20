import {declareOauthApp} from '#/main/authentication/oauth'
import {trans} from '#/main/app/intl'

/**
 * Generic OAuth2 app integration.
 */
export default declareOauthApp({
  name: 'generic',
  fieldsMapping: true,
  configure: () => ([
    {
      name: 'urlAuthorize',
      type: 'url',
      label: trans('oauth_url_authorize', {}, 'security'),
      help: trans('oauth_url_authorize_help', {}, 'security'),
      required: true
    }, {
      name: 'urlAccessToken',
      type: 'url',
      label: trans('oauth_url_access_token', {}, 'security'),
      help: trans('oauth_url_access_token_help', {}, 'security'),
      required: true
    }, {
      name: 'urlResourceOwnerDetails',
      type: 'url',
      label: trans('oauth_url_resource_owner_details', {}, 'security'),
      help: trans('oauth_url_resource_owner_details_help', {}, 'security'),
      required: true
    }, {
      name: 'additionalParameters.scopes',
      type: 'collection',
      label: trans('oauth_scopes', {}, 'security'),
      size: 'sm',
      options: {
        type: 'string'
      }
    }
  ])
})
