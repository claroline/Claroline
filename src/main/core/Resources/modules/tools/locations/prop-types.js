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

const Room = {
  propTypes: {
    id: T.string,
    code: T.string,
    name: T.string,
    description: T.string,
    capacity: T.number,
    location: T.shape(
      Location.propTypes
    )
  },
  defaultProps: {
    capacity: 10
  }
}

const RoomBooking = {
  propTypes: {
    id: T.string,
    dates: T.arrayOf(T.string),
    description: T.string
  },
  defaultProps: {}
}

const Material = {
  propTypes: {
    id: T.string,
    code: T.string,
    name: T.string,
    description: T.string,
    quantity: T.number,
    location: T.shape(
      Location.propTypes
    )
  },
  defaultProps: {
    quantity: 1
  }
}

const MaterialBooking = {
  propTypes: {
    id: T.string,
    dates: T.arrayOf(T.string),
    description: T.string
  },
  defaultProps: {}
}

export {
  Location,
  Room,
  RoomBooking,
  Material,
  MaterialBooking
}
