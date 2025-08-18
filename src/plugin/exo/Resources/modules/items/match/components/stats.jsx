import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {trans} from '#/main/app/intl/translation'

import {AnswerStats} from '#/plugin/exo/components/answer-stats'
import {utils} from '#/plugin/exo/items/match/utils'
import {Html} from '#/main/app/components/html'

const MatchStats = (props) =>
  <div className="match-paper">
    <div className="match-associations col-md-12">
      {props.item.solutions.map((solution) =>
        <div
          key={`stats-${solution.firstId}-${solution.secondId}`}
          className={classes(
            'answer-item',
            {'selected-answer' : props.item.hasExpectedAnswers && solution.score > 0}
          )}
        >
          <div className="sets">
            <Html className="item-content">
              {utils.getSolutionData(solution.firstId, props.item.firstSet)}
            </Html>

            <span className="fa fa-fw fa-chevron-left" />
            <span className="fa fa-fw fa-chevron-right" />

            <Html className="item-content">
              {utils.getSolutionData(solution.secondId, props.item.secondSet)}
            </Html>
          </div>

          <AnswerStats stats={{
            value: props.stats.matches[solution.firstId] && props.stats.matches[solution.firstId][solution.secondId] ?
              props.stats.matches[solution.firstId][solution.secondId] :
              0,
            total: props.stats.total
          }} />
        </div>
      )}
      {props.item.firstSet.map((first) =>
        props.item.secondSet.map((second) =>
          props.stats.matches[first.id] &&
          props.stats.matches[first.id][second.id] &&
          !utils.isPresentInSolutions(first.id, second.id, props.item.solutions) ?
            <div
              key={`stats-${first.id}-${second.id}`}
              className='answer-item'
            >
              <div className="sets">
                <Html className="item-content">
                  {first.data}
                </Html>

                <span className="fa fa-fw fa-chevron-left" />
                <span className="fa fa-fw fa-chevron-right" />

                <Html className="item-content">
                  {second.data}
                </Html>
              </div>

              <AnswerStats stats={{
                value: props.stats.matches[first.id] && props.stats.matches[first.id][second.id] ?
                  props.stats.matches[first.id][second.id] :
                  0,
                total: props.stats.total
              }} />
            </div> :
            ''
        )
      )}
      <div className='answer-item unanswered-item'>
        <div className="match-item-content">
          {trans('unanswered', {}, 'quiz')}
        </div>

        <AnswerStats stats={{
          value: props.stats.unanswered ? props.stats.unanswered : 0,
          total: props.stats.total
        }} />
      </div>
    </div>
  </div>

MatchStats.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    firstSet: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    secondSet: T.arrayOf(T.shape({
      id: T.string.isRequired,
      type: T.string.isRequired,
      data: T.string.isRequired
    })).isRequired,
    solutions: T.arrayOf(T.object),
    title: T.string,
    description: T.string,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  stats: T.shape({
    matches: T.object,
    unanswered: T.number,
    total: T.number
  })
}

export {
  MatchStats
}
