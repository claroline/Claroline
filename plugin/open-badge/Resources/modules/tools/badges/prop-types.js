import {PropTypes as T} from 'prop-types'

import {constants} from '#/plugin/open-badge/tools/badges/badge/constants'

const Badge = {
  propTypes: {
    id: T.string,
    name: T.string,
    image: T.shape({
      url: T.string.isRequired
    }),
    description: T.string,
    meta: T.shape({
      enabled: T.bool
    })
  },
  defaultProps: {
    issuingMode: [constants.ISSUING_MODE_ORGANIZATION]
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
