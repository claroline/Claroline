import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'

import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON, URL_BUTTON} from '#/main/app/buttons'

const HeaderTools = props =>
  <Button
    id={`app-tools-${toKey(props.label)}`}
    type={MENU_BUTTON}
    className="app-header-btn app-header-item"
    icon={props.icon}
    label={props.label}
    tooltip="bottom"
    menu={{
      position: 'bottom',
      align: props.right ? 'right' : 'left',
      label: props.label,
      items: props.tools.map(tool => ({
        type: URL_BUTTON,
        icon: `fa fa-fw fa-${tool.icon}`,
        label: trans(tool.name, {}, 'tools'),
        target: tool.open
      }))
    }}
  />

HeaderTools.propTypes = {
  icon: T.string.isRequired,
  label: T.string.isRequired,
  right: T.bool,
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    open: T.oneOfType([T.string, T.array])
  })).isRequired
}

HeaderTools.defaultProps = {
  right: false
}

export {
  HeaderTools
}
