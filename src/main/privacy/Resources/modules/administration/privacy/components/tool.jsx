import React from 'react'
import {PropTypes as T} from 'prop-types'

import {MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors} from '#/main/privacy/administration/privacy/store'
import {trans} from '#/main/app/intl/translation'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {MODAL_TERMS_OF_SERVICE_CONSUME} from '#/main/privacy/account/privacy/modals/terms'

const PrivacyTool = (props) =>
  <ToolPage>
    <DetailsData
      name={selectors.FORM_NAME}
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
          title: trans('dpo'),
          primary: true,
          fields: [
            {
              name: 'privacy.dpo.name',
              label: trans('dpo'),
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
        }
      ]}
    />

    <Button
      className="btn"
      type={MODAL_BUTTON}
      label={trans('show-terms-of-service', {}, 'privacy')}
      modal={[MODAL_TERMS_OF_SERVICE_CONSUME, {
        parameters: props.parameters
      }]}
    />
    <hr/>
    <div className="row">
      <div className="col-md-4">
        <Button
          className="btn btn-default btn-block"
          type={MODAL_BUTTON}
          label={trans('change_storage_country', {}, 'privacy')}
          modal={[MODAL_COUNTRY_STORAGE, {
            parameters: props.parameters
          }]}
        />
      </div>
      <div className="col-md-4">
        <Button
          className="btn btn-default btn-block"
          type={MODAL_BUTTON}
          label={trans('change_dpo', {}, 'privacy')}
          modal={[MODAL_INFOS_DPO, {
            parameters: props.parameters
          }]}
        />
      </div>
      <div className="col-md-4">
        <Button
          className="btn btn-default btn-block"
          type={MODAL_BUTTON}
          label={trans('change_terms', {}, 'privacy')}
          modal={[MODAL_TERMS_OF_SERVICE, {
            parameters: props.parameters
          }]}
        />
      </div>

    </div>
  </ToolPage>

PrivacyTool.propTypes = {
  path: T.string.isRequired
}

export {
  PrivacyTool
}
