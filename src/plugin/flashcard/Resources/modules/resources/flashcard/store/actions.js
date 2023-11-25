import { API_REQUEST } from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

import {actions as resourceActions} from '#/main/core/resource/store'

export const ATTEMPT_LOAD = 'ATTEMPT_LOAD'
export const FLASHCARD_UPDATE_PROGRESSION = 'FLASHCARD_UPDATE_PROGRESSION'

export const actions = {}

actions.getAttemptAction = makeActionCreator(ATTEMPT_LOAD, 'data')
actions.updateCardProgression = makeActionCreator(FLASHCARD_UPDATE_PROGRESSION, 'id', 'isSuccessful')

actions.updateProgression = (cardId, isSuccessful) => ({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_flashcard_progression_update', {id: cardId, isSuccessful: isSuccessful}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(resourceActions.updateUserEvaluation(data.userEvaluation))
      dispatch(actions.getAttemptAction(data))
    }
  }
})

actions.getAttempt = (deckId) => ({
  [API_REQUEST]: {
    url: ['apiv2_flashcard_deck_current_attempt', {id: deckId}],
    request: {
      method: 'GET'
    },
    success: (response, dispatch) => dispatch(actions.getAttemptAction(response))
  }
})
