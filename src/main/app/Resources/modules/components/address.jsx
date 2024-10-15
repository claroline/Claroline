import React from 'react'
import {PropTypes as T} from 'prop-types'

import {constants as intlConstants} from '#/main/app/intl/constants'
import {CountryFlag} from '#/main/app/components/country-flag'

const Address = (props) =>
  <>
    <p className="mb-0">
      {props.street1}
      {props.street2}
    </p>
    <p className="mb-0">
      {props.postalCode} {props.city}
    </p>
    {props.state &&
      <p className="mb-0">
        {props.state}
      </p>
    }
    {props.country &&
      <p className="mb-0">
        <CountryFlag className="me-2" countryCode={props.country} />
        {intlConstants.REGIONS[props.country.toUpperCase()]}
      </p>
    }
  </>

Address.propTypes = {
  street1: T.string,
  street2: T.string,
  postalCode: T.string,
  city: T.string,
  state: T.string,
  country: T.string
}

export {
  Address
}