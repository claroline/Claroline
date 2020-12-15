import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {constants} from '#/main/core/layout/gauge/constants'
import {Gauge as GaugeTypes} from '#/main/core/layout/gauge/prop-types'
import {
  GaugeContainer,
  GaugeBorder,
  GaugeText
} from '#/main/core/layout/gauge/components/gauge'

const ScoreGauge = props => {
  const radius = Math.min(parseInt(props.width), parseInt(props.height)) / 2
  const fillPercent = props.total ? (props.value / props.total) * 100 : 0

  const circleThickness = constants.GAUGE_BORDER_THICKNESS * radius
  const circleFillGap = constants.GAUGE_BORDER_GAP * radius
  const fillCircleMargin = circleThickness + circleFillGap

  return (
    <GaugeContainer
      className={classes('score-gauge', props.className)}
      type={props.type}
      width={parseInt(props.width)}
      height={parseInt(props.height)}
      radius={radius}
    >
      <defs>
        <clipPath id="cut-off-bottom">
          <rect x={0} y={radius} width={radius * 2} height={radius} />
        </clipPath>

        <clipPath id="cut-off-top">
          <rect x={0} y={0} width={radius * 2} height={radius} />
        </clipPath>
      </defs>

      <GaugeBorder
        radius={radius}
        thickness={circleThickness}
        filling={fillPercent}
        preFilled={props.preFilled}
      />

      <g className="gauge-value">
        <circle cx={radius} cy={radius} r={radius - fillCircleMargin} clipPath="url(#cut-off-top)" />

        <GaugeText
          value={props.value}
          displayValue={props.displayValue}
          margin={fillCircleMargin}
          radius={radius}
          preFilled={props.preFilled}
          position={0.75}
        />
      </g>

      <g className="gauge-total">
        <circle cx={radius} cy={radius} r={radius - fillCircleMargin} clipPath="url(#cut-off-bottom)" />

        <GaugeText
          value={props.total}
          displayValue={props.displayValue}
          margin={fillCircleMargin}
          radius={radius}
          preFilled={true}
          position={0.25}
        />
      </g>

    </GaugeContainer>
  )
}

implementPropTypes(ScoreGauge, GaugeTypes, {
  total: T.number
}, {
  value: undefined // base prop types force it to 0. I don't know the possible effects of changing base class
})

export {
  ScoreGauge
}
