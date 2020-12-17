import {PropTypes as T} from 'prop-types'

const Route = {
  propTypes: {
    path: T.string.isRequired,
    component: T.any, // todo find better typing
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
