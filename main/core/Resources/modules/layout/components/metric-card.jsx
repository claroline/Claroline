import React from 'react'
import {PropTypes as T} from 'prop-types'

import {CountGauge} from '#/main/core/layout/gauge/components/count-gauge.jsx'
import {number} from '#/main/app/intl'
/**
 * Creates a Metric Card.
 *
 * @param props
 * @constructor
 */
const MetricCard = props =>
  <div className="metric-card">
    <CountGauge
      className="metric-card-gauge"
      value={props.value}
      displayValue={(value) => number(value, true)}
    />
    <div className="metric-card-title">{props.cardTitle}</div>
  </div>

MetricCard.propTypes = {
  /**
   * Gauge Value.
   */
  value: T.number.isRequired,

  /**
   * Card Title.
   */
  cardTitle: T.string
}

export {
  MetricCard
}
