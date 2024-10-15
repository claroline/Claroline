import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Email} from '#/main/app/components/email'
import {Phone} from '#/main/app/components/phone'
import {Address} from '#/main/app/components/address'

const Contact = (props) =>
  <address className={classes(props.className, 'd-flex flex-column gap-2')}>
    {props.email &&
      <div className="d-flex flex-row align-items-baseline" role="presentation">
        <span className="fa fa-fw fa-envelope me-2" aria-hidden={true} />
        <Email email={props.email} className="text-reset" />
      </div>
    }
    {props.phone &&
      <div className="d-flex flex-row align-items-baseline" role="presentation">
        <span className="fa fa-fw fa-phone me-2" aria-hidden={true} />
        <Phone phone={props.phone} className="text-reset" />
      </div>
    }

    {props.address &&
      <div className="d-flex flex-row align-items-baseline" role="presentation">
        <span className="fa fa-fw fa-map-marker-alt me-2" aria-hidden={true} />
        <div className="d-inline-block" role="presentation">
          <Address {...props.address} />
        </div>
      </div>
    }
  </address>

Contact.propTypes = {
  className: T.string,
  email: T.string,
  phone: T.string,
  address: T.shape({
    street1: T.string,
    street2: T.string,
    postalCode: T.string,
    city: T.string,
    state: T.string,
    country: T.string
  })
}

export {
  Contact
}
