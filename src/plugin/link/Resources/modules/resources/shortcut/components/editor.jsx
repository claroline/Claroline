import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/link/resources/shortcut/store'

const ShortcutEditor = (props) =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.FORM_NAME}
    target={(shortcut) => ['apiv2_shortcut_update', {id: shortcut.id}]}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'target',
            type: 'resource',
            label: trans('resource'),
            required: true
          }
        ]
      }
    ]}
  />

ShortcutEditor.propTypes = {
  path: T.string.isRequired
}

export {
  ShortcutEditor
}
