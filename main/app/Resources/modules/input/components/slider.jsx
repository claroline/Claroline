/* global document */

import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import times from 'lodash/times'

import {scaleLinear, scaleThreshold} from 'd3-scale'

import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

class Slider extends Component {
  constructor(props) {
    super(props)

    let min = this.props.min
    if (this.props.scale) {
      min = this.props.scale[0]
    }
    let max = this.props.max
    if (this.props.scale) {
      max = this.props.scale[this.props.scale.length - 1]
    }

    const domain = times(this.props.scale.length, (i) => {
      const start = ((this.props.scale[i] - min) / (max - min)) * 100

      let end
      if (i + 1 <= this.props.scale.length) {
        end = ((this.props.scale[i + 1] - min) / (max - min)) * 100
      } else {
        end = start
      }

      return start + ((end - start) / 2)
    })

    this.state = {
      active: false,
      min: min,
      max: max,
      scale: this.props.scale ?
        scaleThreshold()
          .domain(domain)
          .range(this.props.scale)
        :
        scaleLinear()
          .domain([0, 100])
          .range([this.props.min, this.props.max])
          .clamp(true)
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
        const newPos = ((e.clientX - rect.left) / rect.width) * 100
        const newValue = this.state.scale(newPos)

        if (newValue !== this.props.value) {
          if (newValue < this.props.min) {
            this.props.onChange(this.props.min)
          } else if (newValue > this.props.max) {
            this.props.onChange(this.props.max)
          } else {
            this.props.onChange(newValue)
          }
        }
      }
    }
  }

  render() {
    const width = ((this.props.value - this.state.min) / (this.state.max - this.state.min)) * 100

    return (
      <div
        className="slider progress progress-xs"
        ref={this.slider}
        onMouseDown={this.onDragStart}
        onTouchStart={this.onDragStart}
        onClick={this.onMove}
      >
        <div
          className="progress-bar progress-bar-primary"
          role="progressbar"
          aria-valuenow={this.props.value}
          aria-valuemin={this.state.min}
          aria-valuemax={this.state.max}
          style={{
            width: `${width}%`
          }}
        >
          <span className="sr-only">{this.props.value}</span>
        </div>

        {this.props.scale && this.props.scale
          .map((tick, tickIndex) =>
            <div
              key={tick}
              className={classes('slider-tick', {'sr-only': 0 === tickIndex || this.props.scale.length - 1 === tickIndex})}
              style={{
                left:  (((tick - this.state.min) / (this.state.max - this.state.min)) * 100)+'%'
              }}
            >
              {tick}
            </div>
          )
        }

        <TooltipOverlay
          id={this.props.id+'-tooltip'}
          tip={this.props.label || (this.props.value+'')}
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
