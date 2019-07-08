import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {MenuSection} from '#/main/app/layout/menu/components/section'

/*function summaryLink(directory) {
  return {
    type: LINK_BUTTON,
    id: directory.id,
    icon: directory._opened ? 'fa fa-fw fa-folder-open' : 'fa fa-fw fa-folder',
    label: directory.name,
    collapsed: !directory._opened,
    collapsible: !directory._loaded || (directory.children && 0 !== directory.children.length),
    toggleCollapse: (collapsed) => props.toggleDirectoryOpen(directory, !collapsed),
    target: `${props.path}/${directory.id}`,
    children: directory.children ? directory.children.map(summaryLink) : []
  }
}*/

const DirectoryMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('directory', {}, 'resource')}
  >
    My directory menu
  </MenuSection>

DirectoryMenu.propTypes = {
  path: T.string.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

export {
  DirectoryMenu
}
