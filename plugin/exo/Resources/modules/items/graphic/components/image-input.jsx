import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {tex} from '#/main/core/translation'

export class ImageInput extends Component {
  render() {
    return (
      <div>
        <input
          type="file"
          accept="image/*"
          className="img-input"
          style={{display: 'none'}}
          ref={input => this.input = input}
          onChange={() => {
            if (this.input.files[0]) {
              this.props.onSelect(this.input.files[0])
            }
          }}
        />
        <button
          type="button"
          className="btn btn-default"
          ref={button => this.button = button}
          onClick={() => this.input.click()}
        >
          <span className="fa fa-fw fa-file-o"/>
          {tex('graphic_pick_image')}
        </button>
      </div>
    )
  }
}

ImageInput.propTypes = {
  onSelect: T.func.isRequired
}
