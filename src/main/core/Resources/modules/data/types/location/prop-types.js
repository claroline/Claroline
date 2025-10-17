import {PropTypes as T} from 'prop-types'

const Location = {
  propTypes: {
    id: T.string,
    name: T.string,
    poster: T.string,
    meta: T.shape({
      description: T.string
    }),
    phone: T.string,
    address: T.shape({
      street1: T.string,
      street2: T.string,
      postalCode: T.string,
      city: T.string,
      state: T.string,
      country: T.string
    })
  },
  defaultProps: {}
}

export {
  Location
}
