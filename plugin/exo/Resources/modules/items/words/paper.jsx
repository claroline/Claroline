import React, {PropTypes as T} from 'react'

import {tex} from '../../utils/translate'
import {Highlight} from './utils/highlight.jsx'
import {Feedback} from '../components/feedback-btn.jsx'
import {SolutionScore} from '../components/score.jsx'
import {PaperTabs} from '../components/paper-tabs.jsx'
import classes from 'classnames'

const AnswerTable = (props) => {
  return(
    <div className="word-paper">
      {props.solutions.map(solution =>
        <div
          key={solution.text}
          className={classes(
            'item',
            {
              'bg-info text-info': solution.score > 0
            }
        )}>
          <span className="word-label">{solution.text}</span>
          <Feedback
            id={`${solution.text}-feedback`}
            feedback={solution.feedback}
          /> {'\u00a0'}
          <SolutionScore score={solution.score}/>
        </div>
      )}
    </div>
  )
}

AnswerTable.propTypes = {
  solutions: T.arrayOf(T.shape({
    score: T.number.isRequired,
    text: T.string.isRequired,
    feedback: T.string
  }))
}

export const WordsPaper = (props) => {
  const solutions = props.item.solutions.slice(0)
  const halfLength = Math.ceil(solutions.length / 2)
  const leftSide = solutions.splice(0, halfLength)
  const rightSide = solutions

  return (
    <PaperTabs
      id={props.item.id}
      yours={
        props.answer && 0 !== props.answer.length ?
          <Highlight
            text={props.answer}
            solutions={props.item.solutions}
            showScore={true}
          /> :
          <div className="no-answer">{tex('no_answer')}</div>
      }
      expected={
        <div className="row">
          <div className="col-md-6">
            <AnswerTable solutions={leftSide}/>
          </div>
          <div className="col-md-6">
            <AnswerTable solutions={rightSide}/>
          </div>
        </div>
      }
    />
  )
}

WordsPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired,
    description: T.string.isRequired,
    solutions: T.arrayOf(T.object)
  }).isRequired,
  answer: T.string.isRequired
}

WordsPaper.defaultProps = {
  answer: ''
}
