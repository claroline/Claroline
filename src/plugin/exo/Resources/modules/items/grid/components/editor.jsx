import React, {Component} from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'
import Overlay from 'react-bootstrap/lib/Overlay'

import {FormData} from '#/main/app/content/form/containers/data'
import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'
import {constants} from '#/plugin/exo/items/grid/constants'
import {SCORE_SUM} from '#/plugin/exo/quiz/enums'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ColorInput} from '#/main/theme/data/types/color/components/input'
import {KeywordsPopover} from '#/plugin/exo/components/keywords'

import {utils} from '#/plugin/exo/items/grid/utils/utils'
import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {GridItem as GridItemTypes} from '#/plugin/exo/items/grid/prop-types'

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

function hasCell(cells, x, y) {
  let present = false

  cells.forEach(c => {
    if (c.coordinates[0] === x && c.coordinates[1] === y) {
      present = true
    }
  })

  return present
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

function updateCell(grid, property, value, cellId)
{
  const newItem = cloneDeep(grid)
  const cellToUpdate = newItem.cells.find(cell => cell.id === cellId)
  cellToUpdate[property] = value

  if ('_multiple' === property) {
    if (value) {
      const solution = newItem.solutions.find(solution => solution.cellId === cellToUpdate.id)

      cellToUpdate.choices = solution.answers.map(answer => answer.text)
    } else {
      cellToUpdate.choices = []
    }
  }

  return newItem
}

function createSolution(grid, cellId)
{
  const newItem = cloneDeep(grid)
  const cell = newItem.cells.find(cell => cell.id === cellId)

  newItem.solutions.push({
    cellId: cellId,
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

const GridCellPopover = props =>
  <KeywordsPopover
    id={props.id}
    className="cell-popover"
    style={props.style}
    title={trans('grid_edit_cell', {}, 'quiz')}
    keywords={props.solution.answers}
    _errors={props._errors}
    validating={props.validating}
    showCaseSensitive={true}
    showScore={props.hasScore}
    hasExpectedAnswers={props.hasExpectedAnswers}
    _multiple={props._multiple}
    random={props.random}
    close={props.closeSolution}
    remove={props.removeSolution}
    onChange={props.update}
    addKeyword={props.addSolutionAnswer}
    removeKeyword={props.removeSolutionAnswer}
    updateKeyword={props.updateSolutionAnswer}
  />

GridCellPopover.propTypes = {
  id: T.string.isRequired,
  solution: T.shape({
    answers: T.array
  }),
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  _multiple: T.bool.isRequired,
  random: T.bool,
  validating: T.bool.isRequired,
  _errors: T.shape({
    keywords: T.object
  }),
  style: T.object,
  update: T.func.isRequired,
  removeSolution: T.func.isRequired,
  closeSolution: T.func.isRequired,
  addSolutionAnswer: T.func.isRequired,
  updateSolutionAnswer: T.func.isRequired,
  removeSolutionAnswer: T.func.isRequired
}

/**
 * Cell editor.
 * NB : we use a class component because we use `refs` which are not available in functional components.
 */
class GridCell extends Component {
  render() {
    return (
      <td
        className="grid-cell"
        style={{
          color: this.props.cell.color,
          border: `${this.props.border.width}px solid ${this.props.border.color}`,
          backgroundColor: this.props.cell.background
        }}
      >
        <div className="cell-header">
          <div className="cell-actions">
            <ColorInput
              id={`cell-${this.props.cell.id}-font`}
              className="btn-link"
              value={this.props.cell.color}
              colorIcon="fa fa-fw fa-font"
              hideInput={true}
              onChange={color => this.props.update('color', color)}
            />

            <ColorInput
              id={`cell-${this.props.cell.id}-bg`}
              className="btn-link"
              value={this.props.cell.background}
              colorIcon="fa fa-fw fa-fill"
              hideInput={true}
              onChange={color => this.props.update('background', color)}
            />
          </div>

          <div className='cell-actions' ref={element => this.refCellHeader = element}>
            {this.props.solution &&
              <Overlay
                container={this.refCellHeader}
                placement="bottom"
                show={this.props.solutionOpened}
                rootClose={isEmpty(this.props._errors)}
                target={this.refPopover}
                onHide={this.props.closeSolution}
              >
                <GridCellPopover
                  id={`cell-${this.props.cell.id}-popover`}
                  solution={this.props.solution}
                  hasScore={this.props.hasScore}
                  hasExpectedAnswers={this.props.hasExpectedAnswers}
                  validating={this.props.validating}
                  _multiple={this.props.cell._multiple || !isEmpty(this.props.cell.choices)}
                  random={this.props.cell.random}
                  _errors={this.props._errors}
                  update={this.props.update}
                  removeSolution={this.props.removeSolution}
                  closeSolution={this.props.closeSolution}
                  addSolutionAnswer={this.props.addSolutionAnswer}
                  updateSolutionAnswer={this.props.updateSolutionAnswer}
                  removeSolutionAnswer={this.props.removeSolutionAnswer}
                />
              </Overlay>
            }

            <Button
              ref={element => this.refPopover = element}
              id={`cell-${this.props.cell.id}-solution`}
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon={classes('fa fa-fw', {
                'fa-pencil': undefined !== this.props.solution,
                'fa-plus': undefined === this.props.solution
              })}
              label={undefined !== this.props.solution ? trans('grid_edit_solution', {}, 'quiz') : trans('grid_create_solution', {}, 'quiz')}
              callback={
                undefined !== this.props.solution ? this.props.openSolution : this.props.createSolution
              }
              tooltip="top"
            />

            {undefined !== this.props.solution &&
              <Button
                id={`cell-${this.props.cell.id}-delete-solution`}
                className="btn-link"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-trash"
                label={trans('delete', {}, 'actions')}
                callback={this.props.removeSolution}
                tooltip="top"
              />
            }
          </div>
        </div>

        {this.props.solution === undefined &&
          <textarea
            id={`${this.props.cell.id}-data`}
            className="cell-input form-control"
            style={{
              color: this.props.cell.color
            }}
            value={this.props.cell.data}
            onChange={(e) => this.props.update('data', e.target.value)}
          />
        }

        {this.props.solution !== undefined && this.props.cell.choices.length > 0 &&
          <div className="cell-dropdown dropdown">
            <button
              type="button"
              id={`choice-drop-down-${this.props.cell.id}`}
              className="btn dropdown-toggle"
              data-toggle="dropdown"
              aria-haspopup="true"
              aria-expanded="true"
              style={{
                color: this.props.cell.color
              }}
            >
              {trans('grid_choice_select_empty', {}, 'quiz')}&nbsp;
              <span className="caret" />
            </button>
            <ul className="dropdown-menu" aria-labelledby={`choice-drop-down-${this.props.cell.id}`}>
              {this.props.cell.choices.map((choice, index) =>
                <li key={`choice-${index}`}>{choice}</li>
              )}
            </ul>
          </div>
        }

        {this.props.solution !== undefined && this.props.cell.choices.length === 0 &&
          <textarea
            className="cell-input form-control"
            id={`${this.props.cell.id}-data`}
            placeholder={utils.getBestAnswer(this.props.solution.answers)}
            style={{
              color: this.props.cell.color
            }}
          />
        }
      </td>
    )
  }
}

GridCell.propTypes = {
  border: T.shape({
    width: T.number.isRequired,
    color: T.string.isRequired
  }).isRequired,
  cell: T.shape({
    id: T.string.isRequired,
    _multiple: T.bool.isRequired,
    random: T.bool,
    data: T.string.isRequired,
    background: T.string.isRequired,
    color: T.string.isRequired,
    choices: T.arrayOf(T.string),
    input: T.bool.isRequired
  }).isRequired,
  solution: T.shape({
    answers: T.array.isRequired
  }),
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  validating: T.bool.isRequired,
  _errors: T.object,
  solutionOpened: T.bool.isRequired,
  update: T.func.isRequired,
  createSolution: T.func.isRequired,
  removeSolution: T.func.isRequired,
  openSolution: T.func.isRequired,
  closeSolution: T.func.isRequired,
  addSolutionAnswer: T.func.isRequired,
  updateSolutionAnswer: T.func.isRequired,
  removeSolutionAnswer: T.func.isRequired
}

const GridRow = props =>
  <tr>
    {props.cells.map(cell =>
      <GridCell
        key={`grid-cell-${cell.id}`}
        cell={cell}
        border={props.border}
        solution={utils.getSolutionByCellId(cell.id, props.solutions)}
        hasScore={props.score.type === SCORE_SUM && props.sumMode === constants.SUM_CELL}
        hasExpectedAnswers={props.hasExpectedAnswers}
        _errors={get(props, '_errors.'+cell.id)}
        validating={props.validating}
        solutionOpened={props._popover === cell.id}
        update={(property, newValue) => props.updateCell(cell.id, property, newValue)}
        createSolution={() => props.createCellSolution(cell.id)}
        removeSolution={() => props.removeCellSolution(cell.id)}
        openSolution={() => props.openPopover(cell.id)}
        closeSolution={props.closePopover}
        addSolutionAnswer={() => props.addSolutionAnswer(cell.id)}
        updateSolutionAnswer={(keywordId, parameter, value) => props.updateSolutionAnswer(cell.id, keywordId, parameter, value)}
        removeSolutionAnswer={(keywordId) => props.removeSolutionAnswer(cell.id, keywordId)}
      />
    )}

    <td className="row-controls">
      {props.score.type === SCORE_SUM && props.sumMode === constants.SUM_ROW &&
        <input
          type="number"
          min="0"
          step="0.5"
          disabled={!utils.atLeastOneSolutionInRow(props.index, props.cells, props.solutions)}
          value={utils.getRowScore(props.cells, props.solutions)}
          className="form-control grid-score"
          onChange={e => props.updateScore(e.target.value)}
        />
      }

      <Button
        id={`grid-btn-delete-row-${props.index}`}
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-trash"
        label={trans('delete', {}, 'actions')}
        disabled={!props.deletable}
        callback={props.removeRow}
        tooltip="top"
      />
    </td>
  </tr>

GridRow.propTypes = {
  index: T.number.isRequired,
  cells: T.arrayOf(T.shape({
    id: T.string.isRequired
  })).isRequired,
  solutions: T.arrayOf(T.object).isRequired,
  sumMode: T.string,
  score: T.shape({
    type: T.string.isRequired
  }).isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  border: T.shape({
    width: T.number.isRequired,
    color: T.string.isRequired
  }).isRequired,
  deletable: T.bool.isRequired,
  validating: T.bool.isRequired,
  _errors: T.object,
  _popover: T.string,
  removeRow: T.func.isRequired,
  updateScore: T.func.isRequired,
  updateCell: T.func.isRequired,
  createCellSolution: T.func.isRequired,
  removeCellSolution: T.func.isRequired,
  openPopover: T.func.isRequired,
  closePopover: T.func.isRequired,
  addSolutionAnswer: T.func.isRequired,
  updateSolutionAnswer: T.func.isRequired,
  removeSolutionAnswer: T.func.isRequired
}

const GridTable = props =>
  <table className="grid-table">
    <tbody>
      {[...Array(utils.getNbRows(props.item.cells))].map((it, rowIndex) =>
        <GridRow
          key={`grid-row-${rowIndex}`}
          index={rowIndex}
          cells={utils.getCellsByRow(rowIndex, props.item.cells)}
          solutions={props.item.solutions}
          border={props.item.border}
          score={props.item.score}
          hasExpectedAnswers={props.item.hasExpectedAnswers}
          sumMode={props.item.sumMode}
          deletable={utils.getNbRows(props.item.cells) > 1}
          validating={props.validating}
          _errors={props.errors}
          _popover={props.item._popover}
          removeRow={() => props.removeRow(rowIndex)}
          updateScore={(newScore) => {
            const newItem = cloneDeep(props.item)
            const cellsInRow = utils.getCellsByRow(rowIndex, newItem.cells)
            cellsInRow.forEach(cell => {
              const solutionToUpdate = newItem.solutions.find(solution => solution.cellId === cell.id)
              if (undefined !== solutionToUpdate && undefined !== solutionToUpdate.answers && solutionToUpdate.answers.length > 0) {
                solutionToUpdate.answers.forEach(answer => answer.score = parseFloat(newScore))
              }
            })

            props.update('solutions', newItem.solutions)
          }}
          updateCell={(cellId, property, newValue) => {
            const newItem = updateCell(props.item, property, newValue, cellId)
            props.update('cells', newItem.cells)
          }}
          createCellSolution={(cellId) => {
            const newItem = createSolution(props.item, cellId)
            props.update('solutions', newItem.solutions)
          }}
          removeCellSolution={(cellId) => {
            const newItem = cloneDeep(props.item)
            const cell = newItem.cells.find(cell => cell.id === cellId)
            cell.choices = []
            cell.input = false
            const solutionIndex = newItem.solutions.findIndex(solution => solution.cellId === cellId)
            newItem.solutions.splice(solutionIndex, 1)

            // Close popover on solution delete if needed
            if (newItem._popover === cell.id) {
              newItem._popover = null
            }

            props.update('solutions', newItem.solutions)
            props.update('_popover', null)
          }}
          addSolutionAnswer={(cellId) => {
            const newItem = cloneDeep(props.item)
            const cellToUpdate = newItem.cells.find(cell => cell.id === cellId)
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

            props.update('solutions', newItem.solutions)
            props.update('cells', newItem.cells)
          }}

          updateSolutionAnswer={(cellId, keyword, parameter, value) => {
            const newItem = cloneDeep(props.item)
            const cellToUpdate = newItem.cells.find(cell => cell.id === cellId)
            const solution = newItem.solutions.find(solution => cellId === solution.cellId)
            const answer = solution.answers.find(answer => answer._id === keyword)

            answer[parameter] = value

            if ('score' === parameter && SCORE_SUM === newItem.score.type && constants.SUM_CELL === newItem.sumMode) {
              answer.expected = value > 0
            } else if ('expected' === parameter) {
              answer.score = value ? 1 : 0
            }

            if (cellToUpdate._multiple) {
              cellToUpdate.choices = solution.answers.map(answer => answer.text)
            }

            props.update('solutions', newItem.solutions)
            props.update('cells', newItem.cells)
          }}


          removeSolutionAnswer={(cellId, keyword) => {
            const newItem = cloneDeep(props.item)
            const cellToUpdate = newItem.cells.find(cell => cell.id === cellId)
            const solution = newItem.solutions.find(solution => solution.cellId === cellToUpdate.id)
            const answers = solution.answers

            answers.splice(answers.findIndex(answer => answer._id === keyword), 1)

            answers.forEach(keyword => keyword._deletable = answers.length > 1)
            if (cellToUpdate._multiple) {
              cellToUpdate.choices = solution.answers.map(answer => answer.text)
            }

            props.update('solutions', newItem.solutions)
          }}
          openPopover={props.openPopover}
          closePopover={props.closePopover}
        />
      )}

      <tr>
        {[...Array(utils.getNbCols(props.item.cells))].map((it, colIndex) =>
          <td key={`grid-col-${colIndex}-controls`} className="col-controls">
            {props.item.score.type === SCORE_SUM && props.item.sumMode === constants.SUM_COL &&
              <input
                type="number"
                min="0"
                step="0.5"
                disabled={!utils.atLeastOneSolutionInCol(colIndex, props.item.cells, props.item.solutions)}
                value={utils.getColScore(colIndex, props.item.cells, props.item.solutions)}
                className="form-control grid-score"
                onChange={e => {
                  const newItem = cloneDeep(props.item)
                  const cellsInRow = utils.getCellsByCol(colIndex, newItem.cells)
                  cellsInRow.forEach(cell => {
                    const solutionToUpdate = newItem.solutions.find(solution => solution.cellId === cell.id)
                    if (undefined !== solutionToUpdate && undefined !== solutionToUpdate.answers && solutionToUpdate.answers.length > 0) {
                      solutionToUpdate.answers.forEach(answer => answer.score = parseFloat(e.target.value))
                    }
                  })

                  props.update('solutions', newItem.solutions)
                }}
              />
            }

            <Button
              id={`grid-btn-delete-col-${colIndex}`}
              className="btn-link"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-trash"
              label={trans('delete', {}, 'actions')}
              disabled={utils.getNbCols(props.item.cells) <= 1}
              callback={() => props.removeColumn(colIndex)}
              tooltip="top"
            />
          </td>
        )}
        <td />
      </tr>
    </tbody>
  </table>

GridTable.propTypes = {
  item: T.shape({
    sumMode: T.string,
    score: T.shape({
      type: T.string.isRequired
    }).isRequired,
    cells: T.array.isRequired,
    rows: T.number.isRequired,
    cols: T.number.isRequired,
    border:  T.object.isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired,
    _errors: T.object,
    _popover: T.string
  }).isRequired,
  hasScore: T.bool.isRequired,
  validating: T.bool.isRequired,
  removeRow: T.func.isRequired,
  removeColumn: T.func.isRequired,
  openPopover: T.func.isRequired,
  closePopover: T.func.isRequired,
  update: T.func.isRequired,
  errors: T.array
}

const GridEditor = (props) => {
  const decoratedItem = cloneDeep(props.item)
  decoratedItem.solutions.forEach(s => {
    if (s.answers) {
      s.answers.forEach(a => {
        if (a['_id'] === undefined) {
          a['_id'] = makeId()
        }
        a['_deletable'] = 1 < s.answers.length
      })
    }
  })

  const GridComponent =  <div className="grid-body">
    <GridTable
      item={decoratedItem}
      hasScore={props.hasAnswerScores}
      validating={props.validating}
      update={props.update}
      removeRow={(row) => {
        const newItem = cloneDeep(decoratedItem)
        deleteRow(row, newItem, true)
        props.update('cells', newItem.cells)
      }}
      removeColumn={(col) => {
        const newItem = cloneDeep(decoratedItem)
        deleteCol(col, newItem, true)
        props.update('cells', newItem.cells)
      }}
      openPopover={(cellId) => props.update('_popover', cellId)}
      closePopover={() => props.update('_popover', null) }
    />
  </div>
  
  

  return (
    <FormData
      className="grid-editor"
      embedded={true}
      name={props.formName}
      dataPart={props.path}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'sumMode',
              type: 'choice',
              label: trans('grid_score_mode_label', {}, 'quiz'),
              displayed: (item) => item.hasExpectedAnswers && props.hasAnswerScores && item.score.type === SCORE_SUM,
              options: {
                multiple: false,
                condensed: false,
                choices: constants.SUM_MODES
              }
            }, {
              name: 'penalty',
              type: 'number',
              label: trans('editor_penalty_label', {}, 'quiz'),
              displayed: (item) => item.hasExpectedAnswers && props.hasAnswerScores && item.score.type === SCORE_SUM
            }, {
              name: 'rows',
              type: 'number',
              label: trans('grid_table_rows', {}, 'quiz'),
              options: {
                min: 1,
                max: 12
              },
              onChange: (value) => {
                if (value && 12 >= value) {
                  const newItem = cloneDeep(props.item)
                  newItem.rows = parseFloat(value)
                  const newCells = []

                  // remove all cells with row index greater than value
                  newItem.cells.forEach(c => {
                    if (c.coordinates[1] < value) {
                      newCells.push(c)
                    }
                  })

                  // add all missing cells with default cell content
                  for (let i = 0; i < value; ++i) {
                    for (let j = 0; j < newItem.cols; ++j) {
                      if (!hasCell(newCells, j, i)) {
                        newCells.push(makeDefaultCell(j, i))
                      }
                    }
                  }
                  newItem.cells = newCells
                  props.update('cells', newItem.cells)
                }
              }
            }, {
              name: 'cols',
              type: 'number',
              label: trans('grid_table_cols', {}, 'quiz'),
              options: {
                min: 1,
                max: 12
              },
              onChange: (value) => {
                if (value && 12 >= value) {
                  const newItem = cloneDeep(props.item)
                  newItem.cols = parseFloat(value)
                  const newCells = []

                  // remove all cells with col index greater than value
                  newItem.cells.forEach(c => {
                    if (c.coordinates[0] < value) {
                      newCells.push(c)
                    }
                  })

                  // add all missing cells with default cell content
                  for (let i = 0; i < newItem.rows; ++i) {
                    for (let j = 0; j < value; ++j) {
                      if (!hasCell(newCells, j, i)) {
                        newCells.push(makeDefaultCell(j, i))
                      }
                    }
                  }
                  newItem.cells = newCells
                  props.update('cells', newItem.cells)
                }
              }
            }, {
              name: 'border.color',
              type: 'color',
              label: trans('grid_table_border', {}, 'quiz')
            }, {
              name: 'border.width',
              type: 'number',
              label: trans('grid_table_border', {}, 'quiz'),
              options: {
                min: 0,
                max: 6
              }
            }, {
              name: 'solutions',
              required: true,
              component: GridComponent
            }
          ]
        }
      ]}
    />
  )
}

implementPropTypes(GridEditor, ItemEditorTypes, {
  item: T.shape(GridItemTypes.propTypes).isRequired
})

export {
  GridEditor
}
