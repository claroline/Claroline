import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config'
import {LinkButton} from '#/main/app/buttons/link'

const MenuBrand = props =>
  <LinkButton
    className="app-menu-brand justify-content-center"
    target="/"
    onClick={props.closeMenu}
  >
    {props.logo &&
      <img
        className="app-brand-logo"
        src={asset(props.logo)}
        alt={trans('logo')}
      />
    }

    {props.showTitle && props.title &&
      <h1 className="app-brand-title d-none d-md-block">
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
