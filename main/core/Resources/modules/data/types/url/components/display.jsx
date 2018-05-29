import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {copy} from '#/main/app/clipboard'
import {Button} from '#/main/app/action/components/button'

const UrlDisplay = (props) =>
  <div id={props.id} className="url-display">
    <Button
      type="url"
      label={props.data}
      className="btn-link"
      target={props.data}
      primary={true}
    />

    <Button
      id={`clipboard-${props.id}`}
      type="callback"
      tooltip="left"
      label={trans('clipboard_copy')}
      className="btn-link"
      icon="fa fa-fw fa-clipboard"
      callback={() => copy(props.data)}
    />
  </div>

UrlDisplay.propTypes = {
  id: T.string.isRequired,
  data: T.string.isRequired
}

export {
  UrlDisplay
}
