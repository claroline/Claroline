import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/core/asset'
import {POINTER_CORRECT, POINTER_WRONG} from './enums'
import {PointableImage} from './components/pointable-image.jsx'
import {findArea} from './player'

export const GraphicFeedback = props =>
  <PointableImage
    src={props.item.image.data || asset(props.item.image.url)}
    absWidth={props.item.image.width}
    pointers={props.answer.map(coords => {
      const area = findArea(coords, props.item.solutions)
      return {
        absX: coords.x,
        absY: coords.y,
        type: (area && (area.score > 0)) ? POINTER_CORRECT : POINTER_WRONG,
        feedback: area && area.feedback
      }
    })}
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
    })).isRequired
  }).isRequired,
  answer: T.arrayOf(T.shape({
    x: T.number.isRequired,
    y: T.number.isRequired
  })).isRequired
}

GraphicFeedback.defaultProps = {
  answer: []
}
