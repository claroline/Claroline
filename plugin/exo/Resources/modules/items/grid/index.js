import editor, {SUM_CELL, SUM_COL, SUM_ROW} from './editor'
import {GridPaper} from './paper.jsx'
import {GridPlayer} from './player.jsx'
import {GridFeedback} from './feedback.jsx'

// As a grid question can have several cells with several choices,
// this function will return an array with the answers that have the biggest score
function expectAnswer(item) {
  let data = []
  // this method will be used to compute score max available for the question in SUM_MODE
  // whe should add answers or not depending on SUM_MODE type (CELL / ROW / COL)
  if (item.solutions) {

    switch(item.sumMode) {
      case SUM_CELL: {
        item.solutions.forEach(s => {
          if (s.answers) {
            let currentAnswer
            let currentScoreMax = 0
            s.answers.forEach(a => {
              if (a.score >= currentScoreMax) {
                currentScoreMax = a.score
                currentAnswer = a
              }
            })
            if (currentAnswer) {
              data.push(currentAnswer)
            }
          }
        })
        break
      }
      case SUM_ROW: {
        [...Array(item.rows)].forEach((x, index) => {
          // find cells that expect an answer for the row... None possible
          const answerCellsForRow = item.cells.filter(cell => cell.coordinates[1] === index && cell.input)
          if (0 < answerCellsForRow.length) {
            // pick the first one since all solutions for the row will have the same score
            const oneAnswerCellOfTheRow = answerCellsForRow[0]
            // get corresponding solution
            const cellSolution = item.solutions.find(solution => solution.cellId === oneAnswerCellOfTheRow.id)
            // every answer for the solution have the same score
            data.push(cellSolution.answers[0])
          }
        })
        break
      }
      case SUM_COL: {
        [...Array(item.cols)].forEach((x, index) => {
          // find cells that expect an answer for the col... None possible
          const answerCellsForCol = item.cells.filter(cell => cell.coordinates[0] === index && cell.input)
          if (0 < answerCellsForCol.length) {
            // pick the first one since all solutions for the col will have the same score
            const oneAnswerCellOfTheCol = answerCellsForCol[0]
            // get corresponding solution
            const cellSolution = item.solutions.find(solution => solution.cellId === oneAnswerCellOfTheCol.id)
            // every answer for the solution have the same score
            data.push(cellSolution.answers[0])
          }
        })
        break
      }
    }
  }

  return data
}

export default {
  type: 'application/x.grid+json',
  name: 'grid',
  paper: GridPaper,
  player: GridPlayer,
  feedback: GridFeedback,
  editor,
  expectAnswer
}
