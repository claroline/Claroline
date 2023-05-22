import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors} from '#/main/privacy/administration/privacy/store'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'

const PrivacyTool = (props) =>
  <ToolPage>
    <DetailsData
      level={2}
      name={selectors.FORM_NAME}
      target={['apiv2_privacy_update']}
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: props.path,
        exact: true
      }}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'privacy.countryStorage',
              label: trans('country_storage', {}, 'privacy'),
              type: 'country'
            }
          ]
        }, {
          icon: 'fa fa-fw fa-user-shield',
          title: trans('dpo'),
          fields: [
            {
              name: 'privacy.dpo.name',
              label: trans('name'),
              type: 'string'
            }, {
              name: 'privacy.dpo.email',
              label: trans('email'),
              type: 'email'
            }, {
              name: 'privacy.dpo.phone',
              label: trans('phone'),
              type: 'string'
            }, {
              name: 'privacy.dpo.address',
              label: trans('address'),
              type: 'address'
            }
          ]
        }, {
          icon: 'fa fa-fw fa-copyright',
          title: trans('terms_of_service', {}, 'privacy'),
          fields: [
            {
              name: 'tos.enabled',
              type: 'boolean',
              label: trans('terms_of_service_activation_message', {}, 'privacy'),
              help: trans('terms_of_service_activation_help', {}, 'privacy'),
              linked: [
                {
                  name: 'tos.text',
                  type: 'translated',
                  label: trans('terms_of_service', {}, 'privacy'),
                  required: true,
                  displayed: get(props.parameters, 'tos.enabled')
                }
              ]
            }
          ]
        }
      ]}
    />
  </ToolPage>

PrivacyTool.propTypes = {
  path: T.string.isRequired,
  lockedParameters: T.arrayOf(T.string),
  parameters: T.shape({
    tos: T.shape({
      enabled: T.bool
    })
  })
}

export {
  PrivacyTool
}
