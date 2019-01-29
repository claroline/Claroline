/* eslint-ignore react/no-string-refs, react/no-find-dom-node */

import React, {Component} from 'react'
import ReactDOM from 'react-dom'
import classes from 'classnames'
import uniqueId from 'lodash/uniqueId'
import Overlay from 'react-bootstrap/lib/Overlay'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {TooltipOverlay} from '#/main/app/overlay/tooltip/components/overlay'

import {Calendar} from '#/main/core/layout/calendar/components/calendar'
import {Calendar as CalendarTypes} from '#/main/core/layout/calendar/prop-types'

const CalendarPopover = props =>
  <div
    className={classes('calendar-popover', props.position)}
    style={props.style}
  >
    <Calendar
      selected={props.selected}
      onChange={props.onChange}
      minDate={props.minDate}
      maxDate={props.maxDate}
      time={props.time}
      minTime={props.minTime}
      maxTime={props.maxTime}
    />
  </div>

implementPropTypes(CalendarPopover, CalendarTypes, {
  // added by the Overlay
  style: T.object,

  // for popover
  position: T.string.isRequired
})

// class is required because we use references.
class CalendarPicker extends Component {
  constructor(props) {
    super(props)

    this.state = {
      autoClose : !this.props.time,
      open: false,
      active: false
    }
  }

  toggle() {
    this.setState({ open: !this.state.open })
  }

  onChange(value) {
    this.props.onChange(value)

    if (this.state.autoClose) {
      this.setState({ open: false })
    }
  }

  // I use TooltipOverlay + raw <button> because of the ref (stateless component can not get refs)
  render() {
    return (
      <div className={classes('calendar-picker', {
        open : this.state.active
      })}>
        <TooltipOverlay
          id={uniqueId('datepicker_')}
          position="right"
          tip={trans(!this.state.open ? 'open_calendar':'close_calendar')}
          disabled={this.props.disabled}
        >
          <button
            type="button"
            role="button"
            ref="_openPicker"
            className={classes('btn', this.props.className)}
            disabled={this.props.disabled}
            onClick={(e) => {
              if (!this.props.disabled) {
                this.toggle()
              }

              e.preventDefault()
              e.stopPropagation()
              e.target.blur()
            }}
          >
            <span className={this.props.icon} aria-hidden="true" />
            <span className="sr-only">
              {this.props.label || trans(this.state.open ? 'open_calendar':'close_calendar')}
            </span>
          </button>
        </TooltipOverlay>

        <Overlay
          show={this.state.open}
          rootClose={true}
          container={this}
          placement={this.props.position}
          onHide={() => this.setState({ open: false })}
          onExited={() => this.setState({ active: false })}
          onEnter={() => this.setState({ active: true })}
          target={() => ReactDOM.findDOMNode(this.refs._openPicker)}
        >
          <CalendarPopover
            position={this.props.position}
            selected={this.props.selected}
            onChange={this.onChange.bind(this)}
            minDate={this.props.minDate}
            maxDate={this.props.maxDate}
            time={this.props.time}
            minTime={this.props.minTime}
            maxTime={this.props.maxTime}
          />
        </Overlay>
      </div>
    )
  }
}

implementPropTypes(CalendarPicker, CalendarTypes, {
  // for button
  label: T.string,
  className: T.string,
  icon: T.string,
  disabled: T.bool,
  // for popover
  position: T.oneOf(['top', 'right', 'bottom', 'left'])
}, {
  // for button
  className: 'btn-default',
  icon: 'fa fa fa-fw fa-calendar',
  disabled: false,
  // for popover
  position: 'bottom'
})

export {
  CalendarPicker
}
