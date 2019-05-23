import React from 'react'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {SolutionScore} from '#/plugin/exo/components/score'
import {FeedbackButton as Feedback} from '#/plugin/exo/buttons/feedback/components/button'
import {AnswerStats} from '#/plugin/exo/items/components/stats'
import {PaperTabs} from '#/plugin/exo/items/components/paper-tabs'

import {Highlight} from '#/plugin/exo/items/words/components/highlight'

const AnswerTable = (props) => {
  return(
    <div className="words-paper">
      {props.solutions.map(solution =>
        <div
          key={solution.text}
          className={classes('word-item answer-item', {
            'selected-answer': solution.score > 0
          })}
        >
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

const AnswerStatsTable = (props) => {
  return(
    <div className="words-paper">
      {props.solutions.map(solution =>
        <div
          key={solution.text}
          className={classes('word-item answer-item', {
            'selected-answer': props.hasExpectedAnswers && solution.score > 0
          })}
        >
          <span className="word-label">{solution.text}</span>
          <AnswerStats stats={{
            value: props.stats.words[solution.text] ? props.stats.words[solution.text] : 0,
            total: props.stats.total
          }}/>
        </div>
      )}
      {!props.isCorrect && props.stats.words['_others'] &&
        <div className="word-item answer-item">
          <span className="word-label">{trans('other_answers', {}, 'quiz')}</span>
          <AnswerStats stats={{
            value: props.stats.words['_others'],
            total: props.stats.total
          }}/>
        </div>
      }
    </div>
  )
}

AnswerStatsTable.propTypes = {
  solutions: T.arrayOf(T.shape({
    score: T.number.isRequired,
    text: T.string.isRequired,
    feedback: T.string
  })),
  stats: T.shape({
    words: T.object,
    unanswered: T.number,
    total: T.number
  }),
  isCorrect: T.bool,
  hasExpectedAnswers: T.bool
}

export const WordsPaper = (props) => {
  const solutions = props.item.solutions.slice(0)
  const halfLength = Math.ceil(solutions.length / 2)
  const leftSide = solutions.splice(0, halfLength)
  const rightSide = solutions

  return (
    <PaperTabs
      showExpected={props.showExpected}
      showStats={props.showStats}
      showYours={props.showYours}
      id={props.item.id}
      yours={
        props.answer && 0 !== props.answer.length ?
          <Highlight
            text={props.answer}
            solutions={props.item.solutions}
            showScore={props.showScore}
            hasExpectedAnswers={props.item.hasExpectedAnswers}
          /> :
          <div className="no-answer">{trans('no_answer', {}, 'quiz')}</div>
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
      stats={
        <div className="words-stats">
          <div className="row">
            <div className="col-md-6">
              <AnswerStatsTable
                solutions={leftSide}
                stats={props.stats}
                isCorrect={true}
                hasExpectedAnswers={props.item.hasExpectedAnswers}
              />
            </div>
            <div className="col-md-6">
              <AnswerStatsTable
                solutions={rightSide}
                stats={props.stats}
                isCorrect={false}
                hasExpectedAnswers={props.item.hasExpectedAnswers}
              />
            </div>
          </div>
          <div className="row">
            <div className='answer-item unanswered-item'>
              <div>{trans('unanswered', {}, 'quiz')}</div>

              <AnswerStats stats={{
                value: props.stats.unanswered ? props.stats.unanswered : 0,
                total: props.stats.total
              }}/>
            </div>
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
    solutions: T.arrayOf(T.object),
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.string.isRequired,
  showScore: T.bool.isRequired,
  showExpected: T.bool.isRequired,
  showYours: T.bool.isRequired,
  showStats: T.bool.isRequired,
  stats: T.shape({
    words: T.object,
    unanswered: T.number,
    total: T.number
  })
}

WordsPaper.defaultProps = {
  answer: ''
}
