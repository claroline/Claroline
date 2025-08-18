import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import has from 'lodash/has'

import {trans} from '#/main/app/intl/translation'

import {AnswerStats} from '#/plugin/exo/components/answer-stats'
import {utils} from '#/plugin/exo/items/pair/utils'

const PairStats = props => {
  const expectedAnswers = utils.getExpectedAnswers(props.item)

  return (
    <div className="row pair-paper">
      <div className="col-md-5 items-col">
        <ul>
          {expectedAnswers.odd.map((o) =>
            <li key={`your-answer-orphean-${o.item.id}`}>
              <div className={classes('item', {
                'selected-answer': props.item.hasExpectedAnswers,
                'stats-answer': !props.item.hasExpectedAnswers
              })}>
                <div className="item-data" dangerouslySetInnerHTML={{__html: o.item.data}} />
                <AnswerStats stats={{
                  value: props.stats.unpaired && props.stats.unpaired[o.item.id] ? props.stats.unpaired[o.item.id] : 0,
                  total: props.stats.total
                }} />
              </div>
            </li>
          )}
          {props.item.items.map((i) =>
            !utils.isPresentInOdds(i.id, expectedAnswers.odd) && has(props.stats, ['unpaired', i.id]) &&
            <li key={`your-answer-orphean-${i.id}`}>
              <div className="item stats-answer">
                <div className="item-data" dangerouslySetInnerHTML={{__html: i.data}} />
                <AnswerStats stats={{
                  value: props.stats.unpaired[i.id],
                  total: props.stats.total
                }} />
              </div>
            </li>
          )}
        </ul>
      </div>
      <div className="col-md-7 pairs-col">
        <ul>
          {expectedAnswers.answers.map((answer) =>
            <li key={`expected-answer-id-${answer.leftItem.id}-${answer.rightItem.id}`}>
              <div className={classes('item', {
                'selected-answer': props.item.hasExpectedAnswers,
                'stats-answer': !props.item.hasExpectedAnswers
              })}>
                <div className="item-data" dangerouslySetInnerHTML={{__html: answer.leftItem.data}} />
                <div className="item-data" dangerouslySetInnerHTML={{__html: answer.rightItem.data}} />

                <AnswerStats stats={{
                  value: has(props.stats, ['paired', answer.leftItem.id, answer.rightItem.id]) ?
                    props.stats.paired[answer.leftItem.id][answer.rightItem.id] :
                    0,
                  total: props.stats.total
                }} />
              </div>
            </li>
          )}
          {props.item.items.map((i1) =>
            props.item.items.map((i2) =>
              has(props.stats, ['paired', i1.id, i2.id]) &&
              !utils.isPresentInSolutions(i1.id, i2.id, props.item.solutions) &&
              <li key={`expected-answer-id-${i1.id}-${i2.id}`}>
                <div className="item stats-answer">
                  <div className="item-data" dangerouslySetInnerHTML={{__html: i1.data}} />
                  <div className="item-data" dangerouslySetInnerHTML={{__html: i2.data}} />

                  <AnswerStats stats={{
                    value: has(props.stats, ['paired', i1.id, i2.id]) ?
                      props.stats.paired[i1.id][i2.id] :
                      0,
                    total: props.stats.total
                  }} />
                </div>
              </li>
            )
          )}
        </ul>
      </div>
      <div className="col-md-12">
        <div className='answer-item unanswered-item'>
          <div>{trans('unanswered', {}, 'quiz')}</div>

          <AnswerStats stats={{
            value: props.stats.unanswered ? props.stats.unanswered : 0,
            total: props.stats.total
          }} />
        </div>
      </div>
    </div>
  )
}

PairStats.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    items: T.arrayOf(T.object).isRequired,
    solutions: T.arrayOf(T.object).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  stats: T.shape({
    unpaired: T.oneOfType([T.object, T.array]),
    paired: T.oneOfType([T.object, T.array]),
    unanswered: T.number,
    total: T.number
  })
}

export {
  PairStats
}
