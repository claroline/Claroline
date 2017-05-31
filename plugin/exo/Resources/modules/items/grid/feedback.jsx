import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {Feedback} from './../components/feedback-btn.jsx'
import {utils} from './utils/utils'
import {WarningIcon} from './utils/warning-icon.jsx'
import {SUM_CELL} from './editor'
import {SCORE_SUM} from './../../quiz/enums'

class YourGridCell extends Component {
  constructor(props) {
    super(props)
    this.getTextValue = this.getTextValue.bind(this)
    this.getSolutionFeedback = this.getSolutionFeedback.bind(this)
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

  render() {
    return (
      <div className="grid-cell">
        {this.props.cell.input &&
          <div className="cell-header">
            <WarningIcon valid={this.props.isValid}/>
            <div className="additional-infos">
              <Feedback
                id={`ass-${this.props.cell.id}-feedback`}
                feedback={this.getSolutionFeedback()}
              />
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
  isValid: T.bool.isRequired
}

class GridFeedback extends Component {
  constructor(props) {
    super(props)
    this.isValidAnswer = this.isValidAnswer.bind(this)
    this.noErrorInCol = this.noErrorInCol.bind(this)
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


  noErrorInCol(colIndex) {
    // find answer cells for the col (if this method is called there is at least one expected answer in the col)
    const answerCellsForCol = this.props.item.cells.filter(cell => cell.coordinates[0] === colIndex && cell.input)
    // find answers for the row
    const colAnswers = this.props.answer.filter(a => undefined !== answerCellsForCol.find(cell => cell.id === a.cellId))
    return colAnswers.every(a => {
      const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
      if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
        return false
      }
    })
  }

  noErrorInRow(rowIndex) {
    // find score for the row
    const answerCellsForRow = this.props.item.cells.filter(cell => cell.coordinates[1] === rowIndex && cell.input)
    const rowAnswers = this.props.answer.filter(a => undefined !== answerCellsForRow.find(cell => cell.id === a.cellId))
    return rowAnswers.every(a => {
      const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
      if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
        return false
      }
    })
  }

  noErrorInGrid() {
    let noError = true
    this.props.answer.every(a => {
      const solution = this.props.item.solutions.find(solution => solution.cellId === a.cellId)
      if (undefined === solution.answers.find(answer => answer.expected && ((answer.caseSensitive && answer.text === a.text) || (answer.text.toLowerCase() === a.text.toLowerCase())))) {
        noError = false
        return false
      }
    })
    return noError
  }

  getYourAnswerCellColors(cell, valid) {
    const errorStyle = {backgroundColor: '#f9e2e2', color: '#b94a48'}
    const successStyle = {backgroundColor: '#d4ffb0', color: '#468847'}
    if(cell.input) {
      return valid ? successStyle : errorStyle
    } else {
      return {backgroundColor: cell.background}
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
      <div className="grid-paper">
        <div className="grid-body">
          <table className="grid-table">
            <tbody>
              {[...Array(this.props.item.rows)].map((x, i) =>
                <tr key={`grid-row-${i}`}>
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
    solutions: T.arrayOf(T.object).isRequired
  }).isRequired,
  answer: T.array.isRequired
}

GridFeedback.defaultProps = {
  answer: []
}

export {GridFeedback}
