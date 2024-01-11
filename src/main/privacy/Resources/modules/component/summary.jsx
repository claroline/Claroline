import React from 'react'
import {PropTypes as T} from 'prop-types'

import {constants as intlConstants} from '#/main/app/intl/constants'
import {trans} from '#/main/app/intl/translation'
import {PrivacyCard} from '#/main/privacy/administration/privacy/components/card'

const PrivacySummary = props =>
  <div className="row row-cols-1 row-cols-md-2 g-4 mt-0">
    <div className="col">
      <PrivacyCard
        title={trans('dpo', {}, 'privacy')}
        content={
          <fieldset>
            <ul className="list-unstyled mx-2 mb-0">
              {props.parameters.dpo.name && (
                <li className="form-group mb-2">
                  <div className="form-label">
                    <span className="fa fa-fw fa-user icon-with-text-right"/>
                    <span>{props.parameters.dpo.name}</span>
                  </div>
                </li>
              )}
              {props.parameters.dpo.email && (
                <li className="form-group mb-2">
                  <div className="form-label">
                    <span className="fa fa-fw fa-envelope icon-with-text-right"/>
                    <span>{props.parameters.dpo.email}</span>
                  </div>
                </li>
              )}
              {props.parameters.dpo.phone && (
                <li className="form-group mb-2">
                  <div className="form-label">
                    <span className="fa fa-fw fa-phone icon-with-text-right"/>
                    <span>{props.parameters.dpo.phone}</span>
                  </div>
                </li>
              )}
              {props.parameters.dpo.address && (props.parameters.dpo.address.street1 || props.parameters.dpo.address.street2 || props.parameters.dpo.address.postalCode || props.parameters.dpo.address.city || props.parameters.dpo.address.state || props.parameters.dpo.address.country) && (
                <li className="form-group mb-2 d-flex">
                  <div className="form-label">
                    <span className="fa fa-fw fa-map-marker-alt icon-with-text-right"/>
                  </div>
                  <div className="form-label">
                    <p className="mb-0">
                      {props.parameters.dpo.address.street1} {props.parameters.dpo.address.street2}
                    </p>
                    <p className="mb-0">
                      {props.parameters.dpo.address.postalCode} {props.parameters.dpo.address.city} {props.parameters.dpo.address.state} <span
                        className={`fi fi-${props.parameters.dpo.address.country ? props.parameters.dpo.address.country.toLowerCase() : 'xx'}`}
                        title={props.parameters.dpo.address.country ? intlConstants.REGIONS[props.parameters.dpo.address.country.toUpperCase()] : trans('empty_value') }
                      />
                    </p>
                  </div>
                </li>
              )}
            </ul>
          </fieldset>
        }
      />
    </div>
    <div className="col">
      <PrivacyCard
        title={trans('country_storage', {}, 'privacy')}
        content={
          <div className="d-flex flex-column align-items-center justify-content-center">
            <div style={{ fontSize: '6rem' }}>
              <span className={`fi fi-${props.parameters.countryStorage ? props.parameters.countryStorage.toLowerCase() : 'xx'}`}/>
            </div>
            <span style={{ fontSize: '1.4rem', fontWeight: 'bold', paddingBottom: '8px' }}>
              {props.parameters.countryStorage ? intlConstants.REGIONS[props.parameters.countryStorage.toUpperCase()] : trans('empty_value') }
            </span>
          </div>
        }
      />
    </div>
  </div>

PrivacySummary.propTypes = {
  icon: T.string,
  title: T.string,
  content: T.oneOfType([T.string, T.element]),
  parameters: T.shape({
    tos: T.shape({
      enabled: T.bool
    }),
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
    }),
    countryStorage: T.bool
  })
}

export {
  PrivacySummary
}
