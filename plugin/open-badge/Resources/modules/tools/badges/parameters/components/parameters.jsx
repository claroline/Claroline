import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

const ParametersForm = props =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.STORE_NAME + '.parameters'}
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: URL_BUTTON,
      target: props.path
    }}
    sections={[
      {
        title: trans('general'),
        fields: [
          {
            name: 'props.badges.enable_default',
            type: 'boolean',
            label: trans('required_validation', {}, 'badge')
          }
        ]
      }
    ]}
  />

export {
  ParametersForm
}
