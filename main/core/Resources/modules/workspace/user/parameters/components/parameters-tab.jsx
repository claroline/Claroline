import React from 'react'
import {connect} from 'react-redux'
import {trans} from '#/main/core/translation'
import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'

const ParametersTabActions = () =>
  <PageActions>
    <FormPageActionsContainer
      formName="parameters"
      opened={true}
      target={(workspace) => ['apiv2_workspace_update', {id: workspace.id}]}
    />
  </PageActions>

const Parameters = () =>
  <FormContainer
    level={3}
    name="parameters"
    sections={[{
      id: 'general',
      title: trans('general'),
      primary: true,
      fields: [
        {
          name: 'registration.validation',
          type: 'boolean',
          label: trans('registration_validation')
        }, {
          name: 'registration.selfRegistration',
          type: 'boolean',
          label: trans('public_registration')
        }, {
          name: 'registration.selfUnregistration',
          type: 'boolean',
          label: trans('public_unregistration')
        }, {
          name: 'display.displayable',
          type: 'boolean',
          label: trans('displayable_in_workspace_list')
        },
        {
          name: 'restrictions.maxUsers',
          type: 'number',
          label: trans('workspace_max_users')
        }
      ]
    }]}
  />

Parameters.propTypes = {
}

const ParametersTab = connect(
  (state) => ({
    workspace: formSelect.data(formSelect.form(state, 'workspace'))
  })
)(Parameters)

export {
  ParametersTabActions,
  ParametersTab
}
