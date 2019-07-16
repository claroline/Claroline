import React, {Component, Fragment} from 'react'
import moment from 'moment'

import {theme} from '#/main/app/config'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'

import {now} from '#/main/app/intl/date'
import {Clock} from '#/main/app/animation/components/clock'
import {Calendar} from '#/main/core/layout/calendar/components/calendar'

const ClockDropdown = () =>
  <div className="app-header-dropdown dropdown-menu dropdown-menu-right">
    <div className="app-header-dropdown-header">
      <Clock />
    </div>

    <Calendar
      time={false}
      showCurrent={false}
    />
  </div>

class ClockMenu extends Component {
  constructor(props) {
    super(props)

    this.state = {
      date: now()
    }
  }

  componentDidMount() {
    this.tick = setInterval(() => this.setState({date: now()}), 1000)
  }

  componentWillUnmount() {
    clearInterval(this.tick)
  }

  render() {
    const date = moment(this.state.date)

    return (
      <Button
        id="app-clock"
        type={MENU_BUTTON}
        className="app-header-btn app-header-item app-header-clock"
        icon={
          <Fragment>
            <h1 className="h4">{date.format('ll')}</h1>
            {date.format('LT')}

            <link rel="stylesheet" type="text/css" href={theme('claroline-distribution-plugin-agenda-clock')} />
          </Fragment>
        }
        label={date.format('LLLL')}
        tooltip="bottom"
        menu={
          <ClockDropdown

          />
        }
      />
    )
  }
}

export {
  ClockMenu
}
