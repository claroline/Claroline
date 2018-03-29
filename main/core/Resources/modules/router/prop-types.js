import {PropTypes as T} from 'prop-types'

const Route = {
  propTypes: {
    path: T.string.isRequired,
    component: T.any, // todo find better typing
    render: T.func,
    exact: T.bool,
    canEnter: T.func,
    onEnter: T.func,
    onLeave: T.func
  },
  defaultProps: {
    path: '',
    exact: false
  }
}

const Redirect = {
  propTypes: {
    from: T.string.isRequired,
    to: T.string.isRequired,
    exact: T.bool
  },
  defaultProps: {
    exact: false
  }
}

const LocationAware = {
  propTypes: {
    location: T.shape({
      pathname: T.string
    }).isRequired
  }
}

export {
  LocationAware,
  Route,
  Redirect
}
