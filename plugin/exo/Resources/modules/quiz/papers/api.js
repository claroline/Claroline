import {generateUrl} from './../../utils/routing'

export const fetchPapers = quizId => {
  return fetch(generateUrl('exercise_papers', {exerciseId: quizId}), {
    credentials: 'include',
    method: 'GET'
  })
  .then(response => response.json())
}
