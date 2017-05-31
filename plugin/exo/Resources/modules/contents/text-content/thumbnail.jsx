import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

export class TextContentThumbnail extends Component {
  render() {
    return (
      <div className="text-content-thumbnail">
        {this.props.data &&
          <div dangerouslySetInnerHTML={{ __html: this.props.data }}>
          </div>
        }
      </div>
    )
  }
}

TextContentThumbnail.propTypes = {
  data: T.string,
  type: T.string.isRequired
}