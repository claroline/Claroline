import React from 'react'
import {trans} from '#/main/app/intl'

const PrivacyItem = (props) =>
  <>
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
        <strong>Nom : </strong>
        {props.item.dpo.name}
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
        {props.item.dpo.address.country}
      </div>
    </div>
  </>

export {PrivacyItem}
