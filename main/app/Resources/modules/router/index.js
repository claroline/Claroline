import {history} from '#/main/app/router/history'

// reexport base router
export {
  NavLink,
  Switch,
  Redirect,
  withRouter,
  matchPath
} from 'react-router-dom'

// export routing components
export {
  Router,
  Routes,
  Route
} from '#/main/app/router/components/router.jsx'

/**
 * Shortcut to navigate using the correct history.
 *
 * @deprecated we will no longer allow dev to navigate this way.
 * Because we have 2 routes in UI know. They should retrieve the correct history in components
 * using `withRouter` HOC.
 */
export function navigate(url) {
  history.push(url)
}
