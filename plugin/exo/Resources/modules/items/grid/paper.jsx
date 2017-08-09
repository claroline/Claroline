import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Feedback} from './../components/feedback-btn.jsx'
import {SolutionScore} from './../components/score.jsx'
import {PaperTabs} from './../components/paper-tabs.jsx'
import {utils} from './utils/utils'
import {WarningIcon} from './utils/warning-icon.jsx'
import {SUM_CELL, SUM_COL, SUM_ROW} from './editor'
import {SCORE_SUM, SCORE_FIXED} from './../../quiz/enums'


class ExpectedGridCell extends Component {
  constructor(props) {
    super(props)
    this.getTextValue = this.getTextValue.bind(this)
    this.getSolutionFeedback = this.getSolutionFeedback.bind(this)
    this.getSolutionScore = this.getSolutionScore.bind(this)

    this.state = {
      currentText: this.getTextValue()
    }
  }

  setTextState(text) {
    this.setState({
      currentText: text
    })
  }

  getCellChoices() {
    const solution = this.props.solutions.find(solution => solution.cellId === this.props.cell.id)
    if (this.props.isSumCellMode) {
      return solution.answers.filter(answer => answer.score > 0)
    } else {
      return solution.answers.filter(answer => answer.expected)
    }
  }

  getTextValue(){
    const solution = this.props.solutions.find(solution => solution.cellId === this.props.cell.id)
    if (this.props.isSumCellMode) {
      let best = null
      solution.answers.forEach(answer => {
        // or score > 0 or expected
        if(best === null || best.score < answer.score) {
          best = answer
        }
      })
      return best.text
    } else {
      return solution.answers.find(answer => answer.expected).text
    }
  }

  getSolutionFeedback(){
    const solution = this.props.solutions.find(solution => solution.cellId === this.props.cell.id)
    if (null === this.state.currentText) {
      let best = null
      solution.answers.forEach(answer => {
        if(best === null || best.score < answer.score) {
          best = answer
        }
      })
      return best.feedback
    } else {
      return solution.answers.find(answer => answer.text === this.state.currentText).feedback
    }
  }

  getSolutionScore(){
    const solution = this.props.solutions.find(solution => solution.cellId === this.props.cell.id)
    if (null ===  this.state.currentText) {
      let best = null
      solution.answers.forEach(answer => {
        if(best === null || best.score < answer.score) {
          best = answer
        }
      })
      return best.score
    } else {
      return solution.answers.find(answer => answer.text === this.state.currentText).score
    }
  }

  render() {
    return (
      <div className="grid-cell">
        {this.props.cell.input &&
          <div className="cell-header-expected">
            <Feedback
              id={`ass-${this.props.cell.id}-feedback`}
              feedback={this.getSolutionFeedback()}
            />
          {this.props.showScore && this.props.isSumCellMode &&
              <SolutionScore score={this.getSolutionScore()}/>
            }
          </div>
        }
        <div className="cell-body">
          {!this.props.cell.input &&
            <div>{this.props.cell.data}</div>
          }
          {this.props.cell.choices.length > 0 &&
            <div className="dropdown">
              <button className="btn btn-default dropdown-toggle" type="button" id={`choice-drop-down-${this.props.cell.id}`} data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <span>{this.state.currentText !== null ? this.state.currentText : this.getTextValue()}</span>
                &nbsp;<span className="caret"></span>
              </button>
              <ul className="dropdown-menu" aria-labelledby={`choice-drop-down-${this.props.cell.id}`}>
                {this.getCellChoices().map((choice, index) => {
                  {return choice.text !== this.state.currentText &&
                     <li
                      key={`choice-${index}`}
                      onClick={() => this.setTextState(choice.text)}>
                      <a>{choice.text}</a>
                    </li>
                  }
                })}
              </ul>
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

ExpectedGridCell.propTypes = {
  cell: T.object.isRequired,
  answers: T.array.isRequired,
  solutions: T.array.isRequired,
  showScore: T.bool.isRequired,
  isSumCellMode: T.bool.isRequired
}

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
              {'text-success': this.props.isValid},
              {'text-danger': !this.props.isValid}
            )}>
            <WarningIcon valid={this.props.isValid}/>
            <div className="additional-infos">
              <Feedback
                id={`ass-${this.props.cell.id}-feedback`}
                feedback={this.getSolutionFeedback()}
              />
              {this.props.showScore &&
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
  penalty: T.number.isRequired
}

class GridPaper extends Component {
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
    if(undefined === answer) {
      return false
    } else {
      const text = answer.text
      const solution = this.props.item.solutions.find(solution => solution.cellId === cell.id)
      // also depends on score or expected depending on score mode
      if(this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_CELL) {
        return undefined !== solution.answers.find(answer => ((answer.caseSensitive && answer.text === text) || (answer.text.toLowerCase() === text.toLowerCase())) && answer.score > 0)
      } else {
        return undefined !== solution.answers.find(answer => ((answer.caseSensitive && answer.text === text) || (answer.text.toLowerCase() === text.toLowerCase())) && answer.expected)
      }
    }
  }

  getColumnScore(colIndex, forExpected) {
    // find score for the col
    const answerCellsForCol = this.props.item.cells.filter(cell => cell.coordinates[0] === colIndex && cell.input)
    // if this method is called there is at least one expected answer in the col
    const oneAnswerCellOfTheCol = answerCellsForCol[0]
    let cellSolutionScore = 0
    const cellSolution = this.props.item.solutions.find(solution => solution.cellId === oneAnswerCellOfTheCol.id)
    cellSolutionScore = cellSolution.answers[0].score

    // for exepcted answer tab we do not need to go further
    if (forExpected) {
      return cellSolutionScore
    }

    const colAnswers = this.props.answer.filter(a => undefined !== answerCellsForCol.find(cell => cell.id === a.cellId))
    if (0 === colAnswers.length) {
      return this.props.item.penalty > 0 ? cellSolutionScore - this.props.item.penalty : 0
    }
    // if penalty is set to 0 and one wrong answer then my score for the col is 0
    if(this.props.item.penalty === 0) {
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

  getRowScore(rowIndex, forExpected) {
    // find score for the row
    const answerCellsForRow = this.props.item.cells.filter(cell => cell.coordinates[1] === rowIndex && cell.input)
    // if this method is called there is at least one expected answer in the row
    const oneAnswerCellOfTheRow = answerCellsForRow[0]
    let cellSolutionScore = 0
    const cellSolution = this.props.item.solutions.find(solution => solution.cellId === oneAnswerCellOfTheRow.id)
    cellSolutionScore = cellSolution.answers[0].score

    // for exepcted answer tab we do not need to go further
    if (forExpected) {
      return cellSolutionScore
    }

    const rowAnswers = this.props.answer.filter(a => undefined !== answerCellsForRow.find(cell => cell.id === a.cellId))
    if (0 === rowAnswers.length) {
      return this.props.item.penalty > 0 ? cellSolutionScore - this.props.item.penalty : 0
    }
    // if penalty is set to 0 and one wrong answer then my score for the col is 0
    if(this.props.item.penalty === 0) {
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
    const noError = this.props.answer.every(a => {
      const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
      if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
        return false
      } else {
        return true
      }
    })
    return noError
  }

  getYourAnswerCellColors(cell, valid) {
    const errorStyle = {backgroundColor: '#f9e2e2', color: '#b94a48'}
    const successStyle = {backgroundColor: '#d4ffb0', color: '#468847'}
    if (this.props.item.score.type === SCORE_FIXED) {
      return this.noErrorInGrid() ? successStyle : errorStyle
    } else if (this.props.item.sumMode === SUM_CELL) {
      if(cell.input) {
        return valid ? successStyle : errorStyle
      } else {
        return {backgroundColor: cell.background}
      }
    } else if (this.props.item.sumMode === SUM_ROW) {
      // if no expected answer in the row then background set for the cell
      if (!utils.atLeastOneSolutionInRow(cell.coordinates[1], this.props.item.cells, this.props.item.solutions)) {
        return cell.background
      } else if (this.noErrorInRow(cell.coordinates[1])) {
        // else if no error in row
        return successStyle
      } else {
        // else at least one error in row
        return errorStyle
      }
    } else {
      // if no expected answer in the col then background set for the cell
      if (!utils.atLeastOneSolutionInCol(cell.coordinates[0], this.props.item.cells, this.props.item.solutions)) {
        return cell.background
      } else if (this.noErrorInCol(cell.coordinates[0])) {
        // else if one or more error in row
        return successStyle
      } else {
        return errorStyle
      }
    }
  }

  getExpectedAnswerCellColors(cell) {
    if(cell.input) {
      return {backgroundColor: '#daf1f8', color: '#3a87ad'}
    } else {
      return {backgroundColor: cell.background}
    }
  }

  render(){
    return (
        <PaperTabs
          id={this.props.item.id}
          hideExpected={this.props.hideExpected}
          yours={
            <div className="grid-paper">
              <div className="grid-body">
                <table className="grid-table">
                  <tbody>
                    {this.props.showScore && this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_COL &&
                      <tr>
                        {[...Array(this.props.item.cols)].map((x, i) =>
                          <td key={`grid-col-score-col-${i}`} style={{padding: '8px'}}>
                            { utils.atLeastOneSolutionInCol(i, this.props.item.cells, this.props.item.solutions) &&
                              <span className={classes(
                                {'text-success': this.getColumnScore(i, false) > 0},
                                {'text-danger': this.getColumnScore(i, false) < 1}
                              )}>
                                <SolutionScore score={this.getColumnScore(i, false)}/>
                              </span>
                            }
                          </td>
                        )}
                      </tr>
                    }
                    {[...Array(this.props.item.rows)].map((x, i) =>
                      <tr key={`grid-row-${i}`}>
                        {this.props.showScore && this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_ROW &&
                          <td key={`grid-row-score-col-${i}`} style={{padding: '8px', verticalAlign: 'middle'}}>
                            { utils.atLeastOneSolutionInRow(i, this.props.item.cells, this.props.item.solutions) &&
                              <span className={classes(
                                {'text-success': this.getRowScore(i, false) > 0},
                                {'text-danger': this.getRowScore(i, false) < 1}
                              )}>
                                <SolutionScore score={this.getRowScore(i, false)}/>
                              </span>
                            }
                          </td>
                        }
                        {[...Array(this.props.item.cols)].map((x, j) => {
                          const cell = utils.getCellByCoordinates(j, i, this.props.item.cells)
                          const valid = this.isValidAnswer(cell)
                          const colors = this.getYourAnswerCellColors(cell, valid)
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
                                    showScore={this.props.showScore && this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_CELL}
                                    cell={cell}
                                    penalty={this.props.item.penalty}/>
                                </td>
                            )
                          }
                        }
                      )}
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          }
          expected={
            <div className="grid-paper">
              <div className="grid-body">
                <table className="grid-table">
                  <tbody>
                    {this.props.showScore && this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_COL &&
                      <tr>
                        {[...Array(this.props.item.cols)].map((x, i) =>
                          <td key={`grid-col-score-col-${i}`} style={{padding: '8px'}}>
                            { utils.atLeastOneSolutionInCol(i, this.props.item.cells, this.props.item.solutions) &&
                              <span className="text-info">
                                <SolutionScore score={this.getColumnScore(i, true)}/>
                              </span>
                            }
                          </td>
                        )}
                      </tr>
                    }
                    {[...Array(this.props.item.rows)].map((x, i) =>
                      <tr key={`grid-row-${i}`}>
                        {this.props.showScore && this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_ROW &&
                          <td key={`grid-row-score-col-${i}`} style={{padding: '8px', verticalAlign: 'middle'}}>
                            { utils.atLeastOneSolutionInRow(i, this.props.item.cells, this.props.item.solutions) &&
                              <span className="text-info">
                                <SolutionScore score={this.getRowScore(i, true)}/>
                              </span>
                            }
                          </td>
                        }
                        {[...Array(this.props.item.cols)].map((x, j) => {
                          const cell = utils.getCellByCoordinates(j, i, this.props.item.cells)
                          const colors = this.getExpectedAnswerCellColors(cell)
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
                                  <ExpectedGridCell
                                    showScore = {this.props.showScore}
                                    answers={this.props.answer}
                                    solutions={this.props.item.solutions}
                                    isSumCellMode={this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === SUM_CELL}
                                    cell={cell}/>
                                </td>
                            )
                          }
                        }
                      )}
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          }
        />
    )
  }
}

GridPaper.propTypes = {
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
    solutions: T.arrayOf(T.object).isRequired
  }).isRequired,
  answer: T.array.isRequired,
  showScore: T.bool.isRequired,
  hideExpected: T.bool.isRequired
}

GridPaper.defaultProps = {
  answer: []
}

export {GridPaper}
