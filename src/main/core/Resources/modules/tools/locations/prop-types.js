import {PropTypes as T} from 'prop-types'

const Location = {
  propTypes: {
    id: T.string,
    name: T.string,
    meta: T.shape({
      type: T.number,
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
    }),
    gps: T.shape({
      latitude: T.number,
      longitude: T.number
    })
  },
  defaultProps: {}
}

export {
  Location
}
