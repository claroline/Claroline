import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors} from '#/main/privacy/administration/privacy/store'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'

const PrivacyTool = (props) =>
  <ToolPage>
    <FormData
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
              name: 'countryStorage',
              label: trans('country_storage', {}, 'privacy'),
              type: 'country'
            }
          ]
        }, {
          icon: 'fa fa-fw fa-user-shield',
          title: trans('dpo'),
          fields: [
            {
              name: 'dpo.name',
              label: trans('name'),
              type: 'string'
            }, {
              name: 'dpo.email',
              label: trans('email'),
              type: 'email'
            }, {
              name: 'dpo.phone',
              label: trans('phone'),
              type: 'string'
            }, {
              name: 'dpo.address',
              label: trans('address'),
              type: 'address'
            }
          ]
        }, {
          icon: 'fa fa-fw fa-file-shield',
          title: trans('terms_of_service', {}, 'privacy'),
          fields: [
            {
              name: 'tos.enabled',
              type: 'boolean',
              label: trans('terms_of_service_activation_message', {}, 'privacy'),
              help: trans('terms_of_service_activation_help', {}, 'privacy'),
              linked: [
                {
                  name: 'tos.template',
                  label: trans('terms_of_service', {}, 'template'),
                  type: 'template',
                  displayed: get(props, 'tos.enabled'),
                  options: {
                    picker: {
                      filters: [{
                        property: 'typeName',
                        value: 'terms_of_service',
                        locked: true
                      }]
                    }
                  }
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
  parameters: T.shape({
    tos: T.shape({
      enabled: T.bool
    })
  })
}

export {
  PrivacyTool
}
