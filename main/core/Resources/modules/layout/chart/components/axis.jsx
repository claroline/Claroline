import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'
import {select} from 'd3-selection'
import {axisLeft, axisBottom} from 'd3-axis'
import {timeDay} from 'd3-time'
import {dateToDisplayFormat} from '#/main/core/scaffolding/date'

import {
  AXIS_TYPE_X,
  AXIS_TYPE_Y,
  DATE_DATA_TYPE,
  NUMBER_DATA_TYPE,
  STRING_DATA_TYPE
} from '#/main/core/layout/chart/enums'

class Axis extends Component {

  componentDidUpdate() {
    this.renderAxis()
  }

  componentDidMount() {
    this.renderAxis()
  }

  renderAxis() {
    if (this.props.values && this.props.scale) {
      const node = this.axisNode
      const rotate = this.axisLabelRotate()
      const axisFormat = this.formatAxis()
      const axis = select(node).call(axisFormat)
      if (rotate !== '') {
        axis.selectAll('g.tick text')
          .attr('transform', () => rotate)
          .style('text-anchor', 'end')
          .attr('dx', '-.8em')
          .attr('dy', '.15em')
      }
      if (this.props.label.grid) {
        select(node).append('g')
          .attr('class', 'grid')
          .call(this.formatGrid(axisFormat))
      }
    }
  }

  formatAxis() {
    switch (this.props.type) {
      case AXIS_TYPE_X: {
        let axis = axisBottom(this.props.scale)
        switch (this.props.dataType) {
          case DATE_DATA_TYPE: {
            axis.tickFormat(dateToDisplayFormat)
            let dist = Math.floor(this.props.values.length/10)
            return this.props.values.length > 10 ?
              axis.ticks(timeDay.filter(d => timeDay.count(0, d) % dist === Math.min(2, dist - 1))) :
              axis.tickValues(this.props.values)
          }
          case NUMBER_DATA_TYPE: {
            return this.props.ticksAsValues ? axis.tickValues(this.props.values) : axis.ticks(10)
          }
          case STRING_DATA_TYPE: {
            return axis.tickValues(this.props.values)
          }
        }
        return axisBottom(this.props.scale).tickValues(this.props.values)
      }
      case AXIS_TYPE_Y: {
        let axis = axisLeft(this.props.scale)
        return this.props.ticksAsValues ? axis.tickValues(this.props.values) : axis.ticks(Math.min(10, this.props.height/20))
      }

    }
  }

  formatGrid(axis) {
    axis.tickFormat('')
    switch (this.props.type) {
      case AXIS_TYPE_X:
        return axis.tickSize(-this.props.height)
      case AXIS_TYPE_Y: {
        return axis.tickSize(-this.props.width)
      }
    }
  }
  
  axisLabelRotate() {
    if (this.props.type === AXIS_TYPE_Y || this.props.values.length === 0 ) {
      return ''
    }
    
    switch (this.props.dataType) {
      case DATE_DATA_TYPE:
        return this.props.values.length > 10 ? 'rotate(-20)' : ''
      case STRING_DATA_TYPE: {
        let maxLength = Math.max(...this.props.values.map(v => v.length))
        return maxLength > 10 && this.props.values.length > 5 ? 'rotate(-20)' : ''
      }
    }
    
    return ''
  }

  render() {
    let transform = '', labelTransform = '', labelWidth = this.props.label.show && this.props.label.text.length * 7
    let hasRotate = this.axisLabelRotate() !== ''
    switch (this.props.type) {
      case AXIS_TYPE_X:
        transform = `translate(0, ${this.props.height})`
        labelTransform = `translate(${this.props.width / 2}, ${hasRotate ? (this.props.margin.bottom - 10) : 40})`
        break
      case AXIS_TYPE_Y:
        transform = ''
        labelTransform = `translate(${0 - this.props.margin.left + 20}, ${(this.props.height + this.props.margin.top - labelWidth) / 2})rotate(-90)`
        break
    }

    return (
      <g className="axis" ref={(node) => {this.axisNode = node}} transform={transform}>
        {this.props.label.show &&
          <text className="axis-label" fill="#000" transform={labelTransform}>{this.props.label.text}</text>
        }
      </g>
    )
  }
}

Axis.propTypes = {
  height: T.number.isRequired,
  width: T.number.isRequired,
  values: T.array,
  scale: T.func,
  ticksAsValues: T.bool,
  type: T.oneOf([AXIS_TYPE_X, AXIS_TYPE_Y]).isRequired,
  dataType: T.oneOf([DATE_DATA_TYPE, NUMBER_DATA_TYPE, STRING_DATA_TYPE]).isRequired,
  label: T.shape({
    show: T.bool.isRequired,
    text: T.string.isRequired,
    grid: T.bool
  }),
  margin: T.shape({
    top: T.number.isRequired,
    right: T.number.isRequired,
    bottom: T.number.isRequired,
    left: T.number.isRequired
  }).isRequired
}

Axis.defaultProps = {
  label: {
    show: false,
    label: '',
    grid: false
  },
  ticksAsValues: false
}

export {
  Axis
}
