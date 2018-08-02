import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/core/resource/modals/creation/store'

const ShortcutForm = props =>
  <FormData
    level={5}
    name={selectors.FORM_NAME}
    dataPart="resource"
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'target',
            label: trans('target_resource', {}, 'resource'),
            type: 'resource',
            required: true,
            onChange: (target) => props.update(target)
          }
        ]
      }
    ]}
  />

ShortcutForm.propTypes = {
  update: T.func.isRequired
}

const ShortcutCreation = connect(
  null,
  () => ({
    update() {
      // todo default set props of the shortcut with target
    }
  })
)(ShortcutForm)

export {
  ShortcutCreation
}
