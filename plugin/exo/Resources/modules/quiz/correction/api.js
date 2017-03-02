import {generateUrl} from './../../utils/routing'

export const fetchCorrection = quizId => {
  return fetch(generateUrl('exercise_correction_questions', {exerciseId: quizId}), {
    credentials: 'include',
    method: 'GET'
  })
  .then(response => response.json())
}