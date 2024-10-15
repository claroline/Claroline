import React, {useId} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {ThemeIcon} from '#/main/theme/components/icon'
import {fileSize} from '#/main/app/intl'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action'

const FileThumbnail = props => {
  const labelId = useId()

  return (
    <article className={classes('file-preview gap-3', props.className)} aria-labelledby={labelId}>
      <ThemeIcon
        className="file-preview-icon"
        mimeType={props.file.type || props.file.mimeType}
        set="resources"
        size="xs"
      />

      <div className="file-preview-title" id={labelId}>
        {props.file.name || props.file.url}
        {props.file.size && <small className="text-body-secondary">{fileSize(props.file.size)}</small>}
      </div>

      <Toolbar
        buttonName="btn btn-text-secondary p-2"
        actions={[
          {
            name: 'download',
            type: URL_BUTTON,
            label: trans('download', {}, 'actions'),
            disabled: props.disabled,
            displayed: !!props.downloadUrl,
            target: props.downloadUrl
          }, {
            name: 'delete',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-times',
            label: trans('delete', {}, 'actions'),
            tooltip: 'bottom',
            disabled: props.disabled,
            displayed: !!props.delete,
            callback: props.delete
          }
        ]}
      />
    </article>
  )
}

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
  downloadUrl: T.oneOfType([T.array, T.string])
}

FileThumbnail.defaultProps = {
  disabled: false
}

export {
  FileThumbnail
}
