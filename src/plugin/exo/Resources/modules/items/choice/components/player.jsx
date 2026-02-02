import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import shuffle from 'lodash/shuffle'

import {Html} from '#/main/app/components/html'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {NUMBERING_NONE} from '#/main/app/utils/numbering'

class ChoicePlayer extends Component {
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
      <div className="choice-item choice-player">
        <div className={classes('choice-answer-items', this.props.item.direction)}>
          {this.state.choices.map((choice, idx) =>
            <label
              key={choice.id}
              className={classes(
                'answer-item choice-answer-item',
                this.isChecked(choice.id, this.props.answer) ? 'selected-answer' : null
              )}
            >
              <input
                checked={this.isChecked(choice.id, this.props.answer)}
                id={`choice-${choice.id}`}
                className="choice-item-tick form-check-input"
                type={this.props.item.multiple ? 'checkbox': 'radio'}
                disabled={this.props.disabled}
                onChange={() => this.props.onChange(this.select(
                  this.props.item.multiple,
                  choice.id,
                  this.props.answer
                ))}
              />

              {this.props.item.numbering !== NUMBERING_NONE &&
                <b>
                  {getNumbering(this.props.item.numbering, idx)} {'\u00a0'} {/*non-breaking whitespace */}
                </b>
              }

              <Html className="choice-item-content">
                {choice.data}
              </Html>
            </label>
          )}
        </div>
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
    multiple: T.bool.isRequired,
    direction: T.string.isRequired
  }).isRequired,
  answer: T.arrayOf(T.string),
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

ChoicePlayer.defaultProps = {
  answer: [],
  disabled: false
}

export {
  ChoicePlayer
}
