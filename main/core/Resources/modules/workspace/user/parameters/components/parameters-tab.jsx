import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {generateUrl} from '#/main/core/api/router'
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

//todo: maybe rename form name: parameters is missleading
const Parameters = props =>
  <div>
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
    <div className="panel panel-body">
      <h4 className="panel-title">{trans('generate_url')}</h4> <br />
      <div className="alert alert-info">
        {generateUrl('claro_workspace_subscription_url_generate', {slug: props.workspace.meta.slug}, true)}
      </div>
    </div>
  </div>

Parameters.propTypes = {
  workspace: T.shape({
    meta: T.shape({
      slug: T.bool.isRequired
    }).isRequired
  }).isRequired
}

const ParametersTab = connect(
  (state) => ({
    workspace: formSelect.data(formSelect.form(state, 'parameters'))
  })
)(Parameters)

export {
  ParametersTabActions,
  ParametersTab
}
