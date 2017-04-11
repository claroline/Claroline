import cloneDeep from 'lodash/cloneDeep'
import invariant from 'invariant'
import isEmpty from 'lodash/isEmpty'

import {keywords as keywordsUtils} from './../../utils/keywords'
import {ITEM_CREATE} from './../../quiz/editor/actions'
import {SCORE_SUM, SCORE_FIXED} from './../../quiz/enums'
import {makeActionCreator} from '#/main/core/utilities/redux'
import {makeId} from './../../utils/utils'
import {tex} from '#/main/core/translation'
import {Grid as component} from './editor.jsx'
import {utils} from './utils/utils'

const UPDATE_PROP = 'UPDATE_PROP'
const DELETE_COLUMN = 'DELETE_COLUMN'
const DELETE_ROW = 'DELETE_ROW'
const UPDATE_COLUMN_SCORE = 'UPDATE_COLUMN_SCORE'
const UPDATE_ROW_SCORE = 'UPDATE_ROW_SCORE'
const UPDATE_CELL = 'UPDATE_CELL'
const CREATE_CELL_SOLUTION = 'CREATE_CELL_SOLUTION'
const DELETE_CELL_SOLUTION = 'DELETE_CELL_SOLUTION'
const OPEN_CELL_POPOVER = 'OPEN_CELL_POPOVER'
const CLOSE_CELL_POPOVER = 'CLOSE_CELL_POPOVER'
const ADD_SOLUTION_ANSWER = 'ADD_SOLUTION_ANSWER'
const UPDATE_SOLUTION_ANSWER = 'UPDATE_SOLUTION_ANSWER'
const REMOVE_SOLUTION_ANSWER = 'REMOVE_SOLUTION_ANSWER'

export const SUM_CELL = 'cell'
export const SUM_COL = 'col'
export const SUM_ROW = 'row'

export const actions = {
  updateProperty: makeActionCreator(UPDATE_PROP, 'property', 'value'),
  deleteColumn: makeActionCreator(DELETE_COLUMN, 'index'),
  deleteRow: makeActionCreator(DELETE_ROW, 'index'),
  updateColumnScore: makeActionCreator(UPDATE_COLUMN_SCORE, 'index', 'score'),
  updateRowScore: makeActionCreator(UPDATE_ROW_SCORE, 'index', 'score'),
  updateCell: makeActionCreator(UPDATE_CELL, 'cellId', 'property', 'value'),
  createCellSolution: makeActionCreator(CREATE_CELL_SOLUTION, 'cellId'),
  deleteCellSolution: makeActionCreator(DELETE_CELL_SOLUTION, 'cellId'),
  openCellPopover: makeActionCreator(OPEN_CELL_POPOVER, 'cellId'),
  closeCellPopover: makeActionCreator(CLOSE_CELL_POPOVER),
  addSolutionAnswer: makeActionCreator(ADD_SOLUTION_ANSWER, 'cellId'),
  removeSolutionAnswer: makeActionCreator(REMOVE_SOLUTION_ANSWER, 'cellId', 'keywordId'),
  updateSolutionAnswer: (cellId, keywordId, parameter, value) => {
    invariant(
      ['text', 'caseSensitive', 'expected', 'feedback', 'score'].indexOf(parameter) > -1,
      'answer attribute is not valid'
    )
    invariant(cellId !== undefined, 'cellId is required')

    return {
      type: UPDATE_SOLUTION_ANSWER,
      cellId, keywordId, parameter, value
    }
  }
}

function decorate(item) {
  return Object.assign({}, item, {
    solutions: item.solutions.map(solution => Object.assign({}, solution, {
      answers: solution.answers.map(keyword => Object.assign({}, keyword, {
        _id: makeId(),
        _deletable: solution.answers.length > 1
      }))
    }))
  })
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
          color: '#DDDDDD',
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

    case UPDATE_CELL: {
      const newItem = cloneDeep(grid)
      const cellToUpdate = newItem.cells.find(cell => cell.id === action.cellId)
      cellToUpdate[action.property] = action.value

      if ('_multiple' === action.property) {
        if (action.value) {
          const solution = newItem.solutions.find(solution => solution.cellId === cellToUpdate.id)

          cellToUpdate.choices = solution.answers.map(answer => answer.text)
        } else {
          cellToUpdate.choices = []
        }
      }

      return newItem
    }

    case CREATE_CELL_SOLUTION: {
      const newItem = cloneDeep(grid)
      const cell = newItem.cells.find(cell => cell.id === action.cellId)

      newItem.solutions.push({
        cellId: action.cellId,
        answers: [
          {
            _id: makeId(),
            text: cell.data ? cell.data : '',
            score: 1,
            caseSensitive: false,
            feedback: '',
            expected: true,
            _deletable: false
          }
        ]
      })

      // ensure cell data is empty
      cell.data = ''
      cell.choices = []
      cell.input = true

      // automatically open popover for the new solution
      newItem._popover = cell.id

      return newItem
    }

    case DELETE_CELL_SOLUTION: {
      const newItem = cloneDeep(grid)
      const cell = newItem.cells.find(cell => cell.id === action.cellId)
      cell.choices = []
      cell.input = false
      const solutionIndex = newItem.solutions.findIndex(solution => solution.cellId === action.cellId)
      newItem.solutions.splice(solutionIndex, 1)

      // Close popover on solution delete if needed
      if (newItem._popover === cell.id) {
        newItem._popover = null
      }

      return newItem
    }

    case CLOSE_CELL_POPOVER: {
      return Object.assign({}, grid, {
        _popover: null
      })
    }

    case OPEN_CELL_POPOVER: {
      return Object.assign({}, grid, {
        _popover: action.cellId
      })
    }

    case ADD_SOLUTION_ANSWER: {
      const newItem = cloneDeep(grid)
      const cellToUpdate = newItem.cells.find(cell => cell.id === action.cellId)
      const solution = newItem.solutions.find(solution => solution.cellId === cellToUpdate.id)

      solution.answers.push({
        _id: makeId(),
        text: '',
        caseSensitive: false,
        feedback: '',
        score: 1,
        expected: true,
        _deletable: solution.answers.length > 0
      })

      if (cellToUpdate._multiple) {
        cellToUpdate.choices = solution.answers.map(answer => answer.text)
      }

      return newItem
    }

    case UPDATE_SOLUTION_ANSWER: {
      const newItem = cloneDeep(grid)
      const cellToUpdate = newItem.cells.find(cell => cell.id === action.cellId)
      const solution = newItem.solutions.find(solution => solution.cellId === cellToUpdate.id)
      const answer = solution.answers.find(answer => answer._id === action.keywordId)

      answer[action.parameter] = action.value

      if ('score' === action.parameter && SCORE_SUM === newItem.score.type && SUM_CELL === newItem.sumMode) {
        answer.expected = action.value > 0
      } else if ('expected' === action.parameter) {
        answer.score = action.value ? 1 : 0
      }

      if (cellToUpdate._multiple) {
        cellToUpdate.choices = solution.answers.map(answer => answer.text)
      }

      return newItem
    }

    case REMOVE_SOLUTION_ANSWER: {
      const newItem = cloneDeep(grid)
      const cellToUpdate = newItem.cells.find(cell => cell.id === action.cellId)
      const solution = newItem.solutions.find(solution => solution.cellId === cellToUpdate.id)
      const answers = solution.answers
      answers.splice(answers.findIndex(answer => answer._id === action.keywordId), 1)

      answers.forEach(keyword => keyword._deletable = answers.length > 1)
      if (cellToUpdate._multiple) {
        cellToUpdate.choices = solution.answers.map(answer => answer.text)
      }

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

  if (grid.solutions.length === 0) {
    // no solution at all
    _errors.solutions = tex('grid_at_least_one_solution')
  } else {
    grid.cells.forEach(cell => {
      const solution = grid.solutions.find(solution => solution.cellId === cell.id)
      if (solution) {
        const cellErrors = {}

        const keywordsErrors = keywordsUtils.validate(solution.answers, SCORE_SUM === grid.score.type && SUM_CELL === grid.sumMode, cell._multiple ? 2 : 1)
        if (!isEmpty(keywordsErrors)) {
          cellErrors.keywords = keywordsErrors
        }

        if (!isEmpty(cellErrors)) {
          _errors[cell.id] = cellErrors
        }
      }
    })
  }

  return _errors
}

function makeDefaultCell(x, y) {
  return {
    id: makeId(),
    data: '',
    coordinates: [x, y],
    background: '#fff',
    color: '#333',
    _multiple: false,
    choices: [],
    input: false
  }
}

export default {
  component,
  reduce,
  decorate,
  validate
}
