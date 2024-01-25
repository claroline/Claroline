import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config'
import {LinkButton} from '#/main/app/buttons/link'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'
import {UserAvatar} from '#/main/core/user/components/avatar'

const HeaderBrand = props =>
  <div className="m-4 mb-auto d-flex gap-4 align-items-center">
    <Button
      className="app-menu-toggle"
      type={CALLBACK_BUTTON}
      icon="fa fa-fw fa-bars"
      label={trans('menu')}
      tooltip="bottom"
      callback={props.toggleMenu}
    />

    {props.currentUser &&
      <span className="position-relative">
        <UserAvatar className="app-header-avatar" picture={props.currentUser.picture} alt={true} size="sm"/>
        <span
          className="app-header-status position-absolute top-100 start-100 translate-middle m-n1 bg-learning rounded-circle">
          <span className="visually-hidden">New alerts</span>
        </span>
      </span>
    }

    {false &&
      <LinkButton
        className={classes('app-header-brand')}
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
    }

    {props.children}
  </div>

HeaderBrand.propTypes = {
  logo: T.string,
  title: T.string.isRequired,
  subtitle: T.string,
  showTitle: T.bool,
  toggleMenu: T.func.isRequired
}

export {
  HeaderBrand
}
