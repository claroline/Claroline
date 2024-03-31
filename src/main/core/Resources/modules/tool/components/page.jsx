import React, {createElement, useContext} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {PageFull} from '#/main/app/page/components/full'

import {getTool, getToolBreadcrumb} from '#/main/core/tool/utils'
import {ToolIcon} from '#/main/core/tool/components/icon'
import {ToolMenu} from '#/main/core/tool/components/menu'
import {ToolContext} from '#/main/core/tool/context'

const ToolPage = props => {
  const toolDef = useContext(ToolContext)

  return (
    <PageFull
      className={classes('tool-page', `${props.name}-page`, props.className)}
      title={trans(props.name, {}, 'tools')}
      path={[].concat(getToolBreadcrumb(props.name, props.currentContext.type, props.currentContext.data), props.path)}
      poster={props.poster || get(props.toolData, 'poster') || get(props.currentContext, 'data.poster')}
      icon={get(props.toolData, 'display.showIcon') ?
        <ToolIcon type={get(props.toolData, 'icon')}/>
        :
        undefined
      }
      fullscreen={props.fullscreen}
      meta={{
        title: `${trans(props.name, {}, 'tools')} - ${'workspace' === props.currentContext.type ? props.currentContext.data.name : trans(props.currentContext.type)}`,
        description: get(props.currentContext.data, 'meta.description')
      }}

      menu={
        <ToolMenu
          path={props.basePath}
          currentContext={props.currentContext}
          toolData={props.toolData}
          menu={toolDef.menu}
          actions={toolDef.actions}
          reload={props.reload}
        />
      }

      styles={toolDef.styles}
      {...omit(props, 'name', 'className', 'currentContext', 'path', 'basePath', 'toolData', 'poster')}
    >
      {props.children}
    </PageFull>
  )
}

ToolPage.propTypes = {
  className: T.string,

  // tool props
  name: T.string.isRequired,
  toolData: T.shape({
    icon: T.string,
    display: T.shape({
      showIcon: T.bool,
      fullscreen: T.bool
    }),
    poster: T.string,
    permissions: T.object.isRequired
  }),
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace', 'account', 'public']),
    data: T.object
  }).isRequired,
  // the name of the primary action of the tool (if we want to override the default one).
  // it can contain more than one action name
  //primaryAction: T.string,

  // from store
  basePath: T.string,
  /*fullscreen: T.bool,*/
  /*toggleFullscreen: T.func.isRequired,*/

  // page props
  subtitle: T.node,
  actions: T.any,
  path: T.arrayOf(T.object),
  children: T.any,
  reload: T.func.isRequired
}

ToolPage.defaultProps = {
  path: []
}

export {
  ToolPage
}
