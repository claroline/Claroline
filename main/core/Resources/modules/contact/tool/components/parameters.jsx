import React from 'react'

import {trans} from '#/main/core/translation'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

const ParametersActions = () =>
  <PageActions>
    <FormPageActionsContainer
      formName="options"
      opened={true}
      target={(parameters) => ['apiv2_contact_options_update', {id: parameters.id}]}
    />
  </PageActions>

const Parameters = () =>
  <FormContainer
    level={3}
    name="options"
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'data.show_username',
            type: 'boolean',
            label: trans('show_username')
          }, {
            name: 'data.show_mail',
            type: 'boolean',
            label: trans('show_mail')
          }, {
            name: 'data.show_phone',
            type: 'boolean',
            label: trans('show_phone')
          }
        ]
      }
    ]}
  />

export {
  ParametersActions,
  Parameters
}
