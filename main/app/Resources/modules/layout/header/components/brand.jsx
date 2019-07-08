import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config'
import {url} from '#/main/app/api'

// todo add alt to logo
// todo make colorized svg work

const SvgLogo = props =>
  <svg className="app-header-logo">
    <use xlinkHref={`${asset(props.url)}#logo-sm`} />
  </svg>

const StandardLogo = props =>
  <img
    className="app-header-logo"
    src={asset(props.url)}
  />

const HomeLink = (props) => props.redirectHome ?
  <a className="app-header-item app-header-brand hidden-xs" href={url(['claro_index'])}>
    {props.children}
  </a> :
  <div className="app-header-item app-header-brand hidden-xs">
    {props.children}
  </div>

HomeLink.propTypes = {
  redirectHome: T.bool.isRequired,
  children: T.any
}

const HeaderBrand = props =>
  <HomeLink redirectHome={props.redirectHome}>
    {props.logo && props.logo.colorized &&
      <SvgLogo url={props.logo.url} />
    }

    {props.logo && !props.logo.colorized &&
      <StandardLogo url={props.logo.url} />
    }

    {props.showTitle && props.title &&
      <h1 className="app-header-title hidden-sm">
        {props.title}

        {props.subtitle &&
          <small>{props.subtitle}</small>
        }
      </h1>
    }
  </HomeLink>


HeaderBrand.propTypes = {
  logo: T.shape({
    url: T.string.isRequired,
    colorized: T.bool
  }),
  title: T.string.isRequired,
  subtitle: T.string,
  showTitle: T.bool,
  redirectHome: T.bool.isRequired
}

export {
  HeaderBrand
}
