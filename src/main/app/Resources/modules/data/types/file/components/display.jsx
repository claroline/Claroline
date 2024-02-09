import React from 'react'
import {PropTypes as T} from 'prop-types'
import {FileThumbnail} from '#/main/app/data/types/file/components/thumbnail'

const FileDisplay = props => {
  if (props.data) {

    const files = Array.isArray(props.data) ? props.data : [props.data]
    return (
      <>
        {files.map(file =>
          <a key={file.url} href={file.url} className="mb-1 text-reset text-decoration-none">
            <FileThumbnail file={file} />
          </a>
        )}
      </>
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
