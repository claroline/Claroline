import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'
import {utils} from '#/plugin/exo/items/grid/utils/utils'
import {constants} from '#/plugin/exo/items/grid/constants'
import {SCORE_SUM} from '#/plugin/exo/scores/constants'

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
        if (best === null || best.score < answer.score) {
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
        if (best === null || best.score < answer.score) {
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
        if (best === null || best.score < answer.score) {
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
  solutions: T.array.isRequired,
  showScore: T.bool.isRequired,
  isSumCellMode: T.bool.isRequired
}

class GridExpectedAnswer extends Component {
  constructor(props) {
    super(props)

    this.getColumnScore = this.getColumnScore.bind(this)
    this.getRowScore = this.getRowScore.bind(this)
  }

  getColumnScore(colIndex) {
    // find score for the col
    const answerCellsForCol = this.props.item.cells.filter(cell => cell.coordinates[0] === colIndex && cell.input)
    // if this method is called, there is at least one expected answer in the col
    const oneAnswerCellOfTheCol = answerCellsForCol[0]
    let cellSolutionScore = 0
    const cellSolution = this.props.item.solutions.find(solution => solution.cellId === oneAnswerCellOfTheCol.id)
    cellSolutionScore = cellSolution.answers[0].score

    return cellSolutionScore
  }

  getRowScore(rowIndex) {
    // find score for the row
    const answerCellsForRow = this.props.item.cells.filter(cell => cell.coordinates[1] === rowIndex && cell.input)
    // if this method is called, there is at least one expected answer in the row
    const oneAnswerCellOfTheRow = answerCellsForRow[0]
    let cellSolutionScore = 0
    const cellSolution = this.props.item.solutions.find(solution => solution.cellId === oneAnswerCellOfTheRow.id)
    cellSolutionScore = cellSolution.answers[0].score

    return cellSolutionScore
  }

  getExpectedAnswerCellColors(cell) {
    if (cell.input) {
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
              {this.props.showScore && this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === constants.SUM_COL &&
                <tr>
                  {[...Array(this.props.item.cols)].map((x, i) =>
                    <td key={`grid-col-score-col-${i}`} style={{padding: '8px'}}>
                      { utils.atLeastOneSolutionInCol(i, this.props.item.cells, this.props.item.solutions) &&
                        <span className="text-info">
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
                        <span className="text-info">
                          <SolutionScore score={this.getRowScore(i)}/>
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
                          style={Object.assign({border: `${this.props.item.border.width}px solid ${this.props.item.border.color}`}, colors)}
                        >
                          <div className="grid-cell">
                            <div className="cell-body">{cell.data}</div>
                          </div>
                        </td>
                      )
                    } else {
                      return (
                        <td
                          key={`grid-row-${i}-col-${j}`}
                          style={Object.assign({border: `${this.props.item.border.width}px solid ${colors.color}`}, colors)}
                        >
                          <ExpectedGridCell
                            showScore = {this.props.showScore}
                            solutions={this.props.item.solutions}
                            isSumCellMode={this.props.item.score.type === SCORE_SUM && this.props.item.sumMode === constants.SUM_CELL}
                            cell={cell}
                          />
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

GridExpectedAnswer.propTypes = {
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
  showScore: T.bool.isRequired
}

export {
  GridExpectedAnswer
}
