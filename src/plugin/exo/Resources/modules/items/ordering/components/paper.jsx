import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import has from 'lodash/has'

import {trans} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'

import {FeedbackButton} from '#/plugin/exo/buttons/feedback/components/button'
import {SolutionScore} from '#/plugin/exo/components/score'
import {AnswerStats} from '#/plugin/exo/items/components/stats'
import {WarningIcon} from '#/plugin/exo/components/warning-icon'
import {utils} from '#/plugin/exo/items/ordering/utils'
import {PaperTabs} from '#/plugin/exo/items/components/paper-tabs'
import {SCORE_SUM} from '#/plugin/exo/quiz/enums'
import {constants} from '#/plugin/exo/items/ordering/constants'
import isEmpty from 'lodash/isEmpty'

const OrderingAnswerItem = (props) =>
  <div className={classes('ordering-answer-item answer-item', props.className)}>
    {props.hasExpectedAnswers &&
      <WarningIcon className="ordering-item-tick" valid={props.valid} />
    }

    <ContentHtml className="ordering-item-content">
      {props.content}
    </ContentHtml>

    <FeedbackButton
      id={`ordering-answer-${props.id}-feedback`}
      feedback={props.feedback}
    />

    {props.showScore &&
      <SolutionScore score={props.score} />
    }
  </div>

OrderingAnswerItem.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  content: T.string.isRequired,
  feedback: T.string,
  score: T.number,
  hasExpectedAnswers: T.bool,
  valid: T.bool,
  showScore: T.bool
}

const OrderingPaper = props => {
  return (
    <PaperTabs
      id={props.item.id}
      showExpected={props.showExpected}
      showStats={props.showStats}
      showYours={props.showYours}
      yours={
        <div className={classes('ordering-paper', props.item.direction)}>
          <div className={classes('ordering-answer-items', props.item.direction)}>
            {props.item.mode === constants.MODE_INSIDE ?
              props.answer.map((a) =>
                <OrderingAnswerItem
                  id={a.itemId}
                  key={a.itemId}
                  className={classes(props.item.hasExpectedAnswers ?
                    utils.getAnswerClass(a, props.answer, props.item.solutions, props.item.score.type) :
                    'selected-answer'
                  )}
                  hasExpectedAnswers={props.item.hasExpectedAnswers}
                  valid={utils.answerIsValid(a, props.item.solutions)}
                  showScore={props.showScore && props.item.score.type === SCORE_SUM && utils.showScore(a, props.item.solutions)}

                  content={props.item.items.find(item => item.id === a.itemId).data}
                  score={props.item.solutions.find(solution => solution.itemId === a.itemId).score}
                  feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
                />
              )
              :
              props.item.solutions.filter(solution => undefined === props.answer.find(answer => answer.itemId === solution.itemId)).map((solution) =>
                <OrderingAnswerItem
                  id={solution.itemId}
                  key={solution.itemId}
                  className={classes(props.item.hasExpectedAnswers ?
                    solution.score > 0 ? 'incorrect-answer' : 'correct-answer' :
                    undefined
                  )}
                  hasExpectedAnswers={props.item.hasExpectedAnswers}
                  valid={solution.score < 1}
                  showScore={props.showScore && solution.score > 0}

                  content={props.item.items.find(item => item.id === solution.itemId).data}
                  score={solution.score}
                  feedback={solution.score > 0 && solution.feedback}
                />
              )
            }
          </div>

          {props.item.mode === constants.MODE_BESIDE &&
            <div className={classes('answer-zone ordering-answer-items', props.item.direction)}>
              {isEmpty(props.answer) &&
                <div className="ordering-drop-container">{trans('no_answer', {}, 'quiz')}</div>
              }

              {props.answer.map((a) =>
                <OrderingAnswerItem
                  id={a.itemId}
                  key={a.itemId}
                  className={classes(props.item.hasExpectedAnswers ?
                    utils.getAnswerClass(a, props.answer, props.item.solutions, props.item.score.type) :
                    undefined
                  )}
                  hasExpectedAnswers={props.item.hasExpectedAnswers}
                  valid={utils.answerIsValid(a, props.item.solutions)}
                  showScore={props.showScore && props.item.score.type === SCORE_SUM && utils.showScore(a, props.item.solutions)}

                  content={props.item.items.find(item => item.id === a.itemId).data}
                  feedback={props.item.solutions.find(solution => solution.itemId === a.itemId).feedback}
                  score={props.item.solutions.find(solution => solution.itemId === a.itemId).score}
                />
              )}
            </div>
          }
        </div>
      }
      expected={
        <div className={classes('ordering-paper', props.item.direction)}>
          <div className={classes('ordering-answer-items', props.item.direction)}>
            {props.item.solutions
              .filter(solution => props.item.mode === constants.MODE_INSIDE || solution.score < 1)
              .map((solution) =>
                <OrderingAnswerItem
                  id={solution.itemId}
                  key={solution.itemId}
                  className={props.item.mode === constants.MODE_INSIDE ? 'selected-answer' : undefined}
                  showScore={props.showScore && props.item.score.type === SCORE_SUM}

                  content={props.item.items.find(item => item.id === solution.itemId).data}
                  feedback={solution.feedback}
                  score={solution.score}
                />
              )
            }
          </div>

          {props.item.mode === constants.MODE_BESIDE &&
            <div className={classes('answer-zone ordering-answer-items', props.item.direction)}>
              {props.item.solutions.filter(solution => solution.score > 0).map((solution) =>
                <OrderingAnswerItem
                  id={solution.itemId}
                  key={solution.itemId}
                  className="selected-answer"
                  showScore={props.showScore && props.item.score.type === SCORE_SUM}

                  content={props.item.items.find(item => item.id === solution.itemId).data}
                  feedback={solution.feedback}
                  score={solution.score}
                />
              )}
            </div>
          }
        </div>
      }
      stats={
        <div className={classes('ordering-paper', props.item.direction)}>
          <div className="row">
            <div
              className={classes(
                {'horizontal': props.item.direction === constants.DIRECTION_HORIZONTAL && props.item.mode === constants.MODE_BESIDE},
                {'col-md-6': props.item.direction === constants.DIRECTION_VERTICAL && props.item.mode === constants.MODE_BESIDE}
              )}
            >
              {props.item.mode === constants.MODE_INSIDE ?
                <div className={classes('col-md-12 answer-zone',
                  {'horizontal': props.item.direction === constants.DIRECTION_HORIZONTAL}
                )}>
                  {props.item.solutions.map((solution) =>
                    <div
                      key={solution.itemId}
                      className={classes('item answer-item text-info bg-info', {'selected-answer': props.item.hasExpectedAnswers})}
                    >
                      <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                    </div>
                  )}
                  <div className={classes('item stats-item', {'stats-success': props.item.hasExpectedAnswers})}>
                    <AnswerStats stats={{
                      value: has(props.stats, ['orders', utils.getKey(props.item.solutions.filter(solution => solution.score > 0))]) ?
                        props.stats.orders[utils.getKey(props.item.solutions.filter(solution => solution.score > 0))].count :
                        0,
                      total: props.stats.total
                    }}/>
                  </div>
                </div>
                :
                props.item.solutions.filter(solution => solution.score < 1).map((solution) =>
                  <div
                    key={solution.itemId}
                    className={classes('item answer-item', {'selected-answer': props.item.hasExpectedAnswers})}
                  >
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>

                    <AnswerStats stats={{
                      value: props.stats.unused && props.stats.unused[solution.itemId] ? props.stats.unused[solution.itemId] : 0,
                      total: props.stats.total
                    }}/>
                  </div>
                )
              }
              {props.item.mode === constants.MODE_INSIDE && props.stats.orders &&
                Object.values(props.stats.orders).map((o) => {
                  const data = o.data.slice()
                  const key = utils.getKey(data)

                  if (props.stats.orders[key] && !utils.isInSolutions(key, props.item.solutions)) {
                    return (
                      <div key={`stats-unexpected-${key}`} className={classes('col-md-12 answer-zone',
                        {'horizontal': props.item.direction === constants.DIRECTION_HORIZONTAL}
                      )}>
                        {props.stats.orders[key].data.map((d) =>
                          <div key={d.itemId} className="item answer-item">
                            <div className="item-data" dangerouslySetInnerHTML={{__html: d._data}}/>
                          </div>
                        )}
                        <div className="item stats-item">
                          <AnswerStats stats={{
                            value: props.stats.orders[key].count,
                            total: props.stats.total
                          }}/>
                        </div>
                      </div>
                    )
                  }
                })
              }
              {props.item.mode === constants.MODE_BESIDE &&
                props.item.items.filter(i => has(props, ['props', 'stats', 'unused', i.id]) && !utils.isInOddsSolutions(i.id, props.item.solutions)).map((i) =>
                  <div key={`stats-unused-${i.id}`} className="item answer-item">
                    <div className="item-data" dangerouslySetInnerHTML={{__html: i.data}}/>

                    <AnswerStats stats={{
                      value: props.stats.unused[i.id],
                      total: props.stats.total
                    }}/>
                  </div>
                )
              }
            </div>
            {props.item.direction === constants.DIRECTION_VERTICAL && props.item.mode === constants.MODE_BESIDE &&
              <div className="col-md-6">
                <div className="answer-zone">
                  {props.item.solutions.filter(solution => solution.score > 0).map((solution) =>
                    <div key={solution.itemId} className="item answer-item text-info bg-info">
                      <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                    </div>
                  )}
                  <div className={classes('item stats-item', {'stats-success': props.item.hasExpectedAnswers})}>
                    <AnswerStats stats={{
                      value: has(props.stats, ['orders', utils.getKey(props.item.solutions.filter(solution => solution.score > 0))]) ?
                        props.stats.orders[utils.getKey(props.item.solutions.filter(solution => solution.score > 0))].count :
                        0,
                      total: props.stats.total
                    }}/>
                  </div>
                </div>
                {props.item.direction === constants.DIRECTION_VERTICAL && props.item.mode === constants.MODE_BESIDE && props.stats.orders &&
                  Object.values(props.stats.orders).map((o) => {
                    const data = o.data.slice()
                    const key = utils.getKey(data)

                    if (props.stats.orders[key] && !utils.isInSolutions(key, props.item.solutions)) {
                      return (
                        <div key={`stats-unexpected-${key}`} className="answer-zone">
                          {props.stats.orders[key].data.map((d) =>
                            <div key={d.itemId} className="item answer-item">
                              <div className="item-data" dangerouslySetInnerHTML={{__html: d._data}}/>
                            </div>
                          )}

                          <div className="item answer-item stats-item">
                            <AnswerStats stats={{
                              value: props.stats.orders[key].count,
                              total: props.stats.total
                            }}/>
                          </div>
                        </div>
                      )
                    }
                  })
                }
              </div>
            }
          </div>
          {props.item.direction === constants.DIRECTION_HORIZONTAL && props.item.mode === constants.MODE_BESIDE &&
            <div className="row">
              <div className="col-md-12 answer-zone horizontal">
                {props.item.solutions.filter(solution => solution.score > 0).map((solution) =>
                  <div key={solution.itemId} className="item answer-item text-info bg-info">
                    <div className="item-data" dangerouslySetInnerHTML={{__html: props.item.items.find(item => item.id === solution.itemId).data}}/>
                  </div>
                )}
                <div className={classes('item answer-item stats-item', {'stats-success': props.item.hasExpectedAnswers})}>
                  <AnswerStats stats={{
                    value: has(props.stats, ['orders', utils.getKey(props.item.solutions.filter(solution => solution.score > 0))]) ?
                      props.stats.orders[utils.getKey(props.item.solutions.filter(solution => solution.score > 0))].count :
                      0,
                    total: props.stats.total
                  }}/>
                </div>
              </div>
            </div>
          }
          {props.item.direction === constants.DIRECTION_HORIZONTAL && props.item.mode === constants.MODE_BESIDE && props.stats.orders &&
            Object.values(props.stats.orders).map((o) => {
              const data = o.data.slice()
              const key = utils.getKey(data)

              if (has(props.stats.orders, [key]) && !utils.isInSolutions(key, props.item.solutions)) {
                return (
                  <div key={`stats-unexpected-${key}`} className="row">
                    <div className="col-md-12 answer-zone horizontal">
                      {props.stats.orders[key].data.map((d) =>
                        <div key={d.itemId} className="item answer-item">
                          <div className="item-data" dangerouslySetInnerHTML={{__html: d._data}}/>
                        </div>
                      )}

                      <div className="item answer-item stats-item">
                        <AnswerStats stats={{
                          value: props.stats.orders[key].count,
                          total: props.stats.total
                        }}/>
                      </div>
                    </div>
                  </div>
                )
              }
            })
          }
          <div className='answer-item unanswered-item'>
            <div>{trans('unanswered', {}, 'quiz')}</div>

            <AnswerStats stats={{
              value: props.stats.unanswered ? props.stats.unanswered : 0,
              total: props.stats.total
            }}/>
          </div>
        </div>
      }
    />
  )
}

OrderingPaper.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    penalty: T.number.isRequired,
    mode: T.string.isRequired,
    direction: T.string.isRequired,
    score: T.object.isRequired,
    items: T.arrayOf(T.object).isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  answer: T.array.isRequired,
  showScore: T.bool.isRequired,
  showExpected: T.bool.isRequired,
  showYours: T.bool.isRequired,
  showStats: T.bool.isRequired,
  stats: T.shape({
    orders: T.object,
    unused: T.object,
    unanswered: T.number,
    total: T.number
  })
}

OrderingPaper.defaultProps = {
  answer: []
}

export {
  OrderingPaper
}
