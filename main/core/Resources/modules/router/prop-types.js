import {PropTypes as T} from 'prop-types'

const Route = {
  propTypes: {
    path: T.string.isRequired,
    component: T.any.isRequired, // todo find better typing
    exact: T.bool,
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
