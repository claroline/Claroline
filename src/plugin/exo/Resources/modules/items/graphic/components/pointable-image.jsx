import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import tinycolor from 'tinycolor2'

import {Pointer} from '#/plugin/exo/items/graphic/components/pointer'

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
            onClick={this.props.onClick}
          />
          {this.props.pointers.map(pointer =>
            <Pointer
              key={`${pointer.absX}-${pointer.absY}`}
              x={this.absToRel(pointer.absX)}
              y={this.absToRel(pointer.absY)}
              type={pointer.type}
              feedback={pointer.feedback}
              onClick={(e) => this.props.onPointerClick(pointer, e)}
            />
          )}
          {this.props.hasExpectedAnswers && this.props.areas.map(area =>
            <div
              className={classes('pointer', this.props.type, {
                'cursor-pointer': !!this.props.onPointerClick
              })}
              key={area.id}
              style={{
                position: 'absolute',
                top: this.absToRel(area.top),
                left: this.absToRel(area.left),
                width: this.absToRel(area.width),
                height: this.absToRel(area.height),
                border: `solid 2px ${area.color || '#000000'}`,
                borderRadius: this.absToRel(area.borderRadius),
                backgroundColor: tinycolor(area.color).setAlpha(0.5).toRgbString()
              }}
              onClick={(e) => this.props.onPointerClick(area, e)}
            >
              {area.number &&
                <div
                  className={classes('area-number position-absolute start-0 top-0 translate-middle rounded-pill px-2 fw-bold', {
                    'text-light': tinycolor(area.color) && tinycolor(area.color).isDark(),
                    'text-dark': tinycolor(area.color) && tinycolor(area.color).isLight()
                  })}
                  style={{background: tinycolor(area.color).toRgbString()}}
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
  type: T.string,
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
  })),
  hasExpectedAnswers: T.bool.isRequired,
  onPointerClick: T.func
}

PointableImage.defaultProps = {
  onRef: () => {},
  areas: [],
  hasExpectedAnswers: true
}
