import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'
import {utils} from '#/plugin/exo/items/grid/utils/utils'
import {WarningIcon} from '#/plugin/exo/components/warning-icon'
import {constants} from '#/plugin/exo/items/grid/constants'
import {SCORE_SUM, SCORE_FIXED} from '#/plugin/exo/scores/constants'

class YourGridCell extends Component {
  constructor(props) {
    super(props)
    this.getTextValue = this.getTextValue.bind(this)
    this.getSolutionFeedback = this.getSolutionFeedback.bind(this)
    this.getSolutionScore = this.getSolutionScore.bind(this)
  }

  getTextValue(){
    const answer = this.props.answers.find(answer => answer.cellId === this.props.cell.id)
    return undefined === answer ? '' : answer.text
  }

  getSolutionFeedback(){
    const solution = this.props.solutions.find(solution => solution.cellId === this.props.cell.id)
    const givenAnswer = this.props.answers.find(answer => answer.cellId === this.props.cell.id)
    if (undefined === givenAnswer) {
      return ''
    }
    const solutionAnswer = solution.answers.find(answer => answer.text === givenAnswer.text)
    return undefined !== solutionAnswer ? solutionAnswer.feedback : ''
  }

  getSolutionScore(){
    const solution = this.props.solutions.find(solution => solution.cellId === this.props.cell.id)
    const givenAnswer = this.props.answers.find(answer => answer.cellId === this.props.cell.id)
    if (undefined === givenAnswer) {
      return this.props.penalty
    }
    const solutionAnswer = solution.answers.find(answer => answer.text === givenAnswer.text)
    return undefined !== solutionAnswer ? solutionAnswer.score : 0
  }

  render() {
    return (
      <div className="grid-cell">
        {this.props.cell.input &&
          <div className={classes(
            'cell-header',
            {'text-success': this.props.hasExpectedAnswers && this.props.isValid},
            {'text-danger': this.props.hasExpectedAnswers && !this.props.isValid}
          )}>
            {this.props.hasExpectedAnswers &&
              <WarningIcon valid={this.props.isValid}/>
            }
            <div className="additional-infos">
              <Feedback
                id={`ass-${this.props.cell.id}-feedback`}
                feedback={this.getSolutionFeedback()}
              />
              {this.props.hasExpectedAnswers && this.props.showScore &&
                <SolutionScore score={this.getSolutionScore()}/>
              }
            </div>
          </div>
        }
        <div className="cell-body">
          {!this.props.cell.input &&
            <div>{this.props.cell.data}</div>
          }
          {this.props.cell.choices.length > 0 &&
            <div className="dropdown">
              <button className="btn btn-default dropdown-toggle" type="button" id={`choice-drop-down-${this.props.cell.id}`} data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <span>{this.getTextValue()}</span>
                &nbsp;<span className="caret"></span>
              </button>
            </div>
          }
          {this.props.cell.input && this.props.cell.choices.length === 0 &&
            <input
              type="text"
              className="form-control"
              id={`${this.props.cell.id}-data`}
              value={this.getTextValue()}
              disabled="true"
              style={{color:this.props.cell.color}}
            />
          }
        </div>
      </div>
    )
  }
}

YourGridCell.propTypes = {
  cell: T.object.isRequired,
  answers: T.array.isRequired,
  solutions: T.array.isRequired,
  isValid: T.bool.isRequired,
  showScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  penalty: T.number.isRequired
}

class GridFeedback extends Component {
  constructor(props) {
    super(props)
    this.isValidAnswer = this.isValidAnswer.bind(this)
    this.getColumnScore = this.getColumnScore.bind(this)
    this.noErrorInCol = this.noErrorInCol.bind(this)
    this.getRowScore = this.getRowScore.bind(this)
    this.noErrorInRow = this.noErrorInRow.bind(this)
    this.noErrorInGrid = this.noErrorInGrid.bind(this)
  }

  isValidAnswer(cell) {
    const answer = this.props.answer.find(answer => answer.cellId === cell.id)
    if (undefined === answer) {
      return false
    } else {
      const text = answer.text
      const solution = this.props.item.solutions.find(solution => solution.cellId === cell.id)
      // also depends on score or expected depending on score mode
      if (this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === constants.SUM_CELL) {
        return undefined !== solution.answers.find(answer => ((answer.caseSensitive && answer.text === text) || (answer.text.toLowerCase() === text.toLowerCase())) && answer.score > 0)
      } else {
        return undefined !== solution.answers.find(answer => ((answer.caseSensitive && answer.text === text) || (answer.text.toLowerCase() === text.toLowerCase())) && answer.expected)
      }
    }
  }

  getColumnScore(colIndex) {
    // find score for the col
    const answerCellsForCol = this.props.item.cells.filter(cell => cell.coordinates[0] === colIndex && cell.input)
    // if this method is called there is at least one expected answer in the col
    const oneAnswerCellOfTheCol = answerCellsForCol[0]
    let cellSolutionScore = 0
    const cellSolution = this.props.item.solutions.find(solution => solution.cellId === oneAnswerCellOfTheCol.id)
    cellSolutionScore = cellSolution.answers[0].score

    const colAnswers = this.props.answer.filter(a => undefined !== answerCellsForCol.find(cell => cell.id === a.cellId))
    if (0 === colAnswers.length) {
      return this.props.item.penalty > 0 ? cellSolutionScore - this.props.item.penalty : 0
    }
    // if penalty is set to 0 and one wrong answer then my score for the col is 0
    if (this.props.item.penalty === 0) {
      const allGood = colAnswers.every(a => {
        const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
        if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
          return false
        }
      })
      return allGood ? cellSolutionScore : 0
    } else {
      // if penalty is greater than 0 then I should apply the penalty... Only Once !!!
      let answerScore = cellSolutionScore
      colAnswers.every(a => {
        const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
        if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
          answerScore = 0 - this.props.item.penalty
          return false
        } else {
          return true
        }
      })
      return answerScore
    }
  }

  getRowScore(rowIndex) {
    // find score for the row
    const answerCellsForRow = this.props.item.cells.filter(cell => cell.coordinates[1] === rowIndex && cell.input)
    // if this method is called there is at least one expected answer in the row
    const oneAnswerCellOfTheRow = answerCellsForRow[0]
    let cellSolutionScore = 0
    const cellSolution = this.props.item.solutions.find(solution => solution.cellId === oneAnswerCellOfTheRow.id)
    cellSolutionScore = cellSolution.answers[0].score

    const rowAnswers = this.props.answer.filter(a => undefined !== answerCellsForRow.find(cell => cell.id === a.cellId))
    if (0 === rowAnswers.length) {
      return this.props.item.penalty > 0 ? cellSolutionScore - this.props.item.penalty : 0
    }
    // if penalty is set to 0 and one wrong answer then my score for the col is 0
    if (this.props.item.penalty === 0) {
      const allGood = rowAnswers.every(a => {
        const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
        if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
          return false
        } else {
          return true
        }
      })
      return allGood ? cellSolutionScore : 0
    } else {
      // if penalty is greater than 0 then I should apply the penalty... Only Once !!!
      let answerScore = cellSolutionScore
      rowAnswers.every(a => {
        const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
        if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
          answerScore = 0 - this.props.item.penalty
          return false
        } else {
          return true
        }
      })
      return answerScore
    }
  }

  noErrorInCol(colIndex) {
    // find answer cells for the col (if this method is called there is at least one expected answer in the col)
    const answerCellsForCol = this.props.item.cells.filter(cell => cell.coordinates[0] === colIndex && cell.input)
    // find answers for the col
    const colAnswers = this.props.answer.filter(a => undefined !== answerCellsForCol.find(cell => cell.id === a.cellId))
    if (0 === colAnswers.length) {
      return false
    }
    const noError = colAnswers.every(a => {
      const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
      if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
        return false
      } else {
        return true
      }
    })

    return noError
  }

  noErrorInRow(rowIndex) {
    // find answer cells for the row (if this method is called there is at least one expected answer in the row)
    const answerCellsForRow = this.props.item.cells.filter(cell => cell.coordinates[1] === rowIndex && cell.input)
    // find answers for the row
    const rowAnswers = this.props.answer.filter(a => undefined !== answerCellsForRow.find(cell => cell.id === a.cellId))
    if (0 === rowAnswers.length) {
      return false
    }
    const noError = rowAnswers.every(a => {
      const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
      if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
        return false
      } else {
        return true
      }
    })
    return noError
  }

  noErrorInGrid() {
    return this.props.answer.every(a => {
      const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
      if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
        return false
      } else {
        return true
      }
    })
  }

  getYourAnswerCellColors(cell, valid) {
    const errorStyle = {backgroundColor: '#f9e2e2', color: '#b94a48'}
    const successStyle = {backgroundColor: '#d4ffb0', color: '#468847'}
    if (this.props.item.score.type === SCORE_FIXED) {
      return this.noErrorInGrid() ? successStyle : errorStyle
    } else if (this.props.item.sumMode === constants.SUM_CELL) {
      if (cell.input) {
        return valid ? successStyle : errorStyle
      } else {
        return {backgroundColor: cell.background}
      }
    } else if (this.props.item.sumMode === constants.SUM_ROW) {
      // if no expected answer in the row then background set for the cell
      if (!utils.atLeastOneSolutionInRow(cell.coordinates[1], this.props.item.cells, this.props.item.solutions)) {
        return cell.background
      } else if (this.noErrorInRow(cell.coordinates[1])) {
        // else if no error in row
        return successStyle
      } else {
        // else at least one error in the row
        return errorStyle
      }
    } else {
      // if no expected answer in the col then background set for the cell
      if (!utils.atLeastOneSolutionInCol(cell.coordinates[0], this.props.item.cells, this.props.item.solutions)) {
        return cell.background
      } else if (this.noErrorInCol(cell.coordinates[0])) {
        // else if one or more error in the row
        return successStyle
      } else {
        return errorStyle
      }
    }
  }

  render(){
    return (
      <div className="grid-paper">
        <div className="grid-body">
          <table className="grid-table">
            <tbody>
              {this.props.showScore && this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === constants.SUM_COL &&
                <tr>
                  {[...Array(this.props.item.cols)].map((x, i) =>
                    <td key={`grid-col-score-col-${i}`} style={{padding: '8px'}}>
                      { utils.atLeastOneSolutionInCol(i, this.props.item.cells, this.props.item.solutions) &&
                        <span className={classes(
                          {'text-success': this.props.item.hasExpectedAnswers && this.getColumnScore(i) > 0},
                          {'text-danger': this.props.item.hasExpectedAnswers && this.getColumnScore(i) < 1}
                        )}>
                          <SolutionScore score={this.getColumnScore(i)}/>
                        </span>
                      }
                    </td>
                  )}
                </tr>
              }
              {[...Array(this.props.item.rows)].map((x, i) =>
                <tr key={`grid-row-${i}`}>
                  {this.props.showScore && this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === constants.SUM_ROW &&
                    <td key={`grid-row-score-col-${i}`} style={{padding: '8px', verticalAlign: 'middle'}}>
                      { utils.atLeastOneSolutionInRow(i, this.props.item.cells, this.props.item.solutions) &&
                        <span className={classes(
                          {'text-success': this.props.item.hasExpectedAnswers && this.getRowScore(i) > 0},
                          {'text-danger': this.props.item.hasExpectedAnswers && this.getRowScore(i) < 1}
                        )}>
                          <SolutionScore score={this.getRowScore(i)}/>
                        </span>
                      }
                    </td>
                  }
                  {[...Array(this.props.item.cols)].map((x, j) => {
                    const cell = utils.getCellByCoordinates(j, i, this.props.item.cells)
                    const valid = this.isValidAnswer(cell)
                    const colors = this.props.item.hasExpectedAnswers ?
                      this.getYourAnswerCellColors(cell, valid) :
                      {backgroundColor: cell.background, color: this.props.item.border.color}
                    if(!cell.input) {
                      return(
                        <td
                          key={`grid-row-${i}-col-${j}`}
                          style={Object.assign({border: `${this.props.item.border.width}px solid ${this.props.item.border.color}`}, colors)}>
                          <div className="grid-cell">
                            <div className="cell-body">{cell.data}</div>
                          </div>
                        </td>
                      )
                    } else {
                      return (
                        <td
                          key={`grid-row-${i}-col-${j}`}
                          style={Object.assign({border: `${this.props.item.border.width}px solid ${colors.color}`}, colors)}>
                          <YourGridCell
                            isValid={valid}
                            answers={this.props.answer}
                            solutions={this.props.item.solutions}
                            showScore={this.props.showScore && this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === constants.SUM_CELL}
                            hasExpectedAnswers={this.props.item.hasExpectedAnswers}
                            cell={cell}
                            penalty={this.props.item.penalty}/>
                        </td>
                      )
                    }
                  })}
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>
    )
  }
}

GridFeedback.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    penalty: T.number.isRequired,
    sumMode: T.string.isRequired,
    score: T.object.isRequired,
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
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.array.isRequired,
  showScore: T.bool.isRequired
}

GridFeedback.defaultProps = {
  answer: []
}

export {
  GridFeedback
}
