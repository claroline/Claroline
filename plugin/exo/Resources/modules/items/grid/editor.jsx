import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import Overlay from 'react-bootstrap/lib/Overlay'

import {tex} from '#/main/core/translation'
import {ErrorBlock} from '#/main/core/layout/form/components/error-block.jsx'
import {Radios} from './../../components/form/radios.jsx'
import {SUM_CELL, SUM_COL, SUM_ROW, actions} from './editor'
import {SCORE_SUM, SCORE_FIXED} from './../../quiz/enums'
import {FormGroup} from '#/main/core/layout/form/components/form-group.jsx'
import {TooltipButton} from './../../components/form/tooltip-button.jsx'
import {ColorPicker} from './../../components/form/color-picker.jsx'
import {utils} from './utils/utils'
import {KeywordsPopover} from './../components/keywords.jsx'

const GridCellPopover = props =>
  <KeywordsPopover
    id={props.id}
    className="cell-popover"
    style={props.style}
    title={tex('grid_edit_cell')}
    keywords={props.solution.answers}
    _errors={props._errors}
    validating={props.validating}
    showCaseSensitive={true}
    showScore={props.hasScore}
    _multiple={props._multiple}
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
  _multiple: T.bool.isRequired,
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
            <ColorPicker
              className="btn-link-default"
              color={this.props.cell.color}
              forFontColor={true}
              onPick={color => this.props.update('color', color.hex)}
            />

            <ColorPicker
              className="btn-link-default"
              color={this.props.cell.background}
              onPick={color => this.props.update('background', color.hex)}
            />
          </div>

          <div className="cell-actions" ref="cellHeader">
            {this.props.solution &&
              <Overlay
                container={this.refs.cellHeader}
                placement="bottom"
                show={this.props.solutionOpened}
                rootClose={isEmpty(this.props._errors)}
                target={this.refs.popoverToggle}
                onHide={this.props.closeSolution}
              >
                <GridCellPopover
                  id={`cell-${this.props.cell.id}-popover`}
                  solution={this.props.solution}
                  hasScore={this.props.hasScore}
                  validating={this.props.validating}
                  _multiple={this.props.cell._multiple}
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

            <TooltipButton
              ref="popoverToggle"
              id={`cell-${this.props.cell.id}-solution`}
              title={undefined !== this.props.solution ? tex('grid_edit_solution') : tex('grid_create_solution')}
              className="btn-link-default"
              label={
                <span
                  className={classes('fa fa-fw', {
                    'fa-pencil': undefined !== this.props.solution,
                    'fa-plus': undefined === this.props.solution
                  })}
                />
              }
              onClick={
                undefined !== this.props.solution ? this.props.openSolution : this.props.createSolution
              }
            />

            {undefined !== this.props.solution &&
              <TooltipButton
                id={`cell-${this.props.cell.id}-delete-solution`}
                className="btn-link-default"
                title={tex('delete')}
                label={<span className="fa fa-fw fa-trash-o" />}
                onClick={this.props.removeSolution}
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
              {tex('grid_choice_select_empty')}&nbsp;
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
        hasScore={props.score.type === SCORE_SUM && props.sumMode === SUM_CELL}
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
      {props.score.type === SCORE_SUM && props.sumMode === SUM_ROW &&
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

      <TooltipButton
        id={`grid-btn-delete-row-${props.index}`}
        className="btn-link-default"
        title={tex('delete')}
        label={<span className="fa fa-fw fa-trash-o" />}
        enabled={props.deletable}
        onClick={props.removeRow}
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
      {[...Array(props.item.rows)].map((it, rowIndex) =>
        <GridRow
          key={`grid-row-${rowIndex}`}
          index={rowIndex}
          cells={utils.getCellsByRow(rowIndex, props.item.cells)}
          solutions={props.item.solutions}
          border={props.item.border}
          score={props.item.score}
          sumMode={props.item.sumMode}
          deletable={props.item.rows > 1}
          validating={props.validating}
          _errors={props.item._errors}
          _popover={props.item._popover}
          removeRow={() => props.removeRow(rowIndex)}
          updateScore={(newScore) => props.onChange(
            actions.updateRowScore(rowIndex, newScore)
          )}
          updateCell={(cellId, property, newValue) => props.onChange(
            actions.updateCell(cellId, property, newValue)
          )}
          createCellSolution={(cellId) => props.onChange(
            actions.createCellSolution(cellId)
          )}
          removeCellSolution={(cellId) => props.onChange(
            actions.deleteCellSolution(cellId)
          )}
          addSolutionAnswer={(cellId) => props.onChange(
            actions.addSolutionAnswer(cellId)
          )}
          updateSolutionAnswer={(cellId, keyword, parameter, value) => props.onChange(
            actions.updateSolutionAnswer(cellId, keyword, parameter, value)
          )}
          removeSolutionAnswer={(cellId, keyword) => props.onChange(
            actions.removeSolutionAnswer(cellId, keyword)
          )}
          openPopover={props.openPopover}
          closePopover={props.closePopover}
        />
      )}

      <tr>
        {[...Array(props.item.cols)].map((it, colIndex) =>
          <td key={`grid-col-${colIndex}-controls`} className="col-controls">
            {props.item.score.type === SCORE_SUM && props.item.sumMode === SUM_COL &&
              <input
                type="number"
                min="0"
                step="0.5"
                disabled={!utils.atLeastOneSolutionInCol(colIndex, props.item.cells, props.item.solutions)}
                value={utils.getColScore(colIndex, props.item.cells, props.item.solutions)}
                className="form-control grid-score"
                onChange={e => props.onChange(
                  actions.updateColumnScore(colIndex, e.target.value)
                )}
              />
            }

            <TooltipButton
              id={`grid-btn-delete-col-${colIndex}`}
              className="btn-link-default"
              title={tex('delete')}
              label={<span className="fa fa-fw fa-trash-o" />}
              enabled={props.item.cols > 1}
              onClick={() => props.removeColumn(colIndex)}
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
    _errors: T.object,
    _popover: T.string
  }).isRequired,
  validating: T.bool.isRequired,
  removeRow: T.func.isRequired,
  removeColumn: T.func.isRequired,
  openPopover: T.func.isRequired,
  closePopover: T.func.isRequired
}

const Grid = props =>
  <div className="grid-editor">
    <div className="form-group">
      <label htmlFor="grid-score-mode">{tex('grid_score_mode_label')}</label>
      <Radios
        id="grid-score-mode"
        groupName="scoreMode"
        options={[
          {value: SUM_CELL, label:tex('grid_score_sum_cell')},
          {value: SUM_COL, label:tex('grid_score_sum_col')},
          {value: SUM_ROW, label:tex('grid_score_sum_row')},
          {value: SCORE_FIXED, label:tex('fixed_score')}
        ]}
        checkedValue={props.item.score.type === SCORE_FIXED ? SCORE_FIXED : props.item.sumMode}
        onChange={value => props.onChange(
          actions.updateProperty('sumMode', value)
        )}
        inline={false}
      />

      {props.item.score.type === SCORE_SUM &&
        <div className="sub-fields">
          <label htmlFor="grid-penalty">{tex('grid_editor_penalty_label')}</label>
          <input
            id="grid-penalty"
            className="form-control"
            value={props.item.penalty}
            type="number"
            min="0"
            onChange={e => props.onChange(
              actions.updateProperty('penalty', e.target.value)
            )}
          />
        </div>
      }
      {props.item.score.type === SCORE_FIXED &&
        <div className="sub-fields">
          <FormGroup
            controlId={`item-${props.item.id}-fixedSuccess`}
            label={tex('fixed_score_on_success')}
            error={get(props.item, '_errors.score.success')}
            warnOnly={!props.validating}
          >
            <input
              id={`item-${props.item.id}-fixedSuccess`}
              type="number"
              min="0"
              step="0.5"
              value={props.item.score.success}
              className="form-control"
              onChange={e => props.onChange(
                actions.updateProperty('scoreSuccess', e.target.value)
              )}
            />
          </FormGroup>
          <FormGroup
            controlId={`item-${props.item.id}-fixedFailure`}
            label={tex('fixed_score_on_failure')}
            error={get(props.item, '_errors.score.failure')}
            warnOnly={!props.validating}
          >
            <input
              id={`item-${props.item.id}-fixedFailure`}
              type="number"
              value={props.item.score.failure}
              className="form-control"
              onChange={e => props.onChange(
                actions.updateProperty('scoreFailure', e.target.value)
              )}
            />
          </FormGroup>
        </div>
      }
    </div>

    <hr />

    <FormGroup
      className="grid-size"
      controlId={`table-${props.item.id}-rows`}
      label={tex('grid_table_size')}
    >
      <div className="row">
        <div className="col-md-6 col-sm-6 col-xs-6">
          <div className="input-group">
            <input
              id={`table-${props.item.id}-rows`}
              type="number"
              min="1"
              max="12"
              value={props.item.rows}
              className="form-control"
              onChange={e => props.onChange(
                actions.updateProperty('rows', e.target.value)
              )}
            />
            <span className="input-group-addon">{tex('grid_table_rows')}</span>
          </div>
        </div>

        <div className="col-md-6 col-sm-6 col-xs-6">
          <div className="input-group">
            <input
              id={`table-${props.item.id}-cols`}
              type="number"
              min="1"
              max="12"
              value={props.item.cols}
              className="form-control"
              onChange={e => props.onChange(
                actions.updateProperty('cols', e.target.value)
              )}
            />
            <span className="input-group-addon">{tex('grid_table_cols')}</span>
          </div>
        </div>
      </div>
    </FormGroup>

    <FormGroup
      controlId={`table-${props.item.id}-border-width`}
      label={tex('grid_table_border')}
    >
      <div className="input-group">
        <span className="input-group-btn">
          <ColorPicker
            className="btn-default"
            color={props.item.border.color}
            onPick={color => props.onChange(
              actions.updateProperty('borderColor', color.hex)
            )}
          />
        </span>

        <input
          id={`table-${props.item.id}-border-width`}
          type="number"
          min="0"
          max="6"
          value={props.item.border.width}
          className="form-control"
          onChange={e => props.onChange(
            actions.updateProperty('borderWidth', e.target.value)
          )}
        />
      </div>
    </FormGroup>

    {get(props.item, '_errors.solutions') &&
      <ErrorBlock text={props.item._errors.solutions} warnOnly={!props.validating} />
    }

    <div className="grid-body">
      <GridTable
        item={props.item}
        validating={props.validating}
        onChange={props.onChange}
        removeRow={(row) => props.onChange(
          actions.deleteRow(row)
        )}
        removeColumn={(col) => props.onChange(
          actions.deleteColumn(col)
        )}
        openPopover={(cellId) => props.onChange(
          actions.openCellPopover(cellId)
        )}
        closePopover={() => props.onChange(
          actions.closeCellPopover()
        )}
      />
    </div>
  </div>

Grid.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    penalty: T.number.isRequired,
    sumMode: T.string,
    score: T.shape({
      type: T.string.isRequired,
      success: T.number.isRequired,
      failure: T.number.isRequired
    }),
    cells: T.arrayOf(T.shape({
      id: T.string.isRequired,
      data: T.string.isRequired,
      coordinates: T.arrayOf(T.number).isRequired,
      background: T.string.isRequired,
      color: T.string.isRequired,
      choices: T.arrayOf(T.string),
      input: T.bool.isRequired
    })).isRequired,
    rows: T.number.isRequired,
    cols: T.number.isRequired,
    border:  T.shape({
      width: T.number.isRequired,
      color: T.string.isRequired
    }).isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    _errors: T.object,
    _popover: T.string
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

export {Grid}
