import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {PageFull} from '#/main/app/page'

import {getToolBreadcrumb} from '#/main/core/tool/utils'

const ContextPage = (props) =>
  <PageFull
    className={classes('context-page', `${props.name}-page`, props.className)}
    showBreadcrumb={true}
    path={[].concat(getToolBreadcrumb(null, props.name, props.contextData), props.breadcrumb)}
    poster={props.poster || get(props.contextData, 'poster')}

    {...omit(props, 'name', 'className', 'contextData', 'breadcrumb', 'poster')}
  >
    {props.children}
  </PageFull>

ContextPage.propTypes = {
  className: T.string,
  name: T.string.isRequired,
  contextData: T.shape({

  }),
  breadcrumb: T.arrayOf(T.shape({

  }))
}

ContextPage.defaultProps = {
  breadcrumb: []
}

export {
  ContextPage
}
