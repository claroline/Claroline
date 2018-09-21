import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions, selectors} from '#/main/core/resource/modals/creation/store'

const ShortcutForm = props =>
  <FormData
    level={5}
    name={selectors.STORE_NAME}
    dataPart={selectors.FORM_RESOURCE_PART}
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
  (dispatch) => ({
    update(target) {
      dispatch(actions.updateResource('target', target))
    }
  })
)(ShortcutForm)

export {
  ShortcutCreation
}
