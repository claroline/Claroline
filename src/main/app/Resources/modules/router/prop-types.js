import {PropTypes as T} from 'prop-types'

const Route = {
  propTypes: {
    path: T.string.isRequired,
    component: T.any,
    render: T.func,
    exact: T.bool,
    disabled: T.bool,
    onEnter: T.func,
    onLeave: T.func
  },
  defaultProps: {
    path: '',
    disabled: false,
    exact: false
  }
}

const Redirect = {
  propTypes: {
    from: T.string.isRequired,
    to: T.string.isRequired,
    exact: T.bool,
    disabled: T.bool
  },
  defaultProps: {
    exact: false,
    disabled: false
  }
}

export {
  Route,
  Redirect
}
