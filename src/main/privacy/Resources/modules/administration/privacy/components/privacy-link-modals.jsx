import React from 'react'
import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {Button} from '#/main/app/action/components/button'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

const PrivacyLinkModals = (props) =>
  <div className="row">
    <div className="col-lg-4">
      {props.item.countryStorage ?
        <AlertBlock type="success" title={trans('countryStorage_ok', {}, 'privacy')}>
          <Button
            className="btn btn-default"
            type={MODAL_BUTTON}
            label={trans('change_country_storage', {}, 'privacy')}
            modal={[MODAL_COUNTRY_STORAGE, {
              countryStorage: props.item.countryStorage
            }]}
          />
        </AlertBlock>
        :
        <AlertBlock type="warning" title={trans('no_countryStorage', {}, 'privacy')}>
          <Button
            className="btn btn-default"
            type={MODAL_BUTTON}
            label={trans('add_country_storage', {}, 'privacy')}
            modal={[MODAL_COUNTRY_STORAGE, {
              countryStorage: props.item.countryStorage
            }]}
          />
        </AlertBlock>
      }
      <span className="help-block">
        {trans('country_storage_help', {}, 'privacy')}
      </span>
    </div>
    <div className="col-lg-4">
      {props.item.dpo.email  ?
        <AlertBlock type="success" title={trans('dpo_ok', {}, 'privacy')}>
          <Button
            className="btn btn-default"
            type={MODAL_BUTTON}
            label={trans('change_dpo', {}, 'privacy')}
            modal={[MODAL_INFOS_DPO, {
              dpo: props.item.dpo
            }]}
          />
        </AlertBlock>
        :
        <AlertBlock type="warning" title={trans('no_dpo', {}, 'privacy')}>
          <Button
            className="btn btn-default"
            type={MODAL_BUTTON}
            label={trans('add_dpo', {}, 'privacy')}
            modal={[MODAL_INFOS_DPO, {
              dpo: props.item.dpo
            }]}
          />
        </AlertBlock>
      }
      <span className="help-block">
        {trans('dpo_help', {}, 'privacy')}
      </span>
    </div>
    <div className="col-lg-4">
      {props.item.termsOfService ?
        <AlertBlock type="success" title={trans('terms_ok', {}, 'privacy')}>
          <Button
            className="btn btn-default"
            type={MODAL_BUTTON}
            label={trans('change_terms', {}, 'privacy')}
            modal={[MODAL_TERMS_OF_SERVICE, {
              termsOfService: props.item.termsOfService,
              isTermsOfService : props.item.isTermsOfServiceEnabled
            }]}
          />
          {!props.item.isTermsOfServiceEnabled ?
            <p style={{color: 'red'}}>{trans('terms_not_enabled',{}, 'privacy')}</p>
            :
            <p>{trans('terms_enabled',{}, 'privacy')}</p>}
        </AlertBlock>
        :
        <AlertBlock type="warning" title={trans('no_terms', {}, 'privacy')}>
          <Button
            className="btn btn-default"
            type={MODAL_BUTTON}
            label={trans('add_terms', {}, 'privacy')}
            modal={[MODAL_TERMS_OF_SERVICE, {
              terms: props.item.termsOfService
            }]}
          />
        </AlertBlock>
      }
      <span className="help-block">
        {trans('terms_help', {}, 'privacy')}
      </span>
    </div>
  </div>

export {PrivacyLinkModals}
