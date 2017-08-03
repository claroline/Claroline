import {Router, history} from 'backbone'
import {actions} from './actions'

let router = null

export function makeRouter(dispatch) {
  const MyObjectivesToolRouter = Router.extend({
    routes: {
      '': () => dispatch(actions.displayMainView()),
      ':oId/competency/:cId': (oId, cId) => dispatch(actions.displayCompetencyView(oId, cId))
    }
  })
  router = new MyObjectivesToolRouter()
  history.start()
}

export function navigate(fragment, trigger = true) {
  if (!router) {
    throw new Error('Router has not been initialized')
  }

  return router.navigate(fragment, {trigger})
}
