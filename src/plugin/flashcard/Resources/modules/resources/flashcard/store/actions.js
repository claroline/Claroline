import { API_REQUEST } from '#/main/app/api'
export const actions = {}

actions.updateUserProgression = (cardId, isSuccessful) => (dispatch) => dispatch({
  [API_REQUEST]: {
    silent: true,
    url: ['apiv2_flashcard_progression_update', {
      id: cardId,
      isSuccessful: isSuccessful
    } ],
    request: {
      method: 'PUT'
    }
  }
})
