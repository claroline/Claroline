import React, {Component, PropTypes as T} from 'react'
import get from 'lodash/get'
import cloneDeep from 'lodash/cloneDeep'
import classes from 'classnames'
import Popover from 'react-bootstrap/lib/Popover'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import {tex} from './../../utils/translate'
import {ErrorBlock} from './../../components/form/error-block.jsx'
import {Radios} from './../../components/form/radios.jsx'
import {SUM_CELL, SUM_COL, SUM_ROW, actions} from './editor'
import {SCORE_SUM, SCORE_FIXED} from './../../quiz/enums'
import {FormGroup} from './../../components/form/form-group.jsx'
import {Textarea} from './../../components/form/textarea.jsx'
import {TooltipButton} from './../../components/form/tooltip-button.jsx'
import {TooltipElement} from './../../components/form/tooltip-element.jsx'
import {ColorPicker} from './../../components/form/color-picker.jsx'
import {utils} from './utils/utils'

class Keyword extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showFeedback: false
    }
  }

  render() {
    return (
      <div  className={classes(
          'keyword',
          utils.getKeywordPositiveNegativeClass(this.props.score.type, this.props.sumMode, this.props.keyword)
        )}>
        <div className="row">
          { this.props.score.type === SCORE_SUM && this.props.sumMode === SUM_CELL &&
            <span>
              <div className="col-xs-5">
                <input
                  type="text"
                  className="form-control keyword-text"
                  value={this.props.keyword.text}
                  onChange={e => this.props.update(this.props.index, 'text', e.target.value)}
                />
              </div>
              <div className="col-xs-1">
                <TooltipElement
                  id={`tooltip-${this.props.index}-keyword-case-sensitive`}
                  tip={tex('case_sensitive')}>
                  <input
                    className="case-sensitive"
                    type="checkbox"
                    checked={this.props.keyword.caseSensitive}
                    onChange={e => this.props.update(this.props.index, 'caseSensitive', e.target.checked)}
                  />
                </TooltipElement>
              </div>
              <div className="col-xs-3">
                <input
                  type="number"
                  className="form-control score"
                  value={this.props.keyword.score}
                  onChange={e => this.props.update(this.props.index, 'score', e.target.value)}
                />
              </div>
            </span>
          }
          { (this.props.score.type === SCORE_FIXED || (this.props.score.type === SCORE_SUM && this.props.sumMode !== SUM_CELL)) &&
            <span>
              <div className="col-xs-3">
                <TooltipElement
                  id={`tooltip-${this.props.index}-keyword-expected`}
                  tip={tex('grid_expected_keyword')}>
                  <input
                    className="expected"
                    type="checkbox"
                    checked={this.props.keyword.expected}
                    onChange={e => this.props.update(this.props.index, 'expected', e.target.checked)}
                  />
                </TooltipElement>
              </div>
              <div className="col-xs-5">
                <input
                  id
                  type="text"
                  className="form-control keyword-text"
                  value={this.props.keyword.text}
                  onChange={e => this.props.update(this.props.index, 'text', e.target.value)}
                />
              </div>
              <div className="col-xs-1">
                <TooltipElement
                  id={`tooltip-${this.props.index}-keyword-case-sensitive`}
                  tip={tex('case_sensitive')}>
                  <input
                    className="case-sensitive"
                    type="checkbox"
                    checked={this.props.keyword.caseSensitive}
                    onChange={e => this.props.update(this.props.index, 'caseSensitive', e.target.checked)}
                  />
                </TooltipElement>
              </div>
            </span>
          }
          <div className="col-xs-3">
            <TooltipButton
              id={`grid-${this.props.index}-feedback-toggle`}
              className="fa fa-comments-o"
              title={tex('grid_feedback_info')}
              onClick={() => this.setState({showFeedback: !this.state.showFeedback})}
            />
            <TooltipButton
              id={`keyword-${this.props.index}-delete`}
              className="fa fa-trash-o"
              enabled={this.props.deletable}
              title={tex('delete')}
              onClick={() => this.props.remove(this.props.index)}
            />
          </div>
        </div>
        {this.state.showFeedback &&
          <div className="feedback-container">
            <Textarea
              id={`keyword-${this.props.index}-feedback`}
              title={tex('feedback')}
              content={this.props.keyword.feedback}
              onChange={value => this.props.update(this.props.index, 'feedback', value)}
            />
          </div>
        }
      </div>
    )
  }
}

Keyword.propTypes = {
  keyword: T.object.isRequired,
  update: T.func.isRequired,
  index: T.number.isRequired,
  deletable: T.bool.isRequired,
  remove: T.func.isRequired,
  score: T.object,
  sumMode: T.string
}

class PopoverBody extends Component {
  constructor(props) {
    super(props)

    const solution =  Object.assign(
      {},
      {cellId: props.solution ? props.solution.cellId : props.cell.id},
      {answers: props.solution ? props.solution.answers : [
        {
          text: props.cell.data,
          score: 1,
          caseSensitive: false,
          feedback: '',
          expected: true
        }
      ]}
    )

    this.updateKeyword = this.updateKeyword.bind(this)
    this.addAnswer = this.addAnswer.bind(this)
    this.removeAnswer = this.removeAnswer.bind(this)
    this.switchListMode = this.switchListMode.bind(this)

    this.state = {
      solution: solution,
      isList : this.props.cell.choices.length > 0
    }
  }
  // update store with state solution
  componentDidMount() {
    this.props.onUpdate(this.state)
  }

  updateKeyword(index, property, value) {
    let updated = cloneDeep(this.state.solution)
    switch(property) {
      case 'caseSensitive': {
        updated.answers[index].caseSensitive = Boolean(value)
        break
      }
      case 'expected': {
        updated.answers[index].expected = Boolean(value)
        break
      }
      case 'score': {
        updated.answers[index].score = parseFloat(value)
        break
      }
      case 'text': {
        updated.answers[index].text = value
        break
      }
      case 'feedback': {
        updated.answers[index].feedback = value
        break
      }
    }

    this.setState({solution:updated}, () => {
      this.props.onUpdate(this.state)
    })
  }

  switchListMode(value) {
    this.setState({isList:value}, () => {
      this.props.onUpdate(this.state)
    })
  }

  addAnswer() {
    const solution = cloneDeep(this.state.solution)
    solution.answers.push({
      text: '',
      score: 1,
      caseSensitive: false,
      feedback: '',
      expected: false
    })
    this.setState({solution:solution})
  }

  removeAnswer(index) {
    const solution =  cloneDeep(this.state.solution)
    solution.answers.splice(index, 1)
    this.setState({solution:solution})
  }

  render() {
    return (
      <div className="popover-body">
        {get(this.props, 'errors.answers.text') &&
          <ErrorBlock text={this.props.errors.answers.text} warnOnly={!this.props.validating}/>
        }
        {get(this.props, 'errors.answers.duplicate') &&
          <ErrorBlock text={this.props.errors.answers.duplicate} warnOnly={!this.props.validating}/>
        }
        {get(this.props, 'errors.answers.value') &&
          <ErrorBlock text={this.props.errors.answers.value} warnOnly={!this.props.validating}/>
        }
        <div className="checkbox">
          <label>
            <input
              type="checkbox"
              disabled={this.state.solution.answers.length <= 1}
              checked={this.state.isList}
              onChange={e => this.switchListMode(e.target.checked)}
            />
            {tex('grid_keyword_is_list')}
          </label>
        </div>
        <div className="row">
          { this.props.score.type === SCORE_SUM && this.props.sumMode === SUM_CELL &&
            <span>
              <label className="col-xs-5">{tex('grid_keyword')}</label>
              <label className="col-xs-1"></label>
              <label className="col-xs-6">{tex('grid_keyword_score')}</label>
            </span>
          }
          { (this.props.score.type === SCORE_FIXED || (this.props.score.type === SCORE_SUM && this.props.sumMode !== SUM_CELL)) &&
            <span>
              <label className="col-xs-3"></label>
              <label className="col-xs-5">{tex('grid_keyword')}</label>
              <label className="col-xs-4"></label>
            </span>
          }
        </div>
        <div className="keywords">

          { this.state.solution.answers.map((keyword, index) =>
            <Keyword
              deletable={this.state.solution.answers.length > 1}
              key={`keyword-${this.props.cell.id}-${index}`}
              keyword={keyword}
              index={index}
              update={this.updateKeyword}
              remove={this.removeAnswer}
              score={this.props.score}
              sumMode={this.props.sumMode}/>
          )}
        </div>
        <button
          className="btn btn-default"
          onClick={this.addAnswer}>
          <span className="fa fa-plus"></span>&nbsp;{tex('grid_add_keyword')}
        </button>
      </div>
    )
  }
}

PopoverBody.propTypes = {
  cell: T.object.isRequired,
  solution: T.object,
  onUpdate: T.func.isRequired,
  validating: T.bool.isRequired,
  errors: T.object,
  score: T.object,
  sumMode: T.string
}

class GridCell extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showPopover: false,
      target: null
    }

    this.updateSolution = this.updateSolution.bind(this)
    this.hidePopover = this.hidePopover.bind(this)
  }

  // callback used in popover body component
  updateSolution(data) {
    // add / update solution
    this.props.onChange(
      actions.addOrUpdateSolution(data)
    )
  }

  hidePopover() {
    this.refs.overlay.hide()
    this.props.onPopoverHide()
  }

  render() {
    const currentSolution = utils.getSolutionByCellId(this.props.cell.id, this.props.solutions)
    return (
      <div className="grid-cell">
        <div className="cell-header">
          <TooltipButton
            id={`cell-${this.props.cell.id}-delete-solution`}
            className="fa fa-trash"
            title={tex('delete')}
            position="bottom"
            enabled={undefined !== currentSolution}
            onClick={() => this.props.onChange(
              actions.deleteSolution(this.props.cell.id),
              this.hidePopover()
            )}
          />
          <OverlayTrigger
            rootClose={false}
            placement="bottom"
            ref="overlay"
            onEntered={this.props.onPopoverShow}
            overlay={
              <Popover
                className="grid-editor-popover"
                id={`cell-${this.props.cell.id}-solution-popover`}
                title={
                  <div>
                    <div className="pull-right">
                      <TooltipButton
                        id={`cell-${this.props.cell.id}-solution-popover-delete`}
                        title={'delete'}
                        enabled={utils.isValidSolution(currentSolution) && !utils.hasDuplicates(currentSolution)}
                        className="btn-sm fa fa-trash"
                        onClick={() => this.props.onChange(
                          actions.deleteSolution(this.props.cell.id),
                          this.hidePopover()
                        )}
                      />
                      <TooltipButton
                        id={`cell-${this.props.cell.id}-solution-popover-close`}
                        title={'close'}
                        enabled={utils.isValidSolution(currentSolution) && !utils.hasDuplicates(currentSolution)}
                        className="btn-sm fa fa-close"
                        onClick={() => this.hidePopover()}
                      />
                    </div>
                  </div>
                }>
                  <PopoverBody
                    onUpdate={this.updateSolution}
                    cell={this.props.cell}
                    solution={currentSolution}
                    errors={this.props.errors}
                    validating={this.props.validating}
                    score={this.props.score}
                    sumMode={this.props.sumMode}/>
              </Popover>
            }
            trigger={'click'}
          >
            <TooltipButton
              id={`cell-${this.props.cell.id}-edit-solution`}
              title={tex('grid_create_or_edit_solution')}
              className="fa fa-pencil"
              enabled={this.props.popoverEnabled}
              onClick={(e) =>
                this.setState({target: e.target, showPopover: !this.state.showPopover})
              }
            />
          </OverlayTrigger>
          <div className="picker-container">
            <ColorPicker
              color={this.props.cell.color}
              forFontColor={true}
              onPick={color => this.props.onChange(
                  actions.updateCellStyle(this.props.cell.id, 'color', color.hex)
              )}
            />
          </div>
          <div className="picker-container">
            <ColorPicker
              color={this.props.cell.background}
              onPick={color => this.props.onChange(
                  actions.updateCellStyle(this.props.cell.id, 'background', color.hex)
              )}
            />
          </div>
        </div>
        <div className="cell-body" style={{backgroundColor:this.props.cell.background}}>
          {currentSolution === undefined &&
            <textarea
              className="form-control"
              onChange={(e) => this.props.onChange(
                actions.updateCellData(this.props.cell.id, e.target.value)
              )}
              id={`${this.props.cell.id}-data`}
              value={this.props.cell.data}
              style={{color:this.props.cell.color}}
            />
          }
          {currentSolution !== undefined && this.props.cell.choices.length > 0 &&
            <div className="dropdown">
              <button className="btn btn-default dropdown-toggle" type="button" id={`choice-drop-down-${this.props.cell.id}`} data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                {tex('grid_choice_select_empty')}&nbsp;
                <span className="caret"></span>
              </button>
              <ul className="dropdown-menu" aria-labelledby={`choice-drop-down-${this.props.cell.id}`}>
                {this.props.cell.choices.map((choice, index) =>
                  <li key={`choice-${index}`}><a style={{color:this.props.cell.color}} href="#">{choice}</a></li>
                )}
              </ul>
            </div>
          }
          {currentSolution !== undefined && this.props.cell.choices.length === 0 &&
            <input
              type="text"
              className="form-control"
              id={`${this.props.cell.id}-data`}
              value=""
              placeholder={utils.getBestAnswer(currentSolution.answers)}
              style={{color:this.props.cell.color}}
            />
          }
        </div>
      </div>
    )
  }
}

GridCell.propTypes = {
  cell: T.object.isRequired,
  solutions: T.arrayOf(T.object).isRequired,
  onChange: T.func.isRequired,
  onPopoverHide: T.func.isRequired,
  onPopoverShow: T.func.isRequired,
  popoverEnabled: T.bool.isRequired,
  score: T.object,
  sumMode: T.string,
  errors: T.object,
  validating: T.bool.isRequired
}

class Grid extends Component {

  constructor(props) {
    super(props)
    this.state = {
      isEditingCell: false
    }
    this.onPopoverShow = this.onPopoverShow.bind(this)
    this.onPopoverHide = this.onPopoverHide.bind(this)
  }

  // only one popover can be opened at once ie disable every "pencil" icons
  onPopoverShow() {
    this.setState({isEditingCell: true})
  }

  // only one popover can be opened at once ie enable every "pencil" icons
  onPopoverHide() {
    this.setState({isEditingCell: false})
  }

  render() {

    const borderStyle = {
      border: `${this.props.item.border.width}px solid ${this.props.item.border.color}`
    }

    return (
      <div className="grid-editor">
        {get(this.props.item, '_errors.solutions') &&
          <ErrorBlock text={this.props.item._errors.solutions} warnOnly={!this.props.validating}/>
        }
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
            checkedValue={this.props.item.score.type === SCORE_FIXED ? SCORE_FIXED : this.props.item.sumMode}
            onChange={value => this.props.onChange(
              actions.updateProperty('sumMode', value)
            )}
            inline={false}
          />
          {this.props.item.score.type === SCORE_SUM &&
            <div className="form-group">
              <label htmlFor="grid-penalty">{tex('grid_editor_penalty_label')}</label>
              <input
                id="grid-penalty"
                className="form-control"
                value={this.props.item.penalty}
                type="number"
                min="0"
                onChange={e => this.props.onChange(
                   actions.updateProperty('penalty', e.target.value)
                )}
              />
            </div>
          }
          {this.props.item.score.type === SCORE_FIXED &&
              <div className="sub-fields">
                <FormGroup
                  controlId={`item-${this.props.item.id}-fixedSuccess`}
                  label={tex('fixed_score_on_success')}
                  error={get(this.props.item, '_errors.score.success')}
                  warnOnly={!this.props.validating}
                >
                  <input
                    id={`item-${this.props.item.id}-fixedSuccess`}
                    type="number"
                    min="0"
                    value={this.props.item.score.success}
                    className="form-control"
                    onChange={e => this.props.onChange(
                      actions.updateProperty('scoreSuccess', e.target.value)
                    )}
                  />
                </FormGroup>
                <FormGroup
                  controlId={`item-${this.props.item.id}-fixedFailure`}
                  label={tex('fixed_score_on_failure')}
                  error={get(this.props.item, '_errors.score.failure')}
                  warnOnly={!this.props.validating}
                >
                  <input
                    id={`item-${this.props.item.id}-fixedFailure`}
                    type="number"
                    value={this.props.item.score.failure}
                    className="form-control"
                    onChange={e => this.props.onChange(
                      actions.updateProperty('scoreFailure', e.target.value)
                    )}
                  />
                </FormGroup>
              </div>
            }
        </div>
        <hr/>
        <div className="form-inline table-options">
          <FormGroup
            controlId={`table-${this.props.item.id}-rows`}
            label={tex('grid_table_rows')}>
            <input
              id={`table-${this.props.item.id}-rows`}
              type="number"
              min="1"
              max="12"
              value={this.props.item.rows}
              className="form-control small-input"
              onChange={e => this.props.onChange(
                actions.updateProperty('rows', e.target.value)
              )}
            />
          </FormGroup>
          <FormGroup
            controlId={`table-${this.props.item.id}-cols`}
            label={tex('grid_table_cols')}>
            <input
              id={`table-${this.props.item.id}-cols`}
              type="number"
              min="1"
              max="12"
              value={this.props.item.cols}
              className="form-control small-input"
              onChange={e => this.props.onChange(
                actions.updateProperty('cols', e.target.value)
              )}
            />
          </FormGroup>
          <FormGroup
            controlId={`table-${this.props.item.id}-border-width`}
            label={tex('grid_table_border')}>
            <input
              id={`table-${this.props.item.id}-border-width`}
              type="number"
              min="0"
              max="6"
              value={this.props.item.border.width}
              className="form-control small-input"
              onChange={e => this.props.onChange(
                actions.updateProperty('borderWidth', e.target.value)
              )}
            />
          </FormGroup>
          <div className="form-group picker-container">
            <ColorPicker
              color={this.props.item.border.color}
              onPick={color => this.props.onChange(
                  actions.updateProperty('borderColor', color.hex)
              )}
            />
          </div>
        </div>
        <div className="grid-body">
          <table className="grid-table">
            <tbody>
              { this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_COL &&
                <tr>
                  {[...Array(this.props.item.cols)].map((x, i) =>
                    <td key={`grid-col-score-col-${i}`} style={{padding: '8px'}}>
                      <input
                        type="number"
                        min="0"
                        disabled={!utils.atLeastOneSolutionInCol(i, this.props.item.cells, this.props.item.solutions)}
                        value={utils.getColScore(i, this.props.item.cells, this.props.item.solutions)}
                        className="form-control small-input"
                        onChange={e => this.props.onChange(
                          actions.updateColumnScore(i, e.target.value)
                        )}
                      />
                    </td>
                  )}
                </tr>
              }
              {[...Array(this.props.item.rows)].map((x, i) =>
                <tr key={`grid-row-${i}`}>
                  { this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_ROW &&
                    <td key={`grid-row-score-col-${i}`} style={{padding: '8px', verticalAlign: 'middle'}}>
                      <input
                        type="number"
                        min="0"
                        disabled={!utils.atLeastOneSolutionInRow(i, this.props.item.cells, this.props.item.solutions)}
                        value={utils.getRowScore(i, this.props.item.cells, this.props.item.solutions)}
                        className="form-control small-input"
                        onChange={e => this.props.onChange(
                          actions.updateRowScore(i, e.target.value)
                        )}
                      />
                    </td>
                  }
                  {[...Array(this.props.item.cols)].map((x, j) =>
                    <td key={`grid-row-${i}-col-${j}`} style={borderStyle}>
                      <GridCell
                        cell={utils.getCellByCoordinates(j, i, this.props.item.cells)}
                        solutions={this.props.item.solutions}
                        score={this.props.item.score}
                        sumMode={this.props.item.sumMode}
                        onChange={this.props.onChange}
                        onPopoverHide={this.onPopoverHide}
                        onPopoverShow={this.onPopoverShow}
                        popoverEnabled={!this.state.isEditingCell}
                        errors={this.props.item._errors}
                        validating={this.props.validating}
                      />
                    </td>
                  )}
                  <td>
                    <TooltipButton
                      id={`grid-btn-delete-row-${i}`}
                      className="fa fa-trash"
                      title={tex('delete')}
                      position="top"
                      enabled={this.props.item.rows > 1}
                      onClick={() => this.props.onChange(
                        actions.deleteRow(i)
                      )}
                    />
                  </td>
                </tr>
              )}
              <tr>
                { this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_ROW &&
                  <td></td>
                }
                {[...Array(this.props.item.cols)].map((x, i) =>
                  <td key={`grid-btn-delete-col-${i}`}>
                    <TooltipButton
                      id={`grid-btn-delete-col-${i}`}
                      className="fa fa-trash"
                      title={tex('delete')}
                      position="top"
                      enabled={this.props.item.cols > 1}
                      onClick={() => this.props.onChange(
                        actions.deleteColumn(i)
                      )}
                    />
                  </td>
                )}
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    )
  }
}

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
    _errors: T.object
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

export {Grid}
