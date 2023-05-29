import React from 'react'
import {trans} from '#/main/app/intl'
import {MODAL_COUNTRY_STORAGE} from '#/main/privacy/administration/privacy/modals/country'
import {MODAL_INFOS_DPO} from '#/main/privacy/administration/privacy/modals/dpo'
import {MODAL_TERMS_OF_SERVICE} from '#/main/privacy/administration/privacy/modals/terms'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
const PrivacyItem = (props) =>
  <div className="row panel panel-default" style={{paddingTop: '25px'}}>
    <div className="col-md-6">
      <div className="panel panel-default">
        <div className="panel-heading">
          <h3>{trans('dpo_info', {}, 'privacy')}</h3>
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
    </div>
    <div className="col-md-6">
      <div className="panel panel-default">
        <div className="panel-heading">
          <h3>{trans('country_storage', {}, 'privacy')}</h3>
        </div>
        <div className="panel-body">
          {props.item.countryStorage}
        </div>
      </div>
    </div>
  </div>

export {PrivacyItem}
