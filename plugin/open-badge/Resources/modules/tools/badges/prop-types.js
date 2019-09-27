import {PropTypes as T} from 'prop-types'

import {constants} from '#/plugin/open-badge/tools/badges/badge/constants'

const Badge = {
  propTypes: {
    id: T.string,
    name: T.string,
    image: T.shape({
      url: T.string.isRequired
    }),
    criteria: T.string,
    description: T.string,
    issuingMode: T.arrayOf(T.string),
    meta: T.shape({
      enabled: T.bool
    })
  },
  defaultProps: {
    issuingMode: [constants.ISSUING_MODE_ORGANIZATION],
    meta: {
      enabled: true
    }
  }
}

const Assertion = {
  propTypes: {
    id: T.string
  }
}

const Evidence = {
  propTypes: {
    id: T.string
  }
}

export {
  Badge,
  Assertion,
  Evidence
}
