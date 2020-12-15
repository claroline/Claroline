import {PropTypes as T} from 'prop-types'

const Modal = {
  propTypes: {
    id: T.string.isRequired,
    type: T.string.isRequired,
    fading: T.bool.isRequired,
    props: T.object
  },
  defaultProps: {}
}

export {
  Modal
}
