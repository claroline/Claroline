import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LiquidGauge} from '#/main/core/layout/evaluation/components/liquid-gauge.jsx'

import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/evaluation/prop-types'

/**
 * Renders a gauge to display progression of the user in the resource evaluation.
 */
const ProgressionGauge = props =>
  <LiquidGauge
    id="user-progression"
    type="user"
    value={props.userEvaluation.score ? (props.userEvaluation.score / props.userEvaluation.scoreMax)*100 : 0}
    width={props.width}
    height={props.height}
  />

ProgressionGauge.propTypes = {
  userEvaluation: T.shape(
    UserEvaluationTypes.propTypes
  ).isRequired,
  width: T.number,
  height: T.number
}

export {
  ProgressionGauge
}
