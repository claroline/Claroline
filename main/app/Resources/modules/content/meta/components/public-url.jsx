import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'
import {copy} from '#/main/app/clipboard'

import {Button} from '#/main/app/action/components/button'

const ContentPublicUrl = props => {
  const publicUrl = url([...props.url, true])

  return (
    <div className={classes('content-public-url', props.className)}>
      <Button
        type="url"
        label={publicUrl}
        className="btn-link"
        target={publicUrl}
      />

      <Button
        id={`clipboard-${toKey(publicUrl)}`}
        type="callback"
        tooltip="left"
        label={trans('clipboard_copy')}
        className="btn-link"
        icon="fa fa-fw fa-clipboard"
        callback={() => copy(publicUrl)}
      />
    </div>
  )
}

ContentPublicUrl.propTypes = {
  className: T.string,
  url: T.oneOfType([T.array, T.string])
}

ContentPublicUrl.defaultProps = {
  meta: {}
}

export {
  ContentPublicUrl
}
