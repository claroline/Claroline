import {PropTypes as T} from 'prop-types'

const Modal = {
  propTypes: {
    type: T.string,
    fading: T.bool.isRequired,
    props: T.object
  },
  defaultProps: {}
}

export {
  Modal
}
