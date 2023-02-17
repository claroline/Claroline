import {number} from '#/main/app/intl'

function displayScore(scoreMax, score, displayScore) {
  if (score) {
    return number((score / scoreMax) * displayScore) + ''
  }

  return '0'
}

export {
  displayScore
}