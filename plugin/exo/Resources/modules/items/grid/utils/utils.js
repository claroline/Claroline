
export const utils = {
  getCellsByCol(colIndex, cells) {
    return cells.filter(cell => parseFloat(cell.coordinates[0]) === parseFloat(colIndex))
  },

  getCellsByColGreaterThan(rowIndex, cells) {
    return cells.filter(cell => parseFloat(cell.coordinates[0]) > parseFloat(rowIndex))
  },

  getCellsByRow(rowIndex, cells) {
    return cells
      .filter(cell => parseFloat(cell.coordinates[1]) === parseFloat(rowIndex))
      // sort cells by column
      .sort((cellA, cellB) =>
        cellA.coordinates[0] < cellB.coordinates[0] ? -1 : 1
      )
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

  getRowScore(rowCells, solutions) {
    // in row score mode each item of the row MUST have the same score
    const oneCellOfTheRow = rowCells.find(cell => undefined !== solutions.find(solution => solution.cellId === cell.id))
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

  atLeastOneSolutionInRow(rowIndex, rowCells, solutions) {
    // in col score mode each item of the col MUST have the same score
    return undefined !== rowCells.find(cell => cell.coordinates[1] === rowIndex && undefined !== solutions.find(solution => solution.cellId === cell.id))
  },

  getSolutionByCellId(cellId, solutions) {
    return solutions.find(solution => solution.cellId === cellId)
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
  }
}
