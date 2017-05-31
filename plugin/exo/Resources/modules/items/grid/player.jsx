import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'

import {tex} from '#/main/core/translation'
import {utils} from './utils/utils'

class GridCell extends Component {
  constructor(props) {
    super(props)
    this.getTextValue = this.getTextValue.bind(this)
    this.setTextAnswer = this.setTextAnswer.bind(this)
  }

  setTextAnswer(value) {
    const answers = cloneDeep(this.props.answers)
    const answer = answers.find(answer => answer.cellId === this.props.cell.id)
    // add new
    if(undefined === answer){
      answers.push({cellId:this.props.cell.id, text: value})
    } else { // update
      answer.text = value
    }
    return answers
  }

  getTextValue(){
    const answer = this.props.answers.find(answer => answer.cellId === this.props.cell.id)
    return undefined === answer ? '' : answer.text
  }

  render() {
    return (
      <div className="grid-cell">
        <div className="cell-body">
          {!this.props.cell.input &&
            <div>{this.props.cell.data}</div>
          }
          {this.props.cell.choices.length > 0 &&
            <div className="dropdown">
              <button className="btn btn-default dropdown-toggle" type="button" id={`choice-drop-down-${this.props.cell.id}`} data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                {this.getTextValue() === '' ?
                  <span>{tex('grid_choice_select_empty')}</span>
                  :
                  <span>{this.getTextValue()}</span>
                }
                &nbsp;<span className="caret"></span>
              </button>
              <ul className="dropdown-menu" aria-labelledby={`choice-drop-down-${this.props.cell.id}`}>
                {this.props.cell.choices.map((choice, index) => {
                  {return choice !== this.getTextValue() &&
                     <li
                      key={`choice-${index}`}
                      onClick={() => this.props.onChange(
                        this.setTextAnswer(choice)
                      )}>
                      <a style={{color:this.props.cell.color}}>{choice}</a>
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
              onChange={(e) => this.props.onChange(
                this.setTextAnswer(e.target.value)
              )}
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
  onChange: T.func.isRequired,
  answers: T.array.isRequired
}

class GridPlayer extends Component {

  constructor(props) {
    super(props)
  }

  getCellBackroundColor(x, y) {
    const cell = utils.getCellByCoordinates(x, y, this.props.item.cells)
    return undefined === cell ? '#fff' : cell.background
  }

  render() {
    return (
      <div className="grid-player">
        <div className="grid-body">
          <table className="grid-table">
            <tbody>

              {[...Array(this.props.item.rows)].map((x, i) =>
                <tr key={`grid-row-${i}`}>

                  {[...Array(this.props.item.cols)].map((x, j) =>
                    <td key={`grid-row-${i}-col-${j}`}
                      style={
                        {
                          border: `${this.props.item.border.width}px solid ${this.props.item.border.color}`,
                          backgroundColor:this.getCellBackroundColor(j, i)
                        }
                      }>
                      <GridCell
                        answers={this.props.answer}
                        onChange={this.props.onChange}
                        cell={utils.getCellByCoordinates(j, i, this.props.item.cells)}/>
                    </td>
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

GridPlayer.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
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
    }).isRequired
  }).isRequired,
  onChange: T.func.isRequired,
  answer: T.array.isRequired
}

GridPlayer.defaultProps = {
  answer: []
}

export {GridPlayer}
