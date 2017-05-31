import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {getContentDefinition} from './../../../contents/content-types'

export class ContentInput extends Component {
  render() {
    return (
      <div
        className={classes('modal-item-entry', {'selected': this.props.selected})}
        role="option"
        onMouseOver={() => this.props.handleItemMouseOver(this.props.type)}
        onClick={() => getContentDefinition(this.props.type).browseFiles ?
          this.input.click() :
          this.props.handleSelect(getContentDefinition(this.props.type).mimeType)
        }
      >
        {getContentDefinition(this.props.type).browseFiles &&
          <input
            type="file"
            accept={getContentDefinition(this.props.type).browseFiles + '/*'}
            style={{display: 'none'}}
            ref={input => this.input = input}
            onChange={() => {
              if (this.input.files[0]) {
                const file = this.input.files[0]
                const item = this.props.handleSelect(file.type)
                this.props.handleFileUpload(item.id, file)
              }
            }}
          />
        }
        <span className={classes('item-icon item-icon-lg', getContentDefinition(this.props.type).icon)}></span>
      </div>
    )
  }
}

ContentInput.propTypes = {
  type: T.string.isRequired,
  selected: T.bool.isRequired,
  handleSelect: T.func.isRequired,
  handleFileUpload: T.func.isRequired,
  handleItemMouseOver:  T.func.isRequired
}
