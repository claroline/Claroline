import React from 'react'
import {PropTypes as T} from 'prop-types'

const HeaderTitle = props =>
  <h1 className="app-header-item app-header-title">
    {props.title}

    {props.subtitle &&
      <small>{props.subtitle}</small>
    }
  </h1>

HeaderTitle.propTypes = {
  title: T.string.isRequired,
  subtitle: T.string
}

export {
  HeaderTitle
}
