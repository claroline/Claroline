import {PropTypes as T} from 'prop-types'

import {User} from '#/main/community/prop-types'

const Badge = {
  propTypes: {
    id: T.string,
    name: T.string,
    image: T.string,
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
    issuingPeer: false,
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
