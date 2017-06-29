import {Router, history} from 'backbone'
import {actions} from './actions'

let router = null

export function makeRouter(dispatch) {
  const AdminTaskToolRouter = Router.extend({
    routes: {
      '': () => dispatch(actions.displayManagementView()),
      'mail': () => dispatch(actions.displayMailView()),
      'message': () => dispatch(actions.displayMessageView())
    }
  })
  router = new AdminTaskToolRouter()
  history.start()
}

export function navigate(fragment, trigger = true) {
  if (!router) {
    throw new Error('Router has not been initialized')
  }

  return router.navigate(fragment, {trigger})
}
