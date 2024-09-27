import React from 'react'
import {useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {PageFull} from '#/main/app/page'

import {selectors} from '#/main/app/context/store'

const ContextPage = (props) => {
  const contextType = useSelector(selectors.type)
  const contextData = useSelector(selectors.data)
  const contextPath = useSelector(selectors.path)

  return (
    <PageFull
      className={classes('context-page', `${contextType}-page`, props.className)}
      breadcrumb={(!props.root ? [
        {
          label: get(contextData, 'name') || trans(contextType, {}, 'context'),
          target: contextPath
        }
      ] : []).concat(props.breadcrumb || [])}
      title={props.title ?
        props.title + ' | ' + get(contextData, 'name', trans(contextType, {}, 'context')) :
        get(contextData, 'name', trans(contextType, {}, 'context'))
      }
      description={props.description || get(contextData, 'meta.description')}

      {...omit(props, 'className', 'breadcrumb', 'root', 'title', 'description')}
    >
      {props.children}
    </PageFull>
  )
}

ContextPage.propTypes = PageFull.propTypes
ContextPage.defaultProps = PageFull.defaultProps

export {
  ContextPage
}
