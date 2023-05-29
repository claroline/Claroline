import React from 'react'
import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {Button} from '#/main/app/action/components/button'

const PrivacyItem = (props) =>
  <div className="privacy-item">
    <div className="panel panel-default" style={{marginTop: '25px'}}>
      <div className="panel-heading">
        {trans('country_storage', {}, 'privacy')}
      </div>
      <div className="panel-body">
        {props.item.countryStorage}
      </div>
    </div>
    <div className="panel panel-default">
      <div className="panel-heading">
        {trans('dpo_info', {}, 'privacy')}
      </div>
      <div className="panel-body">
        <strong>{props.item.dpo.name}</strong>
        <br/>
        <strong>Email :</strong> {props.item.dpo.email}
        <br/>
        <strong>Téléphone :</strong> {props.item.dpo.phone}
        <br/>
        <strong>Adresse :</strong>
        <br/>
        {props.item.dpo.address.street1}<br/>
        {props.item.dpo.address.street2}<br/>
        {props.item.dpo.address.postalCode}<br/>
        {props.item.dpo.address.city}<br/>
        {props.item.dpo.address.state}<br/>
        {props.item.dpo.address.country}<br/>
      </div>
    </div>
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

export {PrivacyItem}
