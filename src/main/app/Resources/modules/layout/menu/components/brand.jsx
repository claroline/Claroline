import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config'
import {LinkButton} from '#/main/app/buttons/link'

const MenuBrand = props =>
  <LinkButton
    className={classes('app-header-brand my-4 mx-3 justify-content-center')}
    target="/"
    onClick={props.closeMenu}
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

MenuBrand.propTypes = {
  logo: T.string,
  title: T.string.isRequired,
  subtitle: T.string,
  showTitle: T.bool,
  closeMenu: T.func.isRequired
}

export {
  MenuBrand
}
