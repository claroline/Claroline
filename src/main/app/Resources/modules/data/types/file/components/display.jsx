import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'


const FileDisplay = props => {
  if (!isEmpty(props.data)) {
    const files = Array.isArray(props.data) ? props.data : [props.data]

    if (1 === files.length) {
      const file = files[0]

      return (
        <FileThumbnail file={file} downloadUrl={file.url} />
      )
    }

    return (
      <ul className="list-unstyled mb-0">
        {files.map(file =>
          <li key={file.url}>
            <FileThumbnail className="mb-1" file={file} downloadUrl={file.url} />
          </li>
        )}
      </ul>
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
