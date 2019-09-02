import {PropTypes as T} from 'prop-types'

import {ISSUING_MODE_ORGANIZATION} from '#/plugin/open-badge/tools/badges/badge/constants'

const Badge = {
  propTypes: {
    id: T.string
  },
  defaultProps: {
    issuingMode: [ISSUING_MODE_ORGANIZATION]
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
