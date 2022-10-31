import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Liquid} from '#/main/app/animation/components/liquid'

import {Gauge as GaugeTypes} from '#/main/core/layout/gauge/prop-types'
import {
  GaugeContainer,
  GaugeBorder,
  GaugeText
} from '#/main/core/layout/gauge/components/gauge'

const LIQUID_GAUGE_CONFIG = {
  // The gauge minimum value.
  minValue: 0,
  // The gauge maximum value.
  maxValue: 100,
  // The outer circle thickness as a percentage of it's radius.
  circleThickness: 0.08,
  // The size of the gap between the outer circle and wave circle as a percentage of the outer circles radius.
  circleFillGap: 0.06
}

const LiquidGauge = props => {
  const radius = Math.min(parseInt(props.width), parseInt(props.height)) / 2
  const fillPercent = Math.max(LIQUID_GAUGE_CONFIG.minValue, Math.min(LIQUID_GAUGE_CONFIG.maxValue, props.value))/LIQUID_GAUGE_CONFIG.maxValue

  const circleThickness = LIQUID_GAUGE_CONFIG.circleThickness * radius
  const circleFillGap = LIQUID_GAUGE_CONFIG.circleFillGap * radius
  const fillCircleMargin = circleThickness + circleFillGap

  return (
    <GaugeContainer
      className={classes('liquid-gauge', props.className)}
      type={props.type}
      width={parseInt(props.width)}
      height={parseInt(props.height)}
      radius={radius}
    >
      <GaugeBorder
        radius={radius}
        thickness={circleThickness}
        preFilled={true}
        color={props.color}
      />

      <GaugeText
        value={props.value}
        displayValue={props.displayValue}
        margin={fillCircleMargin}
        radius={radius}
        preFilled={props.preFilled}
        color={props.color}
      />

      <Liquid
        id={props.id}
        className="gauge-liquid"
        fillPercent={fillPercent}
        radius={radius}
        margin={fillCircleMargin}
        preFilled={props.preFilled}
        wave={props.wave}
        color={props.color}
      >
        <GaugeText
          className="gauge-liquid-text"
          value={props.value}
          displayValue={props.displayValue}
          margin={fillCircleMargin}
          radius={radius}
          preFilled={props.preFilled}
          color={props.color}
        />
      </Liquid>
    </GaugeContainer>
  )
}

implementPropTypes(LiquidGauge, GaugeTypes, {
  /**
   * An unique identifier for the Gauge.
   */
  id: T.string.isRequired,

  /**
   * Makes the liquid wave.
   */
  wave: T.bool
}, {
  wave: true
})

export {
  LiquidGauge
}
