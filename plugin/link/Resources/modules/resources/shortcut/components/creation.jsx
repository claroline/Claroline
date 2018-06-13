import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {selectors} from '#/main/core/resource/modals/creation/store'

import {RESOURCE_TYPE} from '#/main/core/resource/data/types'

const ShortcutForm = props =>
  <FormContainer
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
            type: RESOURCE_TYPE,
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
