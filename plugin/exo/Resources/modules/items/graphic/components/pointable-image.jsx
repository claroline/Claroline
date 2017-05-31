import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import tinycolor from 'tinycolor2'
import {Pointer} from './pointer.jsx'
import {SHAPE_RECT} from './../enums'

export class PointableImage extends Component {
  constructor(props) {
    super(props)
    this.onResize = this.onResize.bind(this)
    this.state = {resizes: 0}
  }

  componentDidMount() {
    window.addEventListener('resize', this.onResize)
    // forces re-render based on computed relative coords of pointers
    // (possible only when img ref is available)
    this.img.onload = () => this.onResize()
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.onResize)
  }

  onResize() {
    // "resizes" has no meaning here, we're just forcing a re-render of pointers
    this.setState({resizes: this.state.resizes + 1})
  }

  absToRel(length) {
    // img ref isn't available on first render (async)
    if (this.img) {
      return Math.round(length / (this.props.absWidth / this.img.width))
    }

    return 0
  }

  render() {
    return (
      <div className="pointable-img">
        <div style={{
          position: 'relative',
          cursor: this.props.onClick ? 'crosshair' : 'auto',
          userSelect: 'none'
        }}>
          <img
            ref={el => {
              this.img = el
              this.props.onRef(el)
            }}
            src={this.props.src}
            draggable={false}
            onDragStart={e => e.stopPropagation()}
            onClick={e => this.props.onClick && this.props.onClick(e)}
          />
          {this.props.pointers.map(pointer =>
            <Pointer
              key={`${pointer.absX}-${pointer.absY}`}
              x={this.absToRel(pointer.absX)}
              y={this.absToRel(pointer.absY)}
              type={pointer.type}
              feedback={pointer.feedback}
            />
          )}
          {this.props.areas.map(area =>
            <div
              key={area.id}
              style={{
                position: 'absolute',
                top: this.absToRel(area.top),
                left: this.absToRel(area.left),
                width: this.absToRel(area.width),
                height: this.absToRel(area.height),
                border: `solid 2px ${area.color}`,
                borderRadius: this.absToRel(area.borderRadius),
                backgroundColor: tinycolor(area.color).setAlpha(0.5).toRgbString()
              }}
            >
              {area.number &&
                <div
                  className="area-number"
                  style={{
                    position: 'absolute',
                    top: area.shape === SHAPE_RECT ? '-12px' : '-2px',
                    left: area.shape === SHAPE_RECT ? '-12px' : '-2px'
                  }}
                >
                  {area.number}
                </div>
              }
            </div>
          )}
        </div>
      </div>
    )
  }
}

PointableImage.propTypes = {
  src: T.string.isRequired,
  absWidth: T.number.isRequired,
  onRef: T.func.isRequired,
  onClick: T.func,
  pointers: T.arrayOf(T.shape({
    absX: T.number.isRequired,
    absY: T.number.isRequired,
    type: T.string.isRequired,
    feedback: T.string
  })).isRequired,
  areas: T.arrayOf(T.shape({
    id: T.string.isRequired,
    top: T.number.isRequired,
    left: T.number.isRequired,
    width: T.number.isRequired,
    height: T.number.isRequired,
    borderRadius: T.number.isRequired,
    color: T.string.isRequired,
    number: T.number
  }))
}

PointableImage.defaultProps = {
  onRef: () => {},
  areas: []
}
