import {actions as baseActions} from './../actions'
import {VIEW_STATISTICS} from './../enums'
import {selectors as paperSelectors} from './../papers/selectors'
import {actions as paperActions} from './../papers/actions'

export const actions = {}

actions.displayStatistics = () => {
  return (dispatch, getState) => {
    if (!paperSelectors.papersFetched(getState())) {
      dispatch(paperActions.fetchPapers(paperSelectors.quizId(getState()))).then(() => {
        dispatch(baseActions.updateViewMode(VIEW_STATISTICS))
      })
    } else {
      dispatch(baseActions.updateViewMode(VIEW_STATISTICS))
    }
  }
}
