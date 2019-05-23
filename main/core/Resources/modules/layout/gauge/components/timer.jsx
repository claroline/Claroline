import React, {Component} from 'react'
import moment from 'moment'

import {PropTypes as T} from '#/main/app/prop-types'
import {computeElapsedTime} from '#/main/app/intl/date'

import{CountGauge} from '#/main/core/layout/gauge/components/count-gauge'


class Timer extends Component {
  constructor(props) {
    super(props)

    this.state = {
      remainingTime: props.totalTime,
      timer: null
    }

    this.updateTimer = this.updateTimer.bind(this)
    this.formatTime = this.formatTime.bind(this)
  }

  componentDidMount() {
    this.timer = setInterval(this.updateTimer, 1000)
  }

  componentWillUnmount() {
    if (this.timer) {
      clearInterval(this.timer)
    }
  }

  updateTimer() {
    const elapsedTime = computeElapsedTime(this.props.startDate)
    const diff = this.props.totalTime - elapsedTime
    const remainingTime = diff > 0 ? diff : 0

    if (remainingTime > 0) {
      this.setState({
        remainingTime: remainingTime
      })
    } else {
      if (this.timer) {
        clearInterval(this.timer)
      }

      if (this.props.onTimeOver) {
        this.props.onTimeOver()
      }
    }
  }

  formatTime() {
    const endTime = moment().hours(0).minutes(0).seconds(0).milliseconds(0)

    return endTime.add(this.state.remainingTime, 'seconds').format('HH:mm:ss')
  }

  render() {
    return (
      <CountGauge
        className="timer-component"
        value={this.state.remainingTime}
        total={this.props.totalTime}
        displayValue={this.formatTime}
        type={this.props.type}
        width={this.props.width}
        height={this.props.height}
      />
    )
  }
}

Timer.propTypes = {
  width: T.number,
  height: T.number,
  totalTime: T.number.isRequired,
  startDate: T.string.isRequired,
  onTimeOver: T.func,
  type: T.string
}

export {
  Timer
}
