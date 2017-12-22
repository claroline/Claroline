import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import shuffle from 'lodash/shuffle'
import classes from 'classnames'

import {getNumbering} from './../../utils/numbering'
import {NUMBERING_NONE} from './../../quiz/enums'

export class ChoicePlayer extends Component {
  constructor(props) {
    super(props)
    this.state = {
      choices: this.randomize(props.item.choices, props.item.random)
    }
    this.select = this.select.bind(this)
  }

  randomize(choices, random) {
    return random ? shuffle(choices) : choices
  }

  isChecked(choiceId, answers) {
    return answers.indexOf(choiceId) > -1
  }

  select(multiple, choiceId, answers) {
    if (!multiple) {
      return [choiceId]
    }

    return answers.indexOf(choiceId) === -1 ?
      [choiceId].concat(answers) :
      answers.filter(answer => answer !== choiceId)
  }

  render() {
    return (
      <div className="choice-player">
        {this.state.choices.map((choice, idx) =>
          <label
            key={choice.id}
            className={classes(
              'answer-item choice-item',
              this.isChecked(choice.id, this.props.answer) ? 'selected-answer' : null
            )}
          >
            {this.props.item.numbering !== NUMBERING_NONE &&
              <span>
                {getNumbering(this.props.item.numbering, idx)}) {"\u00a0"} {/*non breaking whitespace */}
              </span>
            }
            <input
              checked={this.isChecked(choice.id, this.props.answer)}
              id={choice.id}
              className="choice-item-tick"
              name={this.props.item.id}
              type={this.props.item.multiple ? 'checkbox': 'radio'}
              onChange={() => this.props.onChange(this.select(
                this.props.item.multiple,
                choice.id,
                this.props.answer
              ))}
            />

            <div
              className="choice-item-content"
              dangerouslySetInnerHTML={{__html: choice.data}}
            ></div>
          </label>
        )}
      </div>
    )
  }
}

ChoicePlayer.propTypes = {
  item: T.shape({
    numbering: T.string.isRequired,
    id: T.string.isRequired,
    choices: T.arrayOf(T.shape({
      id: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    random: T.bool.isRequired,
    multiple: T.bool.isRequired
  }).isRequired,
  answer: T.arrayOf(T.string),
  onChange: T.func.isRequired
}

ChoicePlayer.defaultProps = {
  answer: []
}
