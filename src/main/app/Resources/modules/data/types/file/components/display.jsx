import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {getType} from '#/main/app/data/types/file/utils'
import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'

const FileDisplay = props => {
  let files = []
  if (!isEmpty(props.data)) {
    files = Array.isArray(props.data) ? props.data : [props.data]
  }

  if (!isEmpty(files)) {
    return (
      <div className="file-thumbnails">
        {files.map((file, index) =>
          <FileThumbnail
            key={file.id || file.name || index}
            type={getType(file.mimeType || file.type)}
            data={file}
          />
        )}
      </div>
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
