import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import shuffle from 'lodash/shuffle'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {MenuButton, CALLBACK_BUTTON} from '#/main/app/buttons'

import {utils} from '#/plugin/exo/items/grid/utils/utils'

class CellChoices extends Component {
  constructor(props) {
    super(props)

    this.state = {
      choices: props.random ? shuffle(props.choices) : props.choices
    }
  }

  render() {
    return (
      <MenuButton
        className={classes('btn', {disabled: this.props.disabled})}
        id={`choice-drop-down-${this.props.cellId}`}
        menu={{
          items: this.state.choices.map((choice) => ({
            type: CALLBACK_BUTTON,
            label: choice,
            disabled: this.props.disabled,
            active: this.props.selected === choice,
            callback: () => this.props.onChange(choice)
          }))
        }}
      >
        {this.props.selected || trans('grid_choice_select_empty', {}, 'quiz')}
        &nbsp;<span className="caret" />
      </MenuButton>
    )
  }
}

CellChoices.propTypes = {
  cellId: T.string.isRequired,
  disabled: T.bool,
  random: T.bool,
  choices: T.array,
  selected: T.string,
  onChange: T.func.isRequired
}

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
    if (undefined === answer){
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
            <div style={{color:this.props.cell.color}}>
              {this.props.cell.data}
            </div>
          }

          {this.props.cell.choices.length > 0 &&
            <CellChoices
              cellId={this.props.cell.id}
              disabled={this.props.disabled}
              random={this.props.cell.random}
              choices={this.props.cell.choices}
              selected={this.getTextValue()}
              onChange={(choice) => this.props.onChange(this.setTextAnswer(choice))}
            />
          }

          {this.props.cell.input && this.props.cell.choices.length === 0 &&
            <input
              type="text"
              className="form-control"
              id={`${this.props.cell.id}-data`}
              value={this.getTextValue()}
              disabled={this.props.disabled}
              onChange={(e) => this.props.disabled ? false : this.props.onChange(this.setTextAnswer(e.target.value))}
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
  disabled: T.bool.isRequired,
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
                        disabled={this.props.disabled}
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
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired,
  answer: T.array.isRequired
}

GridPlayer.defaultProps = {
  answer: [],
  disabled: false
}

export {GridPlayer}
