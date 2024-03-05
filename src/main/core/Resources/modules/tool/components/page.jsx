import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {PageFull} from '#/main/app/page/components/full'

import {getTool, getToolBreadcrumb} from '#/main/core/tool/utils'
import {ToolIcon} from '#/main/core/tool/components/icon'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const ToolPage = props => {
  return (
    <PageFull
      className={classes('tool-page', `${props.name}-page`, props.className)}
      title={trans(props.name, {}, 'tools')}
      showBreadcrumb={true}
      path={[].concat(getToolBreadcrumb(props.name, props.currentContext.type, props.currentContext.data), props.path)}
      poster={props.poster || get(props.toolData, 'poster') || get(props.currentContext, 'data.poster')}
      icon={get(props.toolData, 'display.showIcon') ?
        <ToolIcon type={get(props.toolData, 'icon')} />
        :
        undefined
      }
      fullscreen={props.fullscreen}
      meta={{
        title: `${trans(props.name, {}, 'tools')} - ${'workspace' === props.currentContext.type ? props.currentContext.data.name : trans(props.currentContext.type)}`,
        description: get(props.currentContext.data, 'meta.description')
      }}

      menu={
        <Await
          for={getTool(props.name, props.currentContext.type)}
          then={(module) => {
            if (module.default.menu) {
              return createElement(module.default.menu)
            }

            return createElement(ToolMenu)
          }}
        />
      }

      primaryAction={props.primaryAction}
      toolbar="more"
      {...omit(props, 'name', 'className', 'currentContext', 'path', 'basePath', 'toolData', 'reload', 'poster')}

      actions={props.actions}
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
    type: T.oneOf(['administration', 'desktop', 'workspace', 'account', 'home']),
    data: T.object
  }).isRequired,
  // the name of the primary action of the tool (if we want to override the default one).
  // it can contain more than one action name
  //primaryAction: T.string,

  // from store
  basePath: T.string,
  fullscreen: T.bool,
  reload: T.func.isRequired,
  toggleFullscreen: T.func.isRequired,

  // page props
  subtitle: T.node,
  actions: T.any,
  path: T.arrayOf(T.object),
  children: T.any
}

ToolPage.defaultProps = {
  path: []
}

export {
  ToolPage
}
