import {Router, history} from 'backbone'
import {actions} from './actions'

let router = null

export function makeRouter(dispatch) {
  const SessionEventsToolRouter = Router.extend({
    routes: {
      'event/:id': id => dispatch(actions.displaySessionEvent(id)),
      '': () => dispatch(actions.displayMainView())
    }
  })
  router = new SessionEventsToolRouter()
  history.start()
}

export function navigate(fragment, trigger = true) {
  if (!router) {
    throw new Error('Router has not been initialized')
  }

  return router.navigate(fragment, {trigger})
}
