import React from 'react'
import {PropTypes as T} from 'prop-types'

import {number} from '#/main/core/intl'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

import {UserEvaluation as UserEvaluationTypes} from '#/main/core/resource/evaluation/prop-types'

/**
 * Renders a gauge to display progression of the user in the resource evaluation.
 */
const ProgressionGauge = props =>
  <LiquidGauge
    id="user-progression"
    type="user"
    value={props.userEvaluation.score ? (props.userEvaluation.score / props.userEvaluation.scoreMax)*100 : 0}
    displayValue={(value) => number(value)}
    unit="%"
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
