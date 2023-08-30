import { API_REQUEST } from '#/main/app/api'
import {makeActionCreator} from '#/main/app/store/actions'
import {selectors} from '#/plugin/flashcard/resources/flashcard/store/selectors'

export const actions = {}

actions.updateProgressionProp = makeActionCreator(selectors.FLASHCARD_UPDATE_PROGRESSION, 'id', 'is_successful')

actions.updateUserProgression = (cardId, isSuccessful) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_flashcard_progression_update', {
      id: cardId,
      isSuccessful: isSuccessful
    } ],
    request: {
      method: 'PUT'
    },
    success: (data, dispatch) => {
      dispatch(actions.updateProgressionProp(cardId, isSuccessful))
    }
  }
})
