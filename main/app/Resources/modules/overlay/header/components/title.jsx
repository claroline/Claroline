import React from 'react'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'

const HeaderTitle = props => {
  return (
    props.redirectHome ?
      <a href={url(['claro_index'])} className="app-header-title-link">
        <h1 className="app-header-item app-header-title">
          {props.title}

          {props.subtitle &&
            <small>{props.subtitle}</small>
          }
        </h1>
      </a>
      :
      <h1 className="app-header-item app-header-title">
        {props.title}

        {props.subtitle &&
        <small>{props.subtitle}</small>
        }
      </h1>
  )
}


HeaderTitle.propTypes = {
  title: T.string.isRequired,
  subtitle: T.string,
  redirectHome: T.bool.isRequired
}

export {
  HeaderTitle
}
