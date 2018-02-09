import {history} from '#/main/core/router/history'

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
} from '#/main/core/router/components/router.jsx'

/**
 * Shortcut to navigate using the correct history.
 */
export function navigate(url) {
  history.push(url)
}
