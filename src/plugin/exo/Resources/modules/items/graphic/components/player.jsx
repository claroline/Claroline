import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'

import {POINTER_PLACED} from '#/plugin/exo/items/graphic/constants'
import {PointableImage} from '#/plugin/exo/items/graphic/components/pointable-image'

class GraphicPlayer extends Component {
  constructor(props) {
    super(props)
    this.onClickImage = this.onClickImage.bind(this)
    this.onUndo = this.onUndo.bind(this)
    this.state = {
      pointers: props.answer,
      pointersLeft: props.item.pointers - props.answer.length
    }
  }

  onClickImage(e) {
    if (this.state.pointersLeft > 0) {
      const factor = this.props.item.image.width / this.img.width
      const imgRect = this.img.getBoundingClientRect()
      const clientX = e.clientX - imgRect.left
      const clientY = e.clientY - imgRect.top
      const absX = Math.round(clientX * factor)
      const absY = Math.round(clientY * factor)
      const newPointer = {
        x: absX,
        y: absY
      }
      this.setState({
        pointers: [...this.state.pointers, newPointer],
        pointersLeft: this.state.pointersLeft - 1
      }, () => this.props.onChange(this.state.pointers))
    }
  }

  onUndo() {
    this.setState({
      pointers: this.state.pointers.slice(
        0,
        this.state.pointers.length - 1
      ),
      pointersLeft: this.state.pointersLeft + 1
    }, () => this.props.onChange(this.state.pointers))
  }

  render() {
    return (
      <div className="graphic-player">
        <div className="top-controls">
          <span>
            {trans('graphic_pointers_left', {}, 'quiz')}{this.state.pointersLeft}
          </span>
          {!this.props.disabled && this.state.pointers.length > 0 &&
            <button
              type="button"
              className="btn btn-default"
              onClick={this.onUndo}
            >
              <span className="fa fa-fw fa-undo"/>&nbsp;{trans('undo', {}, 'quiz')}
            </button>
          }
        </div>
        <PointableImage
          src={this.props.item.image.data || asset(this.props.item.image.url)}
          absWidth={this.props.item.image.width}
          onRef={el => this.img = el}
          onClick={this.props.disabled ? undefined :this.onClickImage}
          pointers={this.state.pointers.map(pointer => ({
            type: POINTER_PLACED,
            absX: pointer.x,
            absY: pointer.y
          }))}
        />
      </div>
    )
  }
}

GraphicPlayer.propTypes = {
  item: T.shape({
    image: T.oneOfType([
      T.shape({
        data: T.string.isRequired,
        width: T.number.isRequired
      }),
      T.shape({
        url: T.string.isRequired,
        width: T.number.isRequired
      })
    ]).isRequired,
    pointers: T.number.isRequired
  }).isRequired,
  answer: T.array.isRequired,
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

GraphicPlayer.defaultProps = {
  answer: [],
  disabled: false
}

export {
  GraphicPlayer
}
