import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {matchPath} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {Summary} from '#/main/app/content/components/summary'

class DirectoryMenu extends Component {
  constructor(props) {
    super(props)

    this.getDirectorySummary = this.getDirectorySummary.bind(this)
  }

  componentDidMount() {
    this.props.fetchDirectories(this.props.currentId)
  }

  getDirectorySummary(directory) {
    return {
      type: LINK_BUTTON,
      id: directory.id,
      //icon: directory._opened ? 'fa fa-fw fa-folder-open' : 'fa fa-fw fa-folder',
      label: directory.name,
      collapsed: !directory._opened,
      collapsible: !directory._loaded || (directory.children && 0 !== directory.children.length),
      toggleCollapse: (collapsed) => this.props.toggleDirectoryOpen(directory, !collapsed),
      target: `${this.props.basePath}/${directory.id}`,
      active: !!matchPath(this.props.location.pathname, {path: `${this.props.path}/${directory.id}`}),
      children: directory.children ? directory.children.map(this.getDirectorySummary) : []
    }
  }

  render() {
    return (
      <MenuSection
        {...omit(this.props, 'path')}
        title={trans('directory', {}, 'resource')}
      >
        <Summary
          links={[
            {
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-list-ul',
              label: trans('Toutes les ressources'),
              active: !!matchPath(this.props.location.pathname, {path: `${this.props.basePath}/${this.props.currentId}/all`}),
              target: `${this.props.basePath}/${this.props.currentId}/all`
            }
          ].concat(this.props.directories.map(this.getDirectorySummary), [

          ])}
        />
      </MenuSection>
    )
  }
}

DirectoryMenu.propTypes = {
  location: T.shape({
    pathname: T.string.isRequired
  }),
  basePath: T.string.isRequired,
  currentId: T.string.isRequired,
  directories: T.arrayOf(T.shape({
    // TODO : directory type
  })).isRequired,
  fetchDirectories: T.func.isRequired,
  toggleDirectoryOpen: T.func.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

export {
  DirectoryMenu
}
