import React from 'react'
import {PropTypes as T} from 'prop-types'

import {MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors} from '#/main/privacy/administration/privacy/store/selectors'
import {trans} from '#/main/app/intl/translation'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

const PrivacyTool = (props) => {
  console.log(props.parameters.dpo)
  return(
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

      <div className="row">
        <div className="col-md-4">
          {props.parameters.countryStorage !== null ?
            <AlertBlock type="success" title={trans('countryStorage_ok', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('change_storage_country', {}, 'privacy')}
                modal={[MODAL_COUNTRY_STORAGE]}
              />
            </AlertBlock>
            :
            <AlertBlock type="warning" title={trans('no_countryStorage', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('add_country_storage', {}, 'privacy')}
                modal={[MODAL_COUNTRY_STORAGE]}
              />
            </AlertBlock>
          }
        </div>
        <div className="col-md-4">
          {props.parameters.dpo.email !== '' ?
            <AlertBlock type="success" title={trans('dpo_ok', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('change_dpo', {}, 'privacy')}
                modal={[MODAL_INFOS_DPO]}
              />
            </AlertBlock>
            :
            <AlertBlock type="warning" title={trans('no_dpo', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('add_dpo', {}, 'privacy')}
                modal={[MODAL_INFOS_DPO]}
              />
            </AlertBlock>
          }
        </div>
        <div className="col-md-4">
          {props.parameters.tos.text.fr !== '' ?
            <AlertBlock type="success" title={trans('terms_ok', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('change_terms', {}, 'privacy')}
                modal={[MODAL_TERMS_OF_SERVICE]}
              />
            </AlertBlock>
            :
            <AlertBlock type="warning" title={trans('no_terms', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('add_terms', {}, 'privacy')}
                modal={[MODAL_TERMS_OF_SERVICE]}
              />
            </AlertBlock>
          }
        </div>
      </div>
    </ToolPage>
  )}
PrivacyTool.propTypes = {
  path: T.string.isRequired,
  parameters: T.object.isRequired,
  privacy: T.shape({
    countryStorage: T.string,
    dpo: T.shape({
      name: T.string,
      email: T.string,
      address: T.shape({
        street1: T.string,
        street2: T.string,
        postalCode: T.string,
        city: T.string,
        state: T.string,
        country: T.string
      }),
      phone: T.string
    })
  })
}

export {
  PrivacyTool
}
