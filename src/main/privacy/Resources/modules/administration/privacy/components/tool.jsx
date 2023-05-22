import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors} from '#/main/privacy/administration/privacy/store'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_THERM_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/therms'

const PrivacyTool = (props) =>
  <ToolPage>
    <DetailsData
      name={selectors.FORM_NAME}
      data={props.parameters}
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
    <hr/>
    <Button
      className="btn btn-primary"
      type={MODAL_BUTTON}
      label={trans('Modifier le pays de stockage', {}, 'privacy')}
      modal={[MODAL_COUNTRY_STORAGE, {
        parameters: props.parameters
      }]}
      primary={true}
    />
    <Button
      className="btn btn-primary"
      type={MODAL_BUTTON}
      label={trans('Modifier le DPO', {}, 'privacy')}
      modal={[MODAL_INFOS_DPO, {
        parameters: props.parameters
      }]}
      primary={true}
    />
    <Button
      className="btn btn-primary"
      type={MODAL_BUTTON}
      label={trans('Modifier les conditions générales', {}, 'privacy')}
      modal={[MODAL_THERM_OF_SERVICE, {
        parameters: props.parameters
      }]}
      primary={true}
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
