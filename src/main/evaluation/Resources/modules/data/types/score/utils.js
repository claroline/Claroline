import {number} from '#/main/app/intl'

function displayScore(scoreMax, score, displayScore) {
  let computedScore = 0
  if (scoreMax && score) {
    computedScore = displayScore ? (score / scoreMax) * displayScore : score
  }

  if (scoreMax) {
    return number(computedScore) + ' / ' + number(scoreMax)
  }

  return ''
}

export {
  displayScore
}
