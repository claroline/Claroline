import {API_REQUEST} from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

export const LOAD_DOCIMOLOGY = 'LOAD_DOCIMOLOGY'

export const actions = {}

actions.loadDocimology = makeActionCreator(LOAD_DOCIMOLOGY, 'stats')

actions.fetchDocimology = (quizId) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['exercise_docimology', {id: quizId}],
    success: (data, dispatch) => dispatch(actions.loadDocimology(data))
  }
})
