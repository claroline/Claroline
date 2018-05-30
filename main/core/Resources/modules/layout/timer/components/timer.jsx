import React, {Component} from 'react'

import {PropTypes as T} from '#/main/core/scaffolding/prop-types'
import {computeElapsedTime} from '#/main/core/scaffolding/date'

class Timer extends Component {
  constructor(props) {
    super(props)
    this.state = {
      remainingTime: props.totalTime,
      formattedRemainingTime: this.formatTime(props.totalTime),
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
        remainingTime: remainingTime,
        formattedRemainingTime: this.formatTime(remainingTime)
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
    let remainder = time
    const hours = Math.floor(remainder / 3600)
    remainder = remainder % 3600
    const minutes = Math.floor(remainder / 60)
    const seconds = remainder % 60

    let formattedHours = ''
    let formattedMinutes = ''
    let formattedSeconds = ''

    if (hours > 0) {
      formattedHours += `${hours}h`

      if (minutes < 10 && minutes > 0) {
        formattedMinutes += '0'
      } else if (minutes === 0) {
        formattedMinutes += '00m'
      }
      if (seconds < 10) {
        formattedSeconds += '0'
      }
    }
    if (minutes > 0) {
      formattedMinutes += `${minutes}m`

      if (seconds < 10 && hours === 0) {
        formattedSeconds += '0'
      }
    }
    formattedSeconds += `${seconds}s`

    return formattedHours + formattedMinutes + formattedSeconds
  }

  render() {
    return (
      <div className="timer-component">
        {this.state.formattedRemainingTime}
      </div>
    )
  }
}

Timer.propTypes = {
  totalTime: T.number.isRequired,
  startDate: T.string.isRequired,
  onTimeOver: T.func
}

export {
  Timer
}