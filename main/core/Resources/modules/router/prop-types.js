import {PropTypes as T} from 'prop-types'

const Route = {
  propTypes: {
    path: T.string.isRequired,
    component: T.node.isRequired,
    exact: T.bool,
    onEnter: T.func,
    onLeave: T.func
  },
  defaultProps: {
    exact: true
  }
}

export {
  Route
}
