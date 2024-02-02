import React from 'react'
import {PropTypes as T} from 'prop-types'
import {number} from '#/main/app/intl'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

import {ResourceEvaluation as ResourceEvaluationTypes} from '#/main/evaluation/resource/prop-types'

/**
 * Renders a gauge to display progression of the user in the resource evaluation.
 */
const UserProgression = props =>
  <LiquidGauge
    id="user-progression"
    type="user"
    value={props.userEvaluation.progression || 0}
    displayValue={(value) => number(value) + '%'}
    width={props.width}
    height={props.height}
  />

UserProgression.propTypes = {
  userEvaluation: T.shape(
    ResourceEvaluationTypes.propTypes
  ).isRequired,
  width: T.number,
  height: T.number
}

export {
  UserProgression
}
