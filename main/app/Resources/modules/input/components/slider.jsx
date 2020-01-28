/* global document */

import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

class Slider extends Component {
  constructor(props) {
    super(props)

    this.state = {
      active: false
    }

    this.slider = React.createRef()

    this.onDragStart = this.onDragStart.bind(this)
    this.onDragEnd = this.onDragEnd.bind(this)
    this.onMove = this.onMove.bind(this)
  }

  componentDidMount() {
    document.body.addEventListener('mouseup', this.onDragEnd)
    document.body.addEventListener('touchend', this.onDragEnd)

    document.body.addEventListener('mousemove', this.onMove)
  }

  componentWillUnmount() {
    document.body.removeEventListener('mouseup', this.onDragEnd)
    document.body.removeEventListener('touchend', this.onDragEnd)

    document.body.removeEventListener('mousemove', this.onMove)
  }

  onDragStart(e) {
    e.persist() // because I need to access event async (see https://reactjs.org/docs/events.html#event-pooling)

    this.setState({active: true}, () => this.onMove(e))
  }

  onDragEnd() {
    this.setState({active: false})
  }

  onMove(e) {
    if (this.state.active) {
      const rect = this.slider.current.getBoundingClientRect()

      if (e.clientX >= rect.left && e.clientX <= (rect.left + rect.width)) {
        const newPos = Math.round(((e.clientX - rect.left) / rect.width) * 100)
        this.props.onChange(Math.round((newPos / 100) * (this.props.max - this.props.min)))
      }
    }
  }

  render() {
    const width = (this.props.value / (this.props.max - this.props.min)) * 100
    return (
      <div
        className="slider progress progress-xs"
        ref={this.slider}
        onMouseDown={this.onDragStart}
        onTouchStart={this.onDragStart}
      >
        <div
          className="progress-bar progress-bar-primary"
          role="progressbar"
          aria-valuenow={this.props.value}
          aria-valuemin={this.props.min}
          aria-valuemax={this.props.max}
          style={{
            width: width+'%'
          }}
        >
          <span className="sr-only">{this.props.value}</span>
        </div>

        <TooltipOverlay
          id={this.props.id+'-tooltip'}
          tip={this.props.label || this.props.value}
        >
          <button
            className="slider-cursor"
            type="button"
            style={{
              left: `${width}%`
            }}
          >
            <span className="sr-only">change value</span>
          </button>
        </TooltipOverlay>
      </div>
    )
  }
}

Slider.propTypes = {
  id: T.string.isRequired,
  label: T.string,
  value: T.number,
  min: T.number,
  max: T.number,
  scale: T.arrayOf(T.number),
  onChange: T.func.isRequired
}

Slider.defaultProps = {
  value: 0,
  min: 0,
  max: 100
}

export {
  Slider
}
