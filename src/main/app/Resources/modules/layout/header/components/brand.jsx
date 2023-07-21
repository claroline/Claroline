import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config'
import {LinkButton} from '#/main/app/buttons/link'

const HeaderBrand = props =>
  <LinkButton
    className={classes('app-header-item app-header-brand text-decoration-none', !props.logo && 'd-none d-sm-block')}
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
      <h1 className="app-header-title d-none d-md-block">
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
