import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import has from 'lodash/has'

import {trans} from '#/main/app/intl/translation'
import {Html} from '#/main/app/components/html'

import {AnswerStats} from '#/plugin/exo/components/answer-stats'
import {utils} from '#/plugin/exo/items/set/utils'

const SetStats = props =>
  <div className="set-item set-stats">
    <div className="set-paper row">
      <div className="items-col col-md-5 col-sm-5 col-xs-5">
        <ul>
          {props.item.solutions.odd && props.item.solutions.odd.map((item) =>
            <li key={`stats-expected-${item.itemId}`}>
              <div className={classes('answer-item set-answer-item', {'selected-answer': props.item.hasExpectedAnswers})}>
                <Html className="item-content">
                  {utils.getSolutionItemData(item.itemId, props.item.items)}
                </Html>

                <AnswerStats stats={{
                  value: props.stats.unused && props.stats.unused[item.itemId] ? props.stats.unused[item.itemId] : 0,
                  total: props.stats.total
                }} />
              </div>
            </li>
          )}
          {props.item.items
            .filter(item => has(props, ['stats', 'unused', item.id]) && !utils.isOdd(item.id, props.item.solutions))
            .map((item) =>
              <li key={`stats-unexpected-${item.id}`}>
                <div className="answer-item set-answer-item stats-answer">
                  <div className="item-content" dangerouslySetInnerHTML={{__html: item.data}} />

                  <AnswerStats stats={{
                    value: props.stats.unused[item.id],
                    total: props.stats.total
                  }} />
                </div>
              </li>
            )
          }
        </ul>
      </div>

      <div className="sets-col col-md-7 col-sm-7 col-xs-7">
        <ul>
          {props.item.sets.map((set) =>
            <li key={`stats-expected-set-id-${set.id}`}>
              <div className="set">
                <Html className="set-heading h5 mb-0">
                  {set.data}
                </Html>
                <ul>
                  {utils.getSetItems(set.id, props.item.solutions.associations).map(ass =>
                    <li key={`stats-expected-association-${ass.itemId}-${ass.setId}`}>
                      <div className={classes('association answer-item set-answer-item', {
                        'selected-answer': props.item.hasExpectedAnswers && ass.score > 0
                      })}>
                        <Html className="item-content">
                          {utils.getSolutionItemData(ass.itemId, props.item.items)}
                        </Html>

                        <AnswerStats stats={{
                          value: has(props, ['stats', 'sets', set.id, ass.itemId]) ?
                            props.stats.sets[set.id][ass.itemId] :
                            0,
                          total: props.stats.total
                        }} />
                      </div>
                    </li>
                  )}

                  {props.item.items.map((item) => has(props, ['stats', 'sets', set.id, item.id]) && !utils.isItemInSet(item.id, set.id, props.item.solutions) ?
                    <li key={`stats-unexpected-association-${set.id}-${item.id}`}>
                      <div className="association answer-item set-answer-item stats-answer">
                        <Html className="item-content">
                          {item.data}
                        </Html>

                        <AnswerStats stats={{
                          value: props.stats.sets[set.id][item.id],
                          total: props.stats.total
                        }} />
                      </div>
                    </li> :
                    ''
                  )}
                </ul>
              </div>
            </li>
          )}
        </ul>
      </div>
    </div>

    <div className='answer-item set-answer-item unanswered-item'>
      <div>{trans('unanswered', {}, 'quiz')}</div>

      <AnswerStats stats={{
        value: props.stats.unanswered ? props.stats.unanswered : 0,
        total: props.stats.total
      }} />
    </div>
  </div>

SetStats.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string,
    description: T.string,
    items: T.arrayOf(T.object).isRequired,
    sets: T.arrayOf(T.object).isRequired,
    solutions: T.object,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  stats: T.shape({
    sets: T.object,
    unused: T.object,
    unanswered: T.number,
    total: T.number
  })
}

export {
  SetStats
}
