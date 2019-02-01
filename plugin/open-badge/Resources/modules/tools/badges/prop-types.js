import {PropTypes as T} from 'prop-types'

const Badge = {
  propTypes: {
    id: T.string
  },
  defaultProps: {
    issuingMode: []
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
