import {makeActionCreator, makeInstanceAction} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'
import {API_FETCH_FULFILLED, actions as fetchActions} from '#/main/app/api/fetch/store/actions'
import {selectors} from '#/main/evaluation/sequence/store/selectors'

export const SEQUENCE_OPEN = makeInstanceAction(API_FETCH_FULFILLED, selectors.STORE_NAME)
export const SEQUENCE_RELOAD = 'SEQUENCE_RELOAD'
export const SEQUENCE_EVALUATION_UPDATE    = 'SEQUENCE_EVALUATION_UPDATE'
export const SEQUENCE_SET_CURRENT_STEP = 'SEQUENCE_SET_CURRENT_STEP'
export const SEQUENCE_ENABLE_NAVIGATION = 'SEQUENCE_ENABLE_NAVIGATION'
export const SEQUENCE_DISABLE_NAVIGATION = 'STEP_DISABLE_NAVIGATION'

export const actions = {}

actions.updateUserEvaluation = makeActionCreator(SEQUENCE_EVALUATION_UPDATE, 'userEvaluation', 'progression')
actions.setCurrentStep = makeActionCreator(SEQUENCE_SET_CURRENT_STEP, 'stepSlug')
actions.enableNavigation = makeActionCreator(SEQUENCE_ENABLE_NAVIGATION)
actions.disableNavigation = makeActionCreator(SEQUENCE_DISABLE_NAVIGATION)

actions.reload = makeActionCreator(SEQUENCE_RELOAD, 'sequence')

actions.updateProgression = (stepId) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_sequence_evaluation_update', {id: stepId}],
    request: {
      method: 'PUT'
    },
    success: (response) => dispatch(actions.updateUserEvaluation(response.evaluation, response.progression))
  }
})

actions.checkAccessCode = (sequence, code) => (dispatch) => dispatch({
  [API_REQUEST] : {
    url: ['apiv2_evaluation_sequence_unlock', {id: sequence.id}],
    request: {
      method: 'POST',
      body: JSON.stringify({code: code})
    },
    success: () => dispatch(fetchActions.invalidate(selectors.STORE_NAME)) // force the reload of the sequence
  }
})
