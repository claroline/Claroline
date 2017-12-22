import {Router, history} from 'backbone'
import {actions} from './actions'
import {actions as paperActions} from './papers/actions'
import {actions as playerActions} from './player/actions'
import {actions as correctionActions} from './correction/actions'
import {actions as statisticActions} from './statistics/actions'
import {VIEW_EDITOR, VIEW_OVERVIEW, VIEW_ATTEMPT_END} from './enums'

let router = null

export function makeRouter(dispatch) {
  const QuizRouter = Router.extend({
    routes: {
      'overview': () => dispatch(actions.updateViewMode(VIEW_OVERVIEW)),
      'editor': () => dispatch(actions.updateViewMode(VIEW_EDITOR)),
      'papers/:id': id => dispatch(paperActions.displayPaper(id)),
      'papers': () => dispatch(paperActions.listPapers()),
      'correction/questions': () => dispatch(correctionActions.displayQuestions()),
      'correction/questions/:id': id => dispatch(correctionActions.displayQuestionAnswers(id)),
      'statistics': () => dispatch(statisticActions.displayStatistics()),
      'test': () => dispatch(playerActions.play(null, true)),
      'play': () => dispatch(playerActions.play(null, false)),
      'play/end': () => dispatch(actions.updateViewMode(VIEW_ATTEMPT_END)),
      '': () => dispatch(actions.updateViewMode(VIEW_OVERVIEW, false)),
      '.*': () => dispatch(actions.updateViewMode(VIEW_OVERVIEW))
    }
  })
  router = new QuizRouter()
  history.start()
}

export function navigate(fragment, trigger = true) {
  if (!router) {
    throw new Error('Router has not been initialized')
  }

  return router.navigate(fragment, {trigger})
}
