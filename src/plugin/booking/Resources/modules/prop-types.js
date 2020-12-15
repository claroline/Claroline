import {PropTypes as T} from 'prop-types'

const Room = {
  propTypes: {
    id: T.string,
    code: T.string,
    name: T.string,
    description: T.string,
    capacity: T.number,
    location: T.shape({
      // Location prop-types
    })
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
    quantity: T.number
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
  Room,
  RoomBooking,
  Material,
  MaterialBooking
}
