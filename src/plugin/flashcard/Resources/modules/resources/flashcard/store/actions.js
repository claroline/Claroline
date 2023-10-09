import { API_REQUEST } from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'

import {actions as resourceActions} from '#/main/core/resource/store'

export const FLASHCARD_GET_DECK = 'FLASHCARD_GET_DECK'
export const FLASHCARD_UPDATE_PROGRESSION = 'FLASHCARD_UPDATE_PROGRESSION'

export const actions = {}

actions.updateCardProgression = makeActionCreator(FLASHCARD_UPDATE_PROGRESSION, 'id', 'isSuccessful')
actions.startAttemptAction = makeActionCreator(FLASHCARD_GET_DECK, 'data')

actions.updateProgression = (cardId, isSuccessful, silent = true) => ({
  [API_REQUEST]: {
    silent: silent,
    url: ['apiv2_flashcard_progression_update', {id: cardId, isSuccessful: isSuccessful}],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(resourceActions.updateUserEvaluation(data.userEvaluation))
      dispatch(actions.updateCardProgression(cardId, isSuccessful))
    }
  }
})

actions.startAttempt = (deckId, silent = true) => ({
  [API_REQUEST]: {
    silent: silent,
    url: ['apiv2_flashcard_deck_get', {id: deckId}],
    request: {
      method: 'GET'
    },
    success: (data, dispatch) => {
      dispatch(actions.startAttemptAction(data))
    }
  }
})
