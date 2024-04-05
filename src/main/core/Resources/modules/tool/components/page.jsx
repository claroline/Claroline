import React, {useCallback, useContext} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'

import {ContextPage} from '#/main/app/context/components/page'
import {ToolContext} from '#/main/core/tool/context'
import {selectors, actions} from '#/main/core/tool/store'
import {ToolIcon} from '#/main/core/tool/components/icon'
import {ToolMenu} from '#/main/core/tool/components/menu'

const ToolPage = props => {
  const toolDef = useContext(ToolContext)

  const toolName = useSelector(selectors.name)
  const toolPath = useSelector(selectors.path)
  const toolData = useSelector(selectors.toolData)
  const currentContext = useSelector(selectors.context)

  const dispatch = useDispatch()
  const reload = useCallback(() => dispatch(actions.reload()), [toolName])

  return (
    <ContextPage
      className={classes('tool-page', `${toolName}-page`, props.className)}
      breadcrumb={[
        {
          label: trans(toolName, {}, 'tools'),
          target: toolPath
        }
      ].concat(props.breadcrumb || [])}
      poster={props.poster || get(toolData, 'poster')}
      title={trans(toolName, {}, 'tools')}
      icon={get(toolData, 'display.showIcon') ?
        <ToolIcon type={get(toolData, 'icon')} />
        :
        undefined
      }

      menu={
        <ToolMenu
          path={toolPath}
          currentContext={currentContext}
          toolData={toolData}
          menu={toolDef.menu}
          actions={toolDef.actions}
          reload={reload}
        />
      }

      styles={[].concat(toolDef.styles, props.styles || [])}
      {...omit(props, 'className', 'breadcrumb', 'poster', 'styles')}
    >
      {props.children}
    </ContextPage>
  )
}

ToolPage.propTypes = ContextPage.propTypes
ToolPage.defaultProps = ContextPage.defaultProps

export {
  ToolPage
}
