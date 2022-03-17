import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {PageFull} from '#/main/app/page/components/full'

import {ToolIcon} from '#/main/core/tool/components/icon'
import {getActions, getToolBreadcrumb, showToolBreadcrumb} from '#/main/core/tool/utils'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import classes from 'classnames'

const ToolPage = props => {
  let toolbar = 'edit rights'
  if (props.primaryAction) {
    toolbar = props.primaryAction + ' | ' + toolbar
  }
  toolbar += ' | fullscreen more'

  // ToolPage component can be used multiple times inside a tool,
  // we need to manage the fullscreen flag by ourselves otherwise it will be reset
  // by each new render of a ToolPage because it's stored in the internal state of PageFull
  const fullscreenAction = {
    name: 'fullscreen',
    type: CALLBACK_BUTTON,
    icon: classes('fa fa-fw', {
      'fa-expand': !props.fullscreen,
      'fa-compress': props.fullscreen
    }),
    label: trans(props.fullscreen ? 'fullscreen_off' : 'fullscreen_on'),
    callback: props.toggleFullscreen
  }

  return (
    <PageFull
      className={`${props.name}-page`}
      title={trans(props.name, {}, 'tools')}
      showBreadcrumb={showToolBreadcrumb(props.currentContext.type, props.currentContext.data)}
      path={[].concat(getToolBreadcrumb(props.name, props.currentContext.type, props.currentContext.data), props.path)}
      poster={get(props.toolData, 'poster.url')}
      icon={get(props.toolData, 'display.showIcon') ?
        <ToolIcon type={get(props.toolData, 'icon')} />
        :
        undefined
      }
      fullscreen={props.fullscreen}
      meta={{
        title: `${trans(props.name, {}, 'tools')} - ${'workspace' === props.currentContext.type ? props.currentContext.data.code : trans(props.currentContext.type)}`,
        description: get(props.currentContext.data, 'meta.description')
      }}

      {...omit(props, 'name', 'currentContext', 'path', 'basePath', 'toolData', 'reload')}
      toolbar={toolbar}
      actions={getActions(props.toolData, props.currentContext, {
        update: () => props.reload()
      }, props.basePath).then(baseActions => {
        if (props.actions instanceof Promise) {
          return props.actions.then(promisedActions => promisedActions.concat(baseActions, [fullscreenAction]))
        }

        return (props.actions || []).concat(baseActions, [fullscreenAction])
      })}
    >
      {props.children}
    </PageFull>
  )
}

ToolPage.propTypes = {
  // tool props
  name: T.string.isRequired,
  toolData: T.shape({
    icon: T.string,
    display: T.shape({
      showIcon: T.bool,
      fullscreen: T.bool
    }),
    poster: T.shape({
      url: T.string.isRequired
    }),
    permissions: T.object.isRequired
  }),
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace']),
    data: T.object
  }).isRequired,
  // the name of the primary action of the tool (if we want to override the default one).
  // it can contain more than one action name
  primaryAction: T.string,

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
