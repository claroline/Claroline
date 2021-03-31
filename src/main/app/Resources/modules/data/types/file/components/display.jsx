import React from 'react'
import {PropTypes as T} from 'prop-types'

const FileDisplay = props => {
  if (props.data) {
    let file = props.data
    if (Array.isArray(props.data)) {
      file = file[0]
    }

    return (
      <a href={file.url}>
        {file.name || file.url}
      </a>
    )
  }

  return null
}

FileDisplay.propTypes = {
  // it's named `data` to be able to use it as is in Data* representation
  data: T.oneOfType([
    T.arrayOf( // for retro compatibility old file types could have multiple files
      T.shape({
        name: T.string,
        mimeType: T.string,
        url: T.string.isRequired
      }).isRequired
    ),
    T.shape({
      name: T.string,
      mimeType: T.string,
      url: T.string.isRequired
    }).isRequired
  ])
}

export {
  FileDisplay
}
