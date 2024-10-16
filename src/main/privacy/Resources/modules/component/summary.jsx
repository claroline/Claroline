import React from 'react'
import {PropTypes as T} from 'prop-types'

import {constants as intlConstants} from '#/main/app/intl/constants'
import {trans} from '#/main/app/intl/translation'
import {CountryFlag} from '#/main/app/components/country-flag'
import {Contact} from '#/main/app/components/contact'

import {PrivacyCard} from '#/main/privacy/administration/privacy/components/card'


const PrivacySummary = props =>
  <div className="row row-cols-1 row-cols-md-2 g-4 mt-0">
    <div className="col">
      <PrivacyCard
        title={trans('dpo', {}, 'privacy')}
        content={props.parameters.dpo &&
          <>
            <div className="d-flex flex-row align-items-baseline mb-2" role="presentation">
              <span className="fa fa-fw fa-user me-2" aria-hidden={true} />
              <span role="presentation">{props.parameters.dpo.name}</span>
            </div>
            <Contact {...props.parameters.dpo} />
          </>
        }
      />
    </div>
    <div className="col">
      <PrivacyCard
        title={trans('country_storage', {}, 'privacy')}
        content={
          <div className="card-text d-flex flex-column align-items-center justify-content-center">
            <CountryFlag
              countryCode={props.parameters.countryStorage}
              className="fs-1"
            />

            <span className="fs-4">
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
    countryStorage: T.string
  })
}

PrivacySummary.defaultProps = {
  parameters: {
    dpo: {},
    tos: {}
  }
}

export {
  PrivacySummary
}
