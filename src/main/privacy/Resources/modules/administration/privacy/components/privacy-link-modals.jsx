import React from 'react'
import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button} from '#/main/app/action/components/button'

const PrivacyLinkModals = (props) =>
  <div className="row">
    <div className="col-md-4">
      {props.parameters.countryStorage ?
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
      {props.parameters.dpo.email ?
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
      {props.parameters.termsOfService ?
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

export {PrivacyLinkModals}