import {API_REQUEST, url} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

import {actions as listActions} from '#/main/app/content/list/store/actions'
import {selectors} from '#/plugin/exo/resources/quiz/papers/store/selectors'

export const PAPER_ADD     = 'PAPER_ADD'
export const PAPER_CURRENT = 'PAPER_CURRENT'

export const actions = {}

actions.setCurrentPaper = makeActionCreator(PAPER_CURRENT, 'paper')
actions.addPaper = makeActionCreator(PAPER_ADD, 'paper')

actions.loadCurrentPaper = (quizId, paperId) => (dispatch, getState) => {
  const currentPaper = selectors.currentPaper(getState())

  if (!currentPaper || paperId !== currentPaper.id) {
    // only request paper if it has changed
    // this permits to pre-mount anonymous papers at the end of their quiz to show them
    return dispatch({
      [API_REQUEST]: {
        silent: true,
        url: ['exercise_paper_get', {
          exerciseId: quizId,
          id: paperId
        }],
        success: (data) => dispatch(actions.setCurrentPaper(data))
      }
    })
  }
}

actions.deletePapers = (quizId, papers) => ({
  [API_REQUEST]: {
    url: url(['ujm_exercise_delete_papers', {
      exerciseId: quizId
    }], {
      ids: papers.map(paper => paper.id)
    }),
    request: {
      method: 'DELETE'
    },
    success: (data, dispatch) => dispatch(listActions.invalidateData(selectors.LIST_NAME))
  }
})