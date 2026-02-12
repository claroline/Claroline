import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CLIPBOARD_BUTTON, URL_BUTTON} from '#/main/app/buttons'

const UrlDisplay = (props) =>
  <div id={props.id} className="url-display btn-group w-100">
    <Button
      type={URL_BUTTON}
      label={props.data}
      className="btn btn-outline-primary text-truncate"
      target={props.data}
      onClick={props.onClick}
    />

    <Button
      id={`clipboard-${props.id}`}
      type={CLIPBOARD_BUTTON}
      tooltip="left"
      label={trans('clipboard_copy')}
      className="btn btn-primary"
      icon="fa fa-fw fa-clipboard"
      copy={() => props.data}
      size={props.size}
    />
  </div>

UrlDisplay.propTypes = {
  id: T.string.isRequired,
  data: T.string.isRequired,
  size: T.string,
  onClick: T.func
}

export {
  UrlDisplay
}
