import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ClozeText} from '#/plugin/exo/items/cloze/components/text'
import {AnswersStatsTable} from '#/plugin/exo/items/cloze/components/answer-stats-table'

const ClozeStats = (props) =>
  <div className="cloze-stats">
    <ClozeText
      anchorPrefix="cloze-hole-stats"
      className="cloze-paper"
      text={props.item.text}
      holes={props.item.solutions.map((solution, idx) => {
        return {
          id: solution.holeId,
          component: (
            <span className="badge">{idx + 1}</span>
          )
        }
      })}
    />
    <hr />
    <AnswersStatsTable
      solutions={props.item.solutions}
      stats={props.stats}
      hasExpectedAnswers={props.item.hasExpectedAnswers}
    />
  </div>

ClozeStats.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    text: T.string.isRequired,
    holes: T.arrayOf(T.shape({
      id: T.string.isRequired,
      choices: T.arrayOf(T.string)
    })).isRequired,
    solutions: T.arrayOf(T.object),
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  stats: T.shape({
    holes: T.object,
    unanswered: T.number,
    total: T.number
  })
}

export {
  ClozeStats
}
