import {PropTypes as T} from 'prop-types'

const LtiApp = {
  propTypes: {
    id: T.string.isRequired,
    title: T.string.isRequired,
    url: T.string.isRequired,
    description: T.string,
    appKey: T.string,
    secret: T.string
  }
}

const LtiResource = {
  propTypes: {
    id: T.string,
    ltiApp: T.shape(LtiApp.propTypes),
    openInNewTab: T.bool,
    ratio: T.number,
    ltiData: T.object
  }
}

export {
  LtiApp,
  LtiResource
}