import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {tex} from '#/main/core/translation'
import {Feedback} from '../components/feedback-btn.jsx'
import {SolutionScore} from '../components/score.jsx'
import {AnswerStats} from '../components/stats.jsx'
import {WarningIcon} from './utils/warning-icon.jsx'
import {utils} from './utils/utils'
import {PaperTabs} from '../components/paper-tabs.jsx'

export const BooleanPaper = props => {
  return (
    <PaperTabs
      id={props.item.id}
      showExpected={props.showExpected}
      showStats={props.showStats}
      showYours={props.showYours}
      yours={
        <div className="boolean-paper row">
          {props.item.solutions.map(solution =>
            <div key={solution.id} className="col-md-6">
              <div className={classes(
                  'answer-item choice-item',
                  utils.getAnswerClass(solution, props.answer)
                )}>
                {solution.id === props.answer &&
                  <WarningIcon className="pull-left" valid={solution.score > 0}/>
                }

                {solution.id === props.answer &&
                <span className="pull-right">
                    <Feedback
                      id={`${solution.id}-feedback`}
                      feedback={solution.feedback}
                    />
                    {props.showScore &&
                      <SolutionScore score={solution.score}/>
                    }
                  </span>
                }

                <div dangerouslySetInnerHTML={{__html: props.item.choices.find(choice => choice.id === solution.id).data}}/>
              </div>
            </div>
          )}
        </div>
      }
      expected={
        <div className="boolean-paper row">
          {props.item.solutions.map(solution =>
            <div key={solution.id} className="col-md-6">
              <div className={classes(
                  'answer-item choice-item',
                   solution.score > 0 ? 'selected-answer' : null
                )}>

                <span className="pull-right">
                  <Feedback
                    id={`${solution.id}-feedback`}
                    feedback={solution.feedback}
                  />
                  {props.showScore &&
                    <SolutionScore score={solution.score}/>
                  }
                </span>

                <div dangerouslySetInnerHTML={{__html: props.item.choices.find(choice => choice.id === solution.id).data}}/>
              </div>
            </div>
          )}
        </div>
      }
      stats={props.showStats ?
        <div className="boolean-paper row">
          {props.item.solutions.map(solution =>
            <div key={solution.id} className="col-md-4">
              <div className={classes(
                  'answer-item choice-item',
                   solution.score > 0 ? 'selected-answer' : null
                )}>

                <span className="pull-right">
                  <AnswerStats stats={{
                    value: props.stats.choices[solution.id] ? props.stats.choices[solution.id] : 0,
                    total: props.stats.total
                  }} />
                </span>

                <div dangerouslySetInnerHTML={{__html: props.item.choices.find(choice => choice.id === solution.id).data}}/>
              </div>
            </div>
          )}
          <div className='col-md-4'>
            <div className="answer-item choice-item unanswered-item">
              <span className="pull-right">
                <AnswerStats stats={{
                  value: props.stats.unanswered ? props.stats.unanswered : 0,
                  total: props.stats.total
                }} />
              </span>
              <div>{tex('unanswered')}</div>
            </div>
          </div>
        </div> :
        <div></div>
      }
    />
  )
}


BooleanPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    choices: T.arrayOf(T.shape({
      id: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    solutions: T.arrayOf(T.object)
  }).isRequired,
  answer: T.string.isRequired,
  showScore: T.bool.isRequired,
  showYours: T.bool.isRequired,
  showExpected: T.bool.isRequired,
  showStats: T.bool.isRequired,
  stats: T.shape({
    choices: T.object,
    unanswered: T.number,
    total: T.number
  })
}

BooleanPaper.defaultProps = {
  answer: ''
}
