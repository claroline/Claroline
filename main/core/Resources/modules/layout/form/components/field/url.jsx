import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {copy} from '#/main/app/clipboard'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {Button} from '#/main/app/action/components/button'

const Url = props =>
  <div className="input-group">
    <input
      id={props.id}
      type="text"
      className={classes('form-control', props.className)}
      value={props.value || ''}
      disabled={props.disabled}
      placeholder={props.placeholder}
      onChange={(e) => props.onChange(e.target.value)}
    />

    <span className="input-group-btn">
      <Button
        id={`clipboard-${props.id}`}
        type="callback"
        tooltip="left"
        label={trans('clipboard_copy')}
        className="btn"
        icon="fa fa-fw fa-clipboard"
        callback={() => copy(props.value)}
      />
    </span>
  </div>

implementPropTypes(Url, FormFieldTypes, {
  value: T.string
})

export {
  Url
}
