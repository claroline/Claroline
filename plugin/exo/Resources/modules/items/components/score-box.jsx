import React, {PropTypes as T} from 'react'
import {tex} from '../../utils/translate'

export const ScoreBox = props =>
  <span className="label label-default">
    {tex('score')}: {props.score}
    {props.scoreMax &&
      <span>
        /{props.scoreMax}
      </span>
    }
  </span>

ScoreBox.propTypes = {
  score: T.any,
  scoreMax: T.any
}
