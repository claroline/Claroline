import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {POINTER_PLACED, POINTER_CORRECT, POINTER_WRONG} from '#/plugin/exo/items/graphic/constants'
import {PointableImage} from '#/plugin/exo/items/graphic/components/pointable-image'
import {utils} from '#/plugin/exo/items/graphic/utils'

export const GraphicFeedback = props =>
  <PointableImage
    src={props.item.image.data || asset(props.item.image.url)}
    absWidth={props.item.image.width}
    pointers={props.answer.map(coords => {
      const area = utils.findArea(coords, props.item.solutions)
      return {
        absX: coords.x,
        absY: coords.y,
        type: props.item.hasExpectedAnswers ?
          (area && (area.score > 0)) ? POINTER_CORRECT : POINTER_WRONG :
          POINTER_PLACED,
        feedback: area && area.feedback
      }
    })}
    hasExpectedAnswers={props.item.hasExpectedAnswers}
  />

GraphicFeedback.propTypes = {
  item: T.shape({
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
  answer: T.arrayOf(T.shape({
    x: T.number.isRequired,
    y: T.number.isRequired
  })).isRequired
}

GraphicFeedback.defaultProps = {
  answer: []
}
