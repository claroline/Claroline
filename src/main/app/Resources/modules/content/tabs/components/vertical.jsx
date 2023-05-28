import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ContentNav} from '#/main/app/content/components/nav'

/**
 *
 * @deprecated use ContentNav instead
 */
const Vertical = (props) =>
  <ContentNav
    className={props.className}
    path={props.basePath}
    sections={props.tabs}
    type={props.vertical ? 'vertical' : 'horizontal'}
  />

Vertical.propTypes= {
  className: T.string,
  basePath: T.string,
  tabs: T.arrayOf(T.shape({
    id: T.string,
    path: T.string.isRequired,
    exact: T.bool,
    icon: T.string,
    title: T.node.isRequired,
    displayed: T.bool,
    actions: T.arrayOf(T.shape({
      // TODO : action types
    }))
  })).isRequired,
  vertical: T.bool
}

Vertical.defaultProps = {
  basePath: '',
  vertical: true
}

export {
  Vertical
}
