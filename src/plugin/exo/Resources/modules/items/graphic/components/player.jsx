import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans, transChoice} from '#/main/app/intl/translation'

import {POINTER_PLACED} from '#/plugin/exo/items/graphic/constants'
import {PointableImage} from '#/plugin/exo/items/graphic/components/pointable-image'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

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
          <p className="fs-sm text-body-secondary mb-0">
            <span className="fa fa-circle-info me-2" aria-hidden={true} />
            {transChoice('graphic_pointers_left', this.state.pointersLeft, {count: this.state.pointersLeft}, 'quiz')}
          </p>

          <Button
            className="btn btn-body"
            type={CALLBACK_BUTTON}
            icon="fa fa-fw fa-undo"
            label={trans('cancel', {}, 'actions')}
            callback={this.onUndo}
            size="sm"
            disabled={this.props.disabled || 0 === this.state.pointers.length}
          />
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
