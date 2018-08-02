import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

const ConfigurationFormComponent = props =>
  <FormData
    level={3}
    name="bookReferenceConfiguration"
    buttons={true}
    target={['apiv2_book_reference_configuration_update', {id: props.id}]}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'apiKey',
            type: 'string',
            label: trans('api_key', {}, 'icap_bibliography'),
            required: true
          }
        ]
      }
    ]}
  >
  </FormData>

ConfigurationFormComponent.propTypes = {
  id: T.oneOfType([T.number, T.string]).isRequired,
  saveForm: T.func.isRequired,
  saveEnabled: T.bool.isRequired
}

const ConfigurationForm = connect(
  state => ({
    id: formSelect.data(formSelect.form(state, 'bookReferenceConfiguration')).id
  })
)(ConfigurationFormComponent)

export {
  ConfigurationForm
}