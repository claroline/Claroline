import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {PageMenu} from '#/main/app/page/components/menu'

/**
 * Header of the current page.
 *
 * Contains title, icon, actions and an optional poster image.
 */
const PageHeader = props =>
  <>
    <PageMenu
      embedded={props.embedded}
      {...props.menu}
      breadcrumb={props.breadcrumb}
    />

    {props.poster &&
      <div className="page-poster ratio ratio-poster" role="presentation" style={{
        backgroundImage: `url("${asset(props.poster)}")`
      }}/>
    }

    {props.icon &&
      <div className="page-icon" role="presentation">
        {props.icon}
      </div>
    }
  </>

PageHeader.propTypes = {
  id: T.string,
  title: T.node.isRequired,
  icon: T.oneOfType([T.string, T.node]),
  embedded: T.bool,
  poster: T.string,
  disabled: T.bool,

  /**
   * The path of the page inside the application (used to build the breadcrumb).
   */
  breadcrumb: T.arrayOf(T.shape({
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  }))
}

PageHeader.defaultProps = {
  actions: []
}

export {
  PageHeader
}
