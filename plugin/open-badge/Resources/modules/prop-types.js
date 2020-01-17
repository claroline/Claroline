import {PropTypes as T} from 'prop-types'

import {User} from '#/main/core/user/prop-types'

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
    }),
    restrictions: T.shape({
      hideRecipients: T.bool
    })
  },
  defaultProps: {
    issuingMode: [constants.ISSUING_MODE_ORGANIZATION],
    description: '',
    meta: {
      enabled: true
    },
    restrictions: {
      hideRecipients: false
    }
  }
}

const Assertion = {
  propTypes: {
    id: T.string,
    issuedOn: T.string.isRequired,
    badge: T.shape(
      Badge.propTypes
    ).isRequired,
    user: T.shape(
      User.propTypes
    ).isRequired
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
