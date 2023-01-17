import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import shuffle from 'lodash/shuffle'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

import {WarningIcon} from '#/plugin/exo/components/warning-icon'
import {utils} from '#/plugin/exo/items/cloze/utils'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'

const HoleInput = props =>
  props.choices ?
    <select
      className="form-control input-sm"
      disabled={props.disabled}
      value={props.value}
      onChange={e => props.onChange(e.target.value)}
    >
      <option value=''>{trans('please_choose', {}, 'quiz')}</option>
      {props.choices.map((choice, idx) =>
        <option value={choice} key={idx}>{choice}</option>
      )}
    </select>
    :
    <input
      className="form-control input-sm"
      disabled={props.disabled}
      type="text"
      value={props.value}
      size={props.size}
      onChange={e => props.onChange(e.target.value)}
    />

HoleInput.propTypes = {
  value: T.string,
  size: T.number,
  disabled: T.bool,
  choices: T.arrayOf(T.string),
  onChange: T.func.isRequired
}

HoleInput.defaultProps = {
  value: '',
  disabled: false
}

/**
 * Display Hole in player.
 */
class PlayerHole extends Component {
  constructor(props) {
    super(props)

    this.state = {
      choices: props.random ? shuffle(props.choices) : props.choices
    }
  }

  render() {
    return (
      <span className="cloze-hole">
        <HoleInput
          value={this.props.answer}
          size={this.props.size}
          choices={this.state.choices}
          disabled={this.props.disabled}
          onChange={this.props.onChange}
        />
      </span>
    )
  }
}

PlayerHole.propTypes = {
  size: T.number,
  answer: T.string,
  random: T.bool,
  choices: T.arrayOf(T.string),
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

const SolutionHole = props =>
  <span className={classes('cloze-hole answer-item', props.className)}>
    {props.hasExpectedAnswers &&
      <WarningIcon valid={props.solution && 0 < props.solution.score} />
    }

    <HoleInput
      value={props.answer}
      disabled={props.disabled}
      choices={props.choices}
      onChange={props.onChange}
      size={props.size}
    />

    {props.solution && props.solution.feedback &&
      <Feedback
        id={`${props.id}-feedback`}
        feedback={props.solution.feedback}
      />
    }

    {props.showScore && (!props.solution || (props.solution.score || 0 === props.solution.score)) &&
      <SolutionScore score={props.solution ? props.solution.score : 0} />
    }
  </span>

SolutionHole.propTypes = {
  id: T.string.isRequired,
  answer: T.string,
  choices: T.arrayOf(T.string),
  size: T.number,
  disabled: T.bool,
  className: T.string,
  showScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  solution: T.shape({
    text: T.string.isRequired,
    score: T.number.isRequired,
    feedback: T.string
  }),
  onChange: T.func.isRequired
}

/**
 * Displays user answer for an Hole in papers and feedback.
 */
const UserAnswerHole = props => {
  const solution = utils.getAnswerSolution(props.solutions, props.answer)

  return (
    <SolutionHole
      id={props.id}
      className={classes({
        'correct-answer': props.hasExpectedAnswers && solution && 0 < solution.score,
        'incorrect-answer': props.hasExpectedAnswers && (!solution || 0 >= solution.score)
      })}
      size={props.size}
      answer={props.answer}
      showScore={props.showScore}
      hasExpectedAnswers={props.hasExpectedAnswers}
      choices={props.choices}
      solution={solution}
      disabled={true}
      onChange={() => true}
    />
  )
}

UserAnswerHole.propTypes = {
  id: T.string.isRequired,
  answer: T.string,
  size: T.number,
  choices: T.arrayOf(T.string),
  showScore: T.bool,
  hasExpectedAnswers: T.bool,
  solutions: T.arrayOf(T.shape({
    text: T.string.isRequired,
    score: T.number.isRequired,
    feedback: T.string
  }))
}

/**
 * Displays expected answer for an Hole in papers.
 */
class ExpectedAnswerHole extends Component {
  constructor(props) {
    super(props)

    // Retrieve the expected answer with the most point to display it in the hole
    const bestAnswer = utils.getBestAnswer(this.props.solutions)

    this.state = {
      answer: bestAnswer ? bestAnswer.text : ''
    }
  }

  render() {
    const solution = utils.getAnswerSolution(this.props.solutions, this.state.answer)

    return (
      <SolutionHole
        id={this.props.id}
        className={classes({
          'selected-answer': solution && 0 < solution.score
        })}
        answer={this.state.answer}
        choices={this.props.choices}
        size={this.props.size}
        showScore={this.props.showScore}
        solution={solution}
        disabled={!this.props.choices || 0 === this.props.choices.length}
        onChange={(answer) => this.setState({answer: answer})}
      />
    )
  }
}

ExpectedAnswerHole.propTypes = {
  id: T.string.isRequired,
  choices: T.arrayOf(T.string),
  size: T.number,
  showScore: T.bool.isRequired,
  solutions: T.arrayOf(T.shape({
    text: T.string.isRequired,
    score: T.number.isRequired,
    feedback: T.string
  }))
}

export {
  PlayerHole,
  UserAnswerHole,
  ExpectedAnswerHole
}
