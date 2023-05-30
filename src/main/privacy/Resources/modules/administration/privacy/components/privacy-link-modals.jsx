import React from 'react'
import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {Button} from '#/main/app/action/components/button'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

const PrivacyLinkModals = (props) => {
  console.log('PrivacyLinkModals', props.item)

  return(
    <div className="privacy">
      <div className="row">
        <div className="col-md-4">
          {props.item.countryStorage ?
            <AlertBlock type="success" title={trans('countryStorage_ok', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('change_storage_country', {}, 'privacy')}
                modal={[MODAL_COUNTRY_STORAGE, {
                  item: props.item
                }]}
              />
            </AlertBlock>
            :
            <AlertBlock type="warning" title={trans('no_countryStorage', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('add_country_storage', {}, 'privacy')}
                modal={[MODAL_COUNTRY_STORAGE, {
                  item: props.item
                }]}
              />
            </AlertBlock>
          }
        </div>
        <div className="col-md-4">
          {props.item.dpo.email ?
            <AlertBlock type="success" title={trans('dpo_ok', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('change_dpo', {}, 'privacy')}
                modal={[MODAL_INFOS_DPO, {
                  item: props.item
                }]}
              />
            </AlertBlock>
            :
            <AlertBlock type="warning" title={trans('no_dpo', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('add_dpo', {}, 'privacy')}
                modal={[MODAL_INFOS_DPO, {
                  item: props.item
                }]}
              />
            </AlertBlock>
          }
        </div>
        <div className="col-md-4">
          {props.item.termsOfService ?
            <AlertBlock type="success" title={trans('terms_ok', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('change_terms', {}, 'privacy')}
                modal={[MODAL_TERMS_OF_SERVICE, {
                  item: props.item
                }]}
              />
            </AlertBlock>
            :
            <AlertBlock type="warning" title={trans('no_terms', {}, 'privacy')}>
              <Button
                className="btn btn-default btn-block"
                type={MODAL_BUTTON}
                label={trans('add_terms', {}, 'privacy')}
                modal={[MODAL_TERMS_OF_SERVICE, {
                  item: props.item
                }]}
              />
            </AlertBlock>
          }
        </div>
      </div>
    </div>
  )
}

export {PrivacyLinkModals}