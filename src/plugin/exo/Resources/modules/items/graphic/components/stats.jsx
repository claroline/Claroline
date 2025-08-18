import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'

import {utils} from '#/plugin/exo/items/graphic/utils'
import {PointableImage} from '#/plugin/exo/items/graphic/components/pointable-image'
import {AnswerStatsTable} from '#/plugin/exo/items/graphic/components/answer-stats-table'

const GraphicStats = props => {
  const expectedAreas = props.item.solutions.map((solution, idx) =>
    Object.assign(utils.getAreaPosition(solution.area), {
      number: idx + 1,
      score: solution.score,
      feedback: solution.feedback
    })
  )

  return (
    <div className="graphic-paper">
      <div className="img-zone" style={{position: 'relative'}}>
        <PointableImage
          src={props.item.image.data || asset(props.item.image.url)}
          absWidth={props.item.image.width}
          pointers={[]}
          areas={expectedAreas}
        />
      </div>
      <AnswerStatsTable
        areas={expectedAreas}
        stats={props.stats}
        hasExpectedAnswers={props.item.hasExpectedAnswers}
      />
    </div>
  )
}

GraphicStats.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    image: T.oneOfType([
      T.shape({
        data: T.string.isRequired,
        width: T.number.isRequired
      }),
      T.shape({
        url: T.string.isRequired,
        width: T.number.isRequired
      })
    ]).isRequired,
    solutions: T.arrayOf(T.shape({
      area: T.shape({
        id: T.string.isRequired,
        shape: T.string.isRequired,
        color: T.string.isRequired
      }).isRequired
    })).isRequired,
    hasExpectedAnswers: T.bool.isRequired
  }).isRequired,
  stats: T.shape({
    areas: T.object,
    unanswered: T.number,
    total: T.number
  })
}

export {
  GraphicStats
}
