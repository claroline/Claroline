import {PropTypes as T} from 'prop-types'

const OauthClient = {
  propTypes: {
    id: T.string,
    serviceProvider: T.string.isRequired,
    name: T.string.isRequired,
    clientId: T.string.isRequired,
    clientSecret: T.string,
    clientSecretDisplay: T.string,
    defaultMapping: T.array
  }
}

export {
  OauthClient
}
