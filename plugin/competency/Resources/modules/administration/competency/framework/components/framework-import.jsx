import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as formActions} from '#/main/app/content/form/store'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const FrameworkImportComponent = (props) =>
  <FormData
    level={3}
    name="frameworks.import"
    buttons={true}
    target={() => ['apiv2_competency_framework_import']}
    cancel={{
      type: LINK_BUTTON,
      target: '/frameworks',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'file',
            type: 'file',
            label: trans('file'),
            required: true,
            onChange: (file) => {props.updateProp('frameworks.import', 'file', file)},
            options: {
              uploadUrl: ['apiv2_competency_framework_file_upload']
            }
          }
        ]
      }
    ]}
  />

FrameworkImportComponent.propTypes = {
  updateProp: T.func.isRequired
}

const FrameworkImport = connect(
  null,
  (dispatch) => ({
    updateProp(storeName, prop, value) {
      dispatch(formActions.updateProp(storeName, prop, value))
    }
  })
)(FrameworkImportComponent)

export {
  FrameworkImport
}
