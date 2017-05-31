import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import tinycolor from 'tinycolor2'

import {tex} from '#/main/core/translation'
import {TooltipButton} from './../../../components/form/tooltip-button.jsx'
import {makeDraggable} from './../../../utils/dragAndDrop'
import {AreaResizer, AreaResizerDraggable} from './area-resizer.jsx'
import {AnswerAreaDragPreview} from './answer-area-drag-preview.jsx'
import {
  SHAPE_RECT,
  SHAPE_CIRCLE,
  TYPE_ANSWER_AREA,
  DIR_N,
  DIR_NE,
  DIR_E,
  DIR_SE,
  DIR_S,
  DIR_SW,
  DIR_W,
  DIR_NW
} from './../enums'

const FRAME_GUTTER = 6
export const AREA_GUTTER = 8
const BORDER_WIDTH = 2
const RESIZER_SIZE = 12

export class AnswerArea extends Component {
  render() {
    if (this.props.isDragging) {
      return null
    }

    const props = this.props
    const isRect = this.props.shape === SHAPE_RECT
    const def = this.props.geometry
    const left = isRect ? def.coords[0].x : def.center.x - def.radius
    const top = isRect ? def.coords[0].y : def.center.y - def.radius
    const width = isRect ? def.coords[1].x - def.coords[0].x : def.radius * 2
    const height = isRect ? def.coords[1].y - def.coords[0].y : def.radius * 2
    const frameWidth = width + (AREA_GUTTER * 2)
    const frameHeight = height + (AREA_GUTTER * 2)
    const innerFrameWidth = frameWidth - BORDER_WIDTH * 2
    const innerFrameHeight = frameHeight - BORDER_WIDTH * 2
    const handleWidth = width + (FRAME_GUTTER + AREA_GUTTER) * 2
    const handleHeight = height + (FRAME_GUTTER + AREA_GUTTER) * 2
    const borderRadius = isRect ? 0 : def.radius
    const halfSizer = RESIZER_SIZE / 2
    const border = BORDER_WIDTH
    const halfBorder = border / 2

    const makeResizer = makeResizerFactory(
      props.resizable,
      props.id,
      RESIZER_SIZE,
      this.el
    )
    const resizers = [
      [-halfSizer - halfBorder, -halfSizer - border, DIR_NW],
      [-halfSizer - halfBorder, innerFrameWidth / 2 - halfSizer, DIR_N],
      [-halfSizer - halfBorder, innerFrameWidth + halfBorder - halfSizer, DIR_NE],
      [innerFrameHeight / 2 - halfSizer, innerFrameWidth + halfBorder - halfSizer, DIR_E],
      [innerFrameHeight + halfBorder - halfSizer, innerFrameWidth + border - halfSizer, DIR_SE],
      [innerFrameHeight + halfBorder - halfSizer, innerFrameWidth / 2 - halfSizer, DIR_S],
      [innerFrameHeight + halfBorder - halfSizer, - halfSizer - halfBorder, DIR_SW],
      [innerFrameHeight / 2 - halfSizer, - halfSizer - halfBorder, DIR_W]
    ]


    return props.connectDragSource(
      <div
        ref={el => this.el = el}
        className={classes('area-handle', {
          selected: props.selected,
          undraggable: !props.canDrag
        })}
        onMouseDown={() => props.onSelect(props.id)}
        style={common({
          padding: FRAME_GUTTER,
          left: left - FRAME_GUTTER - AREA_GUTTER,
          top: top - FRAME_GUTTER - AREA_GUTTER,
          width: handleWidth,
          height: handleHeight
        })}
      >
        <div
          className="area-frame"
          style={common({
            left: FRAME_GUTTER,
            top: FRAME_GUTTER,
            width: frameWidth,
            height: frameHeight,
            borderWidth: BORDER_WIDTH
          })}
        >
          <div className="area"
            style={common({
              left: AREA_GUTTER - BORDER_WIDTH,
              top: AREA_GUTTER - BORDER_WIDTH,
              width: width,
              height: height,
              backgroundColor: tinycolor(props.color).setAlpha(0.5).toRgbString(),
              borderRadius: `${borderRadius}px`,
              border: `solid 2px ${props.color}`
            })}
          />

          {resizers.map(makeResizer)}
        </div>

        {props.selected && !props.isDragging &&
          <div
            className="area-controls"
            style={{
              top: FRAME_GUTTER + AREA_GUTTER
            }}
          >
            <TooltipButton
              id="area-edit"
              className="btn-default btn-sm"
              label={<span className="fa fa-fw fa-pencil" />}
              title={tex('graphic_area_edit')}
              onClick={e => {
                const rect = e.target.classList.contains('btn') ?
                  e.target.getBoundingClientRect() : e.target.parentNode.getBoundingClientRect()
                const containerRect = document.getElementsByClassName('graphic-editor')[0].getBoundingClientRect()
                props.togglePopover(
                  props.id,
                  rect.left + (rect.width / 2) + window.pageXOffset - containerRect.left, // works with position relative container
                  rect.top + rect.height + window.pageYOffset - containerRect.top
                )
              }}
            />
            <TooltipButton
              id="area-edit"
              className="btn-danger btn-sm"
              label={<span className="fa fa-fw fa-trash-o" />}
              title={tex('delete')}
              onClick={() => props.onDelete(props.id)}
            />
          </div>
        }
      </div>
    )
  }
}

AnswerArea.propTypes = {
  id: T.string.isRequired,
  shape: T.oneOf([SHAPE_RECT, SHAPE_CIRCLE]),
  color: T.string.isRequired,
  selected: T.bool.isRequired,
  onSelect: T.func.isRequired,
  canDrag: T.bool.isRequired,
  isDragging: T.bool.isRequired,
  connectDragSource: T.func.isRequired,
  togglePopover: T.func.isRequired,
  onDelete: T.func.isRequired,
  resizable: T.bool.isRequired,
  geometry: T.oneOfType([
    T.shape({
      coords: T.arrayOf(T.shape({
        x: T.number.isRequired,
        y: T.number.isRequired
      })).isRequired
    }),
    T.shape({
      center: T.shape({
        x: T.number.isRequired,
        y: T.number.isRequired
      }).isRequired,
      radius: T.number.isRequired
    })
  ]).isRequired
}

AnswerArea.defaultProps = {
  resizable: true
}

export const AnswerAreaDraggable = makeDraggable(
  AnswerArea,
  TYPE_ANSWER_AREA,
  AnswerAreaDragPreview,
  props => ({
    id: props.id,
    type: TYPE_ANSWER_AREA,
    props: props
  })
)

function common(rules) {
  return Object.assign(rules, {
    position: 'absolute'
  })
}

function makeResizerFactory(resizable, areaId, size, el) {
  return (geometry, index) => {
    const Resizer = React.createElement(
      resizable ? AreaResizerDraggable : AreaResizer,
      Object.assign(geometry, {
        areaId,
        size,
        areaEl: el,
        connectDragSource: el => el,
        key: `${areaId}-${index}`,
        top: geometry[0],
        left: geometry[1],
        position: geometry[2]
      })
    )

    return Resizer
  }
}
