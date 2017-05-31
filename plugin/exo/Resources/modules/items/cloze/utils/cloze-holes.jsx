import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import classes from 'classnames'

import {tex} from '#/main/core/translation'
import {select} from '../selectors'
import {Feedback} from '../../components/feedback-btn.jsx'
import {SolutionScore} from '../../components/score.jsx'

const AnswerWarningIcon = props =>
  props.valid ?
    <span className="fa fa-check answer-warning-span" aria-hidden="true" /> :
    <span className="fa fa-times answer-warning-span" aria-hidden="true" />

AnswerWarningIcon.propTypes = {
  valid: T.bool.isRequired
}

const HoleInput = props =>
  props.choices ?
    <select
      className="form-control input-sm"
      disabled={props.disabled}
      value={props.value}
      onChange={e => props.onChange(e.target.value)}
    >
      <option value=''>{tex('please_choose')}</option>
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
      onChange={e => props.onChange(e.target.value)}
    />

HoleInput.propTypes = {
  value: T.string,
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
const PlayerHole = props =>
  <span className="cloze-hole">
    <HoleInput
      value={props.answer}
      choices={props.choices}
      disabled={false}
      onChange={props.onChange}
    />
  </span>

PlayerHole.propTypes = {
  answer: T.string,
  choices: T.arrayOf(T.string),
  onChange: T.func.isRequired
}

const SolutionHole = props =>
  <span className={classes('cloze-hole answer-item', props.className)}>
    <AnswerWarningIcon valid={props.solution && 0 < props.solution.score ? true : false} />

    <HoleInput
      value={props.answer}
      disabled={props.disabled}
      choices={props.choices}
      onChange={props.onChange}
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
  disabled: T.bool,
  className: T.string,
  showScore: T.bool.isRequired,
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
  const solution = select.getAnswerSolution(props.solutions, props.answer)

  return (
    <SolutionHole
      id={props.id}
      className={classes({
        'correct-answer': solution && 0 < solution.score,
        'incorrect-answer': !solution || 0 >= solution.score
      })}
      answer={props.answer}
      showScore={props.showScore}
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
  choices: T.arrayOf(T.string),
  showScore: T.bool,
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
    const bestAnswer = select.getBestAnswer(this.props.solutions)

    this.state = {
      answer: bestAnswer ? bestAnswer.text : ''
    }
  }

  render() {
    const solution = select.getAnswerSolution(this.props.solutions, this.state.answer)

    return (
      <SolutionHole
        id={this.props.id}
        className={classes({
          'selected-answer': solution && 0 < solution.score
        })}
        answer={this.state.answer}
        choices={this.props.choices}
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
