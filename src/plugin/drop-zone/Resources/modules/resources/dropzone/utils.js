import {now} from '#/main/app/intl/date'
import {makeId} from '#/main/core/scaffolding/id'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

function generateCorrection(dropId, user, dropzone, teamId = null) {
  const currentDate = now()
  const correction = {
    id: makeId(),
    drop: dropId,
    user: user,
    score: null,
    comment: null,
    startDate: currentDate,
    lastEditionDate: currentDate,
    endDate: null,
    finished: false,
    valid: true,
    editable: false,
    reported: false,
    correctionDenied: false,
    teamId: teamId,
    grades: []
  }

  return generateCorrectionGrades(correction, dropzone)
}

function generateCorrectionGrades(correction, dropzone) {
  const grades = []

  if (dropzone.parameters.criteriaEnabled) {
    dropzone.parameters.criteria.forEach(c => {
      const grade = correction.grades.find(g => g.criterion === c.id)

      if (grade) {
        grades.push(grade)
      } else {
        grades.push({
          id: makeId(),
          value: 0,
          correction: correction.id,
          criterion: c.id
        })
      }
    })
  }

  return Object.assign({}, correction, {grades: grades})
}

function computeScoreFromGrades(grades, gradeMax, scoreMax) {
  let score = 0
  const total = (gradeMax - 1) * grades.length
  grades.forEach(g => score += g.value)

  return Math.round((score / total) * scoreMax * 100) / 100
}

function computeDropCompletion(dropzone, drop, nbFinishedCorrections) {
  const nbExpected = dropzone.parameters.expectedCorrectionTotal

  return drop.finished && (
    constants.REVIEW_TYPE_PEER !== dropzone.parameters.reviewType || (
      (drop.unlockedDrop || drop.corrections.filter(c => c.finished && c.valid).length >= nbExpected) &&
      (drop.unlockedUser || nbFinishedCorrections >= nbExpected)
    )
  )
}

function getCorrectionKey(drop, dropzone) {
  let key = null

  switch (dropzone.parameters.dropType) {
    case constants.DROP_TYPE_USER :
      key = `user_${drop.user.id}`
      break
    case constants.DROP_TYPE_TEAM :
      key = `team_${drop.teamId}`
      break
  }

  return key
}

export {
  generateCorrection,
  generateCorrectionGrades,
  computeScoreFromGrades,
  computeDropCompletion,
  getCorrectionKey
}
