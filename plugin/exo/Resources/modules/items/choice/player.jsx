import React, {Component, PropTypes as T} from 'react'
import shuffle from 'lodash/shuffle'
import classes from 'classnames'

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
        {this.state.choices.map(choice =>
          <div
            key={choice.id}
            className={classes(
              'item',
              this.props.item.multiple ? 'checkbox': 'radio'
            )}
          >
            <input
              checked={this.isChecked(choice.id, this.props.answer)}
              id={choice.id}
              name={this.props.item.id}
              type={this.props.item.multiple ? 'checkbox': 'radio'}
              onChange={() => this.props.onChange(this.select(
                this.props.item.multiple,
                choice.id,
                this.props.answer
              ))}
            />
            <label
              className="control-label"
              htmlFor={choice.id}
              dangerouslySetInnerHTML={{__html: choice.data}}
            />
          </div>
        )}
      </div>
    )
  }
}

ChoicePlayer.propTypes = {
  item: T.shape({
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
