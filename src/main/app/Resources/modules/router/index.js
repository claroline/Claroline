// reexport base router
export {
  NavLink,
  Switch,
  Redirect,
  withRouter,
  matchPath,
  useLocation
} from 'react-router-dom'

export {Route as RouteTypes, Redirect as RedirectTypes} from '#/main/app/router/prop-types'

// export custom routing components
export {Router} from '#/main/app/router/components/router'
export {Routes} from '#/main/app/router/components/routes'
export {Route} from '#/main/app/router/components/route'
