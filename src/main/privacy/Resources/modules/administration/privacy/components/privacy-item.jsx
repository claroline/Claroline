import React from 'react'
import {trans} from '#/main/app/intl'

const PrivacyItem = (props) => {
  console.log('flûte et sacrebleu ! ', props.item.data.dpo.address)

  return(
    <div className="privacy-item">
      <div className="panel panel-default" style={{marginTop: '25px'}}>
        <div className="panel-heading">
          {trans('country_storage', {}, 'privacy')}
        </div>
        <div className="panel-body">
          {props.item.data.countryStorage}
        </div>
      </div>
      <div className="panel panel-default">
        <div className="panel-heading">
          {trans('dpo_info', {}, 'privacy')}
        </div>
        <div className="panel-body">
          <strong>{props.item.data.dpo.name}</strong>
          <br/>
          <strong>Email :</strong> {props.item.data.dpo.email}
          <br/>
          <strong>Téléphone :</strong> {props.item.data.dpo.phone}
          <br/>
          <strong>Adresse :</strong>
          <br/>
          {props.item.data.dpo.address.street1}<br/>
          {props.item.data.dpo.address.street2}<br/>
          {props.item.data.dpo.address.postalCode}<br/>
          {props.item.data.dpo.address.city}<br/>
          {props.item.data.dpo.address.state}<br/>
          {props.item.data.dpo.address.country}
        </div>
      </div>
    </div>
  )
}


export {PrivacyItem}
