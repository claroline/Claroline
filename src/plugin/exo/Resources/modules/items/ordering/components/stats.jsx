import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import has from 'lodash/has'

import {trans} from '#/main/app/intl/translation'
import {AnswerStats} from '#/plugin/exo/components/answer-stats'
import {utils} from '#/plugin/exo/items/ordering/utils'
import {constants} from '#/plugin/exo/items/ordering/constants'

const OrderingStats = props => {
  return (
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
  )
}

OrderingStats.propTypes = {
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
  stats: T.shape({
    orders: T.object,
    unused: T.object,
    unanswered: T.number,
    total: T.number
  })
}

export {
  OrderingStats
}
