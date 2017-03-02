
import {SUM_CELL} from './../editor'
import {SCORE_SUM, SCORE_FIXED} from './../../../quiz/enums'

export const utils = {

  getCellsByCol(colIndex, cells) {
    return cells.filter(cell => parseFloat(cell.coordinates[0]) === parseFloat(colIndex))
  },
  getCellsByColGreaterThan(rowIndex, cells) {
    return cells.filter(cell => parseFloat(cell.coordinates[0]) > parseFloat(rowIndex))
  },
  getCellsByRow(rowIndex, cells) {
    return cells.filter(cell => parseFloat(cell.coordinates[1]) === parseFloat(rowIndex))
  },
  getCellsByRowGreaterThan(rowIndex, cells) {
    return cells.filter(cell => parseFloat(cell.coordinates[1]) > parseFloat(rowIndex))
  },
  getCellByCoordinates(x, y, cells) {
    return cells.find(cell => parseFloat(cell.coordinates[0]) === parseFloat(x) && parseFloat(cell.coordinates[1]) === parseFloat(y))
  },
  getColScore(colIndex, cells, solutions) {
    // in col score mode each item of the col MUST have the same score
    const oneCellOfTheCol = cells.find(cell => cell.coordinates[0] === colIndex && undefined !== solutions.find(solution => solution.cellId === cell.id))
    let cellSolutionScore = 0
    if (undefined !== oneCellOfTheCol) {
      solutions.forEach(solution => {
        if (undefined !== solution.answers && solution.answers.length > 0 && solution.cellId === oneCellOfTheCol.id && solution.answers[0].score >= cellSolutionScore) {
          cellSolutionScore = solution.answers[0].score
        }
      })
    }
    return cellSolutionScore
  },
  getRowScore(rowIndex, cells, solutions) {
    // in row score mode each item of the row MUST have the same score
    const oneCellOfTheRow = cells.find(cell => cell.coordinates[1] === rowIndex && undefined !== solutions.find(solution => solution.cellId === cell.id))
    let cellSolutionScore = 0
    if (undefined !== oneCellOfTheRow) {
      solutions.forEach(solution => {
        if (undefined !== solution.answers && solution.answers.length > 0 && solution.cellId === oneCellOfTheRow.id && solution.answers[0].score > cellSolutionScore) {
          cellSolutionScore = solution.answers[0].score
        }
      })
    }
    return cellSolutionScore
  },
  atLeastOneSolutionInCol(colIndex, cells, solutions) {
    // in col score mode each item of the col MUST have the same score
    return undefined !== cells.find(cell => cell.coordinates[0] === colIndex && undefined !== solutions.find(solution => solution.cellId === cell.id))
  },
  atLeastOneSolutionInRow(rowIndex, cells, solutions) {
    // in col score mode each item of the col MUST have the same score
    return undefined !== cells.find(cell => cell.coordinates[1] === rowIndex && undefined !== solutions.find(solution => solution.cellId === cell.id))
  },
  getSolutionByCellId(cellId, solutions) {
    return solutions.find(solution => solution.cellId === cellId)
  },
  isValidSolution(solution) {
    return solution !== undefined && solution.answers !== undefined && solution.answers.filter(answer => answer.text !== '' && answer.score > 0).length > 0
  },
  hasDuplicates(solution) {
    let hasDuplicates = false
    if(solution !== undefined && solution.answers !== undefined) {
      solution.answers.forEach(answer => {
        let count = 0
        solution.answers.forEach(check => {
          if (answer.text === check.text && answer.caseSensitive === check.caseSensitive) {
            count++
          }
        })
        if (count > 1) hasDuplicates = true
      })
    }
    return hasDuplicates
  },
  getBestAnswer(answers) {

    let best = null
    answers.forEach(answer => {
      if(best === null || best.score < answer.score) {
        best = answer
      }
    })

    return best === null ? '' : best.text
  },
  getKeywordPositiveNegativeClass(typeScore, sumMode, keyword) {
    if(typeScore === SCORE_SUM && sumMode === SUM_CELL) {
      return keyword.score > 0 ? 'positive-score' : 'negative-score'
    } else if (typeScore === SCORE_FIXED || (typeScore === SCORE_SUM && sumMode !== SUM_CELL)) {
      return keyword.expected ? 'positive-score' : 'negative-score'
    }
  }
}
