import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config'
import {LinkButton} from '#/main/app/buttons/link'

const AppBrand = props =>
  <LinkButton
    className="app-brand justify-content-center"
    target="/"
  >
    {props.logo &&
      <img
        className="app-brand-logo"
        src={asset(props.logo)}
        alt={trans('logo')}
      />
    }

    {props.title &&
      <h1 className="app-brand-title d-none d-md-block">
        {props.title}

        {props.subtitle &&
          <small>{props.subtitle}</small>
        }
      </h1>
    }
  </LinkButton>

AppBrand.propTypes = {
  logo: T.string,
  title: T.string.isRequired,
  subtitle: T.string
}

export {
  AppBrand
}
