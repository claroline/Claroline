import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config'
import {LinkButton} from '#/main/app/buttons/link'

const HeaderBrand = props =>
  <LinkButton
    className="app-header-item app-header-brand hidden-xs"
    target="/"
  >
    {props.logo &&
      <img
        className="app-header-logo"
        src={asset(props.logo)}
        alt={trans('logo')}
      />
    }

    {props.showTitle && props.title &&
      <h1 className="app-header-title hidden-sm">
        {props.title}

        {props.subtitle &&
          <small>{props.subtitle}</small>
        }
      </h1>
    }
  </LinkButton>


HeaderBrand.propTypes = {
  logo: T.string,
  title: T.string.isRequired,
  subtitle: T.string,
  showTitle: T.bool
}

export {
  HeaderBrand
}
