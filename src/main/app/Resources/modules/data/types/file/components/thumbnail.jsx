import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {ThemeIcon} from '#/main/theme/components/icon'
import {fileSize} from '#/main/app/intl'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, DOWNLOAD_BUTTON} from '#/main/app/buttons'

const FileThumbnail = props =>
  <div className={classes('file-preview gap-3', props.className)}>
    <ThemeIcon
      className="file-preview-icon"
      mimeType={props.file.type || props.file.mimeType}
      set="resources"
    />

    <div className="file-preview-title">
      {props.file.name || props.file.url}
      {props.file.size && <small className="text-body-secondary">{fileSize(props.file.size)}</small>}
    </div>

    {props.delete &&
        <Button
          className="file-preview-delete btn btn-text-secondary"
          type={CALLBACK_BUTTON}
          icon="fa fa-fw fa-times"
          label={trans('delete', {}, 'actions')}
          tooltip="bottom"
          disabled={props.disabled}
          callback={props.delete}
        />
    }

    {props.download &&
        <Button
          className="file-preview-delete btn btn-text-secondary"
          type={DOWNLOAD_BUTTON}
          icon="fa fa-fw fa-download"
          label={trans('download', {}, 'actions')}
          tooltip="bottom"
          disabled={props.disabled}
          file={props.download}
        />
    }
  </div>

FileThumbnail.propTypes = {
  className: T.string,
  disabled: T.bool.isRequired,
  file: T.shape({
    type: T.string,
    mimeType: T.string, // for retro compatibility
    name: T.string,
    size: T.number,
    url: T.string
  }).isRequired,
  delete: T.func,
  download: T.object
}

FileThumbnail.defaultProps = {
  disabled: false
}

export {
  FileThumbnail
}
