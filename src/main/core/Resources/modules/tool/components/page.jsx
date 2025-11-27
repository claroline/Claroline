import React, {useCallback, useContext} from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ContextPage} from '#/main/app/context/components/page'

import {selectors, actions} from '#/main/core/tool/store'
import {getActions} from '#/main/core/tool/utils'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_COMMAND_PALETTE} from '#/main/app/context/modals/command-palette'
import {PageContext} from '#/main/app/page/context'

const ToolPage = props => {
  const toolDef = useContext(PageContext)

  const currentUser = useSelector(securitySelectors.currentUser)
  const toolName = useSelector(selectors.name)
  const toolPath = useSelector(selectors.path)
  const basePath = useSelector(selectors.basePath)
  const toolData = useSelector(selectors.tool)

  const dispatch = useDispatch()
  const reload = useCallback(() => dispatch(actions.reload()), [toolName])

  return (
    <ContextPage
      className={props.className}
      breadcrumb={props.breadcrumb || [
        {
          label: trans(toolName, {}, 'tools'),
          target: toolPath
        }
      ]}
      title={props.title || trans(toolName, {}, 'tools')}
      description={props.description || trans(toolName+'_desc', {}, 'tools')}
      menu={props.menu || {
        nav: toolDef.menu,
        toolbar: 'show-dashboard configure more',
        // get actions injected through plugins and the ones defined by the current tool
        actions: getActions([toolData], {
          update: reload
        }, basePath, currentUser).then(loadedActions => [
          {
            name: 'search',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-wand-magic-sparkles',
            label: trans('search', {}, 'actions') + ' (Ctrl + K)',
            modal: [MODAL_COMMAND_PALETTE],
            displayed: false
          }
        ].concat(loadedActions, toolDef.actions || []))
      }}
      banner={props.banner}
      styles={[].concat(toolDef.styles || [], props.styles || [])}
      embedded={props.embedded}
      showHeader={props.showHeader}
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
