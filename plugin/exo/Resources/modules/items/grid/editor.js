import cloneDeep from 'lodash/cloneDeep'
import set from 'lodash/set'
import {ITEM_CREATE} from './../../quiz/editor/actions'
import {SCORE_SUM, SCORE_FIXED} from './../../quiz/enums'
import {makeActionCreator, makeId} from './../../utils/utils'
import {tex} from './../../utils/translate'
import {notBlank} from './../../utils/validate'
import {Grid as component} from './editor.jsx'
import {utils} from './utils/utils'

const UPDATE_PROP = 'UPDATE_PROP'
const DELETE_COLUMN = 'DELETE_COLUMN'
const DELETE_ROW = 'DELETE_ROW'
const UPDATE_COLUMN_SCORE = 'UPDATE_COLUMN_SCORE'
const UPDATE_ROW_SCORE = 'UPDATE_ROW_SCORE'
const UPDATE_CELL_STYLE = 'UPDATE_CELL_STYLE'
const UPDATE_CELL_DATA = 'UPDATE_CELL_DATA'
const ADD_OR_UPDATE_SOLUTION = 'ADD_OR_UPDATE_SOLUTION'
const DELETE_SOLUTION = 'DELETE_SOLUTION'

export const SUM_CELL = 'cell'
export const SUM_COL = 'col'
export const SUM_ROW = 'row'

export const actions = {
  updateProperty: makeActionCreator(UPDATE_PROP, 'property', 'value'),
  deleteColumn: makeActionCreator(DELETE_COLUMN, 'index'),
  deleteRow: makeActionCreator(DELETE_ROW, 'index'),
  updateColumnScore: makeActionCreator(UPDATE_COLUMN_SCORE, 'index', 'score'),
  updateRowScore: makeActionCreator(UPDATE_ROW_SCORE, 'index', 'score'),
  updateCellData: makeActionCreator(UPDATE_CELL_DATA, 'id', 'value'),
  updateCellStyle: makeActionCreator(UPDATE_CELL_STYLE, 'id', 'property', 'value'),
  addOrUpdateSolution: makeActionCreator(ADD_OR_UPDATE_SOLUTION, 'data'),
  deleteSolution: makeActionCreator(DELETE_SOLUTION, 'id')
}


function reduce(grid = {}, action) {
  switch (action.type) {
    case ITEM_CREATE: {
      return Object.assign({}, grid, {
        random: false,
        penalty: 0,
        sumMode: SUM_CELL,
        cells: [
          makeDefaultCell(0,0),
          makeDefaultCell(0,1),
          makeDefaultCell(1,0),
          makeDefaultCell(1,1)
        ],
        rows: 2,
        cols: 2,
        border: {
          color: '#000',
          width: 1
        },
        solutions: []
      })
    }
    case UPDATE_PROP: {
      const newItem = cloneDeep(grid)
      switch (action.property) {
        case 'penalty': {
          newItem[action.property] = parseFloat(action.value)
          break
        }
        case 'rows': {
          if (action.value < grid.rows) {
            return deleteRow(action.value, newItem, false)
          } else {
            const newRowIndex = action.value - 1
            newItem[action.property] = parseFloat(action.value)
            // add default cell content to each created cell
            for (let i = 0; i < grid.cols; i++) {
              newItem.cells.push(makeDefaultCell(i, newRowIndex))
            }
          }
          break
        }
        case 'cols': {
          if (action.value < grid.cols) {
            return deleteCol(action.value, newItem, false)
          } else {
            newItem[action.property] = parseFloat(action.value)
            const colIndex = action.value - 1
            // add default cell content to each created cell
            for (let i = 0; i < grid.rows; i++) {
              newItem.cells.push(makeDefaultCell(colIndex, i))
            }
          }
          break
        }
        case 'sumMode': {
          if (action.value === SCORE_FIXED) {
            newItem.score.type = SCORE_FIXED
            // can not apply penalty in this case
            newItem.penalty = 0
            // set expected answers
            newItem.solutions.forEach(solution => {
              solution.answers.forEach(answer => {
                if (answer.score > 0) {
                  answer.expected = true
                } else {
                  answer.expected = false
                }
              })
            })
          } else {
            newItem.score.type = SCORE_SUM
            newItem[action.property] = action.value
            // set default values for success and failure
            newItem.score.success = 1
            newItem.score.failure = 0
            // if SUM_CELL update every solution answers
            if (action.value === SUM_CELL) {
              newItem.solutions.forEach(solution => {
                solution.answers.forEach(answer => {
                  if (answer.expected) {
                    answer.score = 1
                  } else {
                    answer.score = 0
                  }
                })
              })
            } else {
              // set expected answers
              newItem.solutions.forEach(solution => {
                solution.answers.forEach(answer => {
                  if (answer.score > 0) {
                    answer.expected = true
                  } else {
                    answer.expected = false
                  }
                })
              })
            }
          }
          break
        }
        case 'scoreSuccess': {
          newItem.score.success = parseFloat(action.value)
          break
        }
        case 'scoreFailure': {
          newItem.score.failure = parseFloat(action.value)
          break
        }
        case 'shuffle': {
          newItem[action.property] = Boolean(action.value)
          break
        }
        case 'borderWidth': {
          newItem.border.width = parseFloat(action.value)
          break
        }
        case 'borderColor': {
          newItem.border.color = action.value
          break
        }
      }
      return newItem
    }
    case DELETE_COLUMN: {
      const newItem = cloneDeep(grid)
      return deleteCol(action.index, newItem, true)
    }
    case DELETE_ROW: {
      const newItem = cloneDeep(grid)
      return deleteRow(action.index, newItem, true)
    }
    case UPDATE_COLUMN_SCORE: {
      const newItem = cloneDeep(grid)
      const cellsInRow = utils.getCellsByCol(action.index, newItem.cells)
      cellsInRow.forEach(cell => {
        const solutionToUpdate = newItem.solutions.find(solution => solution.cellId === cell.id)
        if (undefined !== solutionToUpdate && undefined !== solutionToUpdate.answers && solutionToUpdate.answers.length > 0) {
          solutionToUpdate.answers.forEach(answer => answer.score = parseFloat(action.score))
        }
      })
      return newItem
    }
    case UPDATE_ROW_SCORE: {
      const newItem = cloneDeep(grid)
      const cellsInRow = utils.getCellsByRow(action.index, newItem.cells)
      cellsInRow.forEach(cell => {
        const solutionToUpdate = newItem.solutions.find(solution => solution.cellId === cell.id)
        if (undefined !== solutionToUpdate && undefined !== solutionToUpdate.answers && solutionToUpdate.answers.length > 0) {
          solutionToUpdate.answers.forEach(answer => answer.score = parseFloat(action.score))
        }
      })
      return newItem
    }
    case UPDATE_CELL_STYLE: {
      const newItem = cloneDeep(grid)
      // action property = color / background
      const cellToUpdate = newItem.cells.find(cell => cell.id === action.id)
      cellToUpdate[action.property] = action.value
      return newItem
    }
    case UPDATE_CELL_DATA: {
      const newItem = cloneDeep(grid)
      // action property = color / background
      const cellToUpdate = newItem.cells.find(cell => cell.id === action.id)
      cellToUpdate.data = action.value
      return newItem
    }
    case ADD_OR_UPDATE_SOLUTION: {
      const newItem = cloneDeep(grid)
      const solution = newItem.solutions.find(solution => solution.cellId === action.data.solution.cellId)
      if(undefined !== solution) {
        // update
        solution.answers = action.data.solution.answers
      } else {
        // new
        newItem.solutions.push({
          cellId: action.data.solution.cellId,
          answers: action.data.solution.answers
        })
      }
      const cell = newItem.cells.find(cell => cell.id === action.data.solution.cellId)
      // ensure cell data is empty
      cell.data = ''
      cell.choices = []
      cell.input = true
      if(action.data.isList) {
        // fill cell choices
        action.data.solution.answers.forEach(answer => {
          cell.choices.push(answer.text)
        })
      }

      return newItem
    }
    case DELETE_SOLUTION: {
      const newItem = cloneDeep(grid)
      const cell = newItem.cells.find(cell => cell.id === action.id)
      cell.choices = []
      cell.input = false
      const solutionIndex = newItem.solutions.findIndex(solution => solution.cellId === action.id)
      newItem.solutions.splice(solutionIndex, 1)
      return newItem
    }
  }
  return grid
}

function deleteRow(rowIndex, grid, updateCoords){
  const cellsToDelete = utils.getCellsByRow(rowIndex, grid.cells)
  cellsToDelete.forEach(cell => {
    const cellIndex = grid.cells.findIndex(toRemove => toRemove.id === cell.id)
    grid.cells.splice(cellIndex, 1)
    // also remove associated solution if any
    const solutionIndex = grid.solutions.findIndex(solution => solution.cellId === cell.id)
    if (-1 !== solutionIndex) {
      grid.solutions.splice(solutionIndex, 1)
    }
  })
  // update y coordinates if we are deleting a specific row
  if (updateCoords) {
    // get cells that have a row index greater than the one we are deleting
    let cellsToUpdate = utils.getCellsByRowGreaterThan(rowIndex, grid.cells)
    cellsToUpdate.forEach(cell => --cell.coordinates[1])
  }

  --grid.rows
  return grid
}

function deleteCol(colIndex, grid, updateCoords){
  const cellsToDelete = utils.getCellsByCol(colIndex, grid.cells)
  cellsToDelete.forEach(cell => {
    const cellIndex = grid.cells.findIndex(toRemove => toRemove.id === cell.id)
    grid.cells.splice(cellIndex, 1)
    // also remove associated solution if any
    const solutionIndex = grid.solutions.findIndex(solution => solution.cellId === cell.id)
    if (-1 !== solutionIndex) {
      grid.solutions.splice(solutionIndex, 1)
    }
  })
  // update x coordinates if we are deleting a specific col
  if (updateCoords) {
    // get cells that have a col index greater than the one we are deleting
    let cellsToUpdate = utils.getCellsByColGreaterThan(colIndex, grid.cells)
    cellsToUpdate.forEach(cell => --cell.coordinates[0])
  }
  --grid.cols
  return grid
}

function validate(grid) {
  const _errors = {}

  grid.cells.forEach(cell => {
    const solution = grid.solutions.find(solution => solution.cellId === cell.id)
    let hasPositiveValue = false
    if(undefined !== solution) {

      solution.answers.forEach((answer) => {
        if (notBlank(answer.text)) {
          set(_errors, 'answers.text', tex('grid_empty_word_error'))
        }

        if (grid.score.type === SCORE_SUM && grid.sumMode === SUM_CELL && answer.score > 0) {
          hasPositiveValue = true
        } else if (answer.expected) {
          hasPositiveValue = true
        }
      })

      if (hasDuplicates(solution.answers)) {
        set(_errors, 'answers.duplicate', tex('grid_duplicate_answers'))
      }

      if (!hasPositiveValue) {
        set(_errors, 'answers.value', tex('solutions_requires_positive_answer'))
      }
    }
  })

  // no solution at all
  if (grid.solutions.length === 0) {
    _errors.solutions = tex('grid_at_least_one_solution')
  }

  return _errors
}


function hasDuplicates(answers) {
  let hasDuplicates = false
  answers.forEach(answer => {
    let count = 0
    answers.forEach(check => {
      if (answer.text === check.text && answer.caseSensitive === check.caseSensitive) {
        count++
      }
    })
    if (count > 1) hasDuplicates = true
  })

  return hasDuplicates
}

function makeDefaultCell(x, y) {
  return {
    id: makeId(),
    data: '',
    coordinates: [x, y],
    background: '#fff',
    color: '#000',
    choices: [],
    input: false
  }
}

export default {
  component,
  reduce,
  validate
}
