import React, {Component} from 'react'
import moment from 'moment'

import {PropTypes as T} from '#/main/core/scaffolding/prop-types'
import {computeElapsedTime} from '#/main/core/scaffolding/date'

import{CountGauge} from '#/main/core/layout/gauge/components/count-gauge'


class Timer extends Component {
  constructor(props) {
    super(props)
    this.state = {
      remainingTime: props.totalTime,
      timer: null
    }
    this.updateTimer = this.updateTimer.bind(this)
  }

  componentDidMount() {
    this.setState({timer: setInterval(this.updateTimer, 1000)})
  }

  componentWillUnmount() {
    if (this.state.timer) {
      clearInterval(this.state.timer)
    }
  }

  updateTimer() {
    const elapsedTime = computeElapsedTime(this.props.startDate)
    const diff = this.props.totalTime - elapsedTime
    const remainingTime = diff > 0 ? diff : 0

    if (this.state.remainingTime > 0) {
      this.setState({
        remainingTime: remainingTime
      })
    } else {
      if (this.props.onTimeOver) {
        this.props.onTimeOver()
      }
      if (this.state.timer) {
        clearInterval(this.state.timer)
      }
    }
  }


  formatTime(time) {
    const endTime = moment().hours(0).minutes(0).seconds(0).milliseconds(0)
    return endTime.add(time, 'seconds').format('HH:mm:ss')
  }

  render() {
    return (
      <CountGauge
        className="timer-component"
        value={this.state.remainingTime}
        total={this.props.totalTime}
        displayValue={() => this.formatTime(this.state.remainingTime)}
        type={this.props.type}
        width={70}
        height={70}
      />
    )
  }
}

Timer.propTypes = {
  totalTime: T.number.isRequired,
  startDate: T.string.isRequired,
  onTimeOver: T.func,
  type: T.string
}

export {
  Timer
}
