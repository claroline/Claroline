import {PropTypes as T} from 'prop-types'

const Address = {
  propTypes: {
    street1: T.string,
    street2: T.string,
    postalCode: T.string,
    city: T.string,
    state: T.string,
    country: T.string
  },
  defaultProps: {}
}

export {
  Address
}
