import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'
import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {Button} from '#/main/app/action'


const ConfigurationFormComponent = props =>
  <FormContainer
    level={3}
    name="bookReferenceConfiguration"
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
    <div className="book-referenc-configuration-buttons">
      <Button
        className="btn"
        type="callback"
        callback={() => props.saveForm(props.id)}
        disabled={!props.saveEnabled}
        label={trans('save')}
        icon={'fa fa-save'}
        primary={true}
      />
    </div>
  </FormContainer>

ConfigurationFormComponent.propTypes = {
  id: T.oneOfType([T.number, T.string]).isRequired,
  saveForm: T.func.isRequired,
  saveEnabled: T.bool.isRequired
}

const ConfigurationForm = connect(
  state => ({
    id: formSelect.data(formSelect.form(state, 'bookReferenceConfiguration')).id,
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'bookReferenceConfiguration'))
  }),
  dispatch => ({
    saveForm(id) {
      dispatch(
        formActions.saveForm('bookReferenceConfiguration', ['apiv2_book_reference_configuration_update', {id}])
      )
    }
  })
)(ConfigurationFormComponent)

export {
  ConfigurationForm
}