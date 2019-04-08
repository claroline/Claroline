import React, {Component} from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

function computeTime(value) {
  const restHours = value % 3600
  const hours = (value - restHours) / 3600

  const seconds = restHours % 60
  const minutes = (restHours - seconds) / 60

  return {
    hours: Math.floor(hours),
    minutes: Math.floor(minutes),
    seconds: Math.floor(seconds)
  }
}

class TimeInput extends Component {
  constructor(props) {
    super(props)

    this.state = computeTime(this.props.value)

    this.changeHours = this.changeHours.bind(this)
    this.changeMinutes = this.changeMinutes.bind(this)
    this.changeSeconds = this.changeSeconds.bind(this)
    this.onChange = this.onChange.bind(this)
  }

  componentDidUpdate(prevProps) {
    if (prevProps.value !== this.props.value) {
      this.setState(computeTime(this.props.value))
    }
  }

  changeHours(e) {
    this.onChange('hours', Math.floor(e.target.value))
  }

  changeMinutes(e) {
    this.onChange('minutes', Math.floor(e.target.value))
  }

  changeSeconds(e) {
    this.onChange('seconds', Math.floor(e.target.value))
  }

  onChange(type, value) {
    this.setState({[type]: parseInt(value) || 0}, () => {
      this.props.onChange(
        this.state.hours * 3600 + this.state.minutes * 60 + this.state.seconds
      )
    })
  }

  render() {
    return (
      <div className={classes('input-group', this.props.className, {
        [`input-group-${this.props.size}`]: !!this.props.size
      })}>
        <input
          className="form-control text-right"
          type="number"
          min={0}
          disabled={this.props.disabled}
          value={this.state.hours}
          onChange={this.changeHours}
        />

        <span className="input-group-addon">
          <span className="hidden-xs">{trans('hours')}</span>
          <span className="visible-xs">{trans('hours_short')}</span>
        </span>

        <input
          className="form-control text-right"
          type="number"
          min={0}
          disabled={this.props.disabled}
          value={this.state.minutes}
          onChange={this.changeMinutes}
        />

        <span className="input-group-addon">
          <span className="hidden-xs">{trans('minutes')}</span>
          <span className="visible-xs">{trans('minutes_short')}</span>
        </span>

        <input
          className="form-control text-right"
          type="number"
          min={0}
          disabled={this.props.disabled}
          value={this.state.seconds}
          onChange={this.changeSeconds}
        />

        <span className="input-group-addon">
          <span className="hidden-xs">{trans('seconds')}</span>
          <span className="visible-xs">{trans('seconds_short')}</span>
        </span>
      </div>
    )
  }
}

implementPropTypes(TimeInput, FormFieldTypes, {
  value: T.number,

  min: T.number, // todo : implement
  max: T.number // todo : implement
}, {
  value: 0
})

export {
  TimeInput
}
