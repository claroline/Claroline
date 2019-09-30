import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {matchPath} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {MenuSection} from '#/main/app/layout/menu/components/section'
import {Summary} from '#/main/app/content/components/summary'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'

class DirectoryMenu extends Component {
  constructor(props) {
    super(props)

    this.getDirectorySummary = this.getDirectorySummary.bind(this)
  }

  componentDidMount() {
    this.props.fetchDirectories(this.props.currentNode.id)
  }

  getDirectorySummary(directory) {
    return {
      type: LINK_BUTTON,
      id: directory.id,
      label: directory.name,
      collapsed: !directory._opened,
      collapsible: !directory._loaded || (directory.children && 0 !== directory.children.length),
      toggleCollapse: (collapsed) => this.props.toggleDirectoryOpen(directory.id, !collapsed),
      target: `${this.props.basePath}/${directory.slug}`,
      active: !!matchPath(this.props.location.pathname, {path: `${this.props.basePath}/${directory.slug}`}),
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
              icon: 'fa fa-fw fa-arrow-left',
              label: get(this.props.currentNode, 'parent') ?
                trans('back_to', {target: get(this.props.currentNode, 'parent.name')}) :
                trans('back'),
              displayed: isEmpty(this.props.rootNode) || this.props.currentNode.slug !== this.props.rootNode.slug,
              target: `${this.props.basePath}/${get(this.props.currentNode, 'parent.slug', '')}`,
              exact: true
            }, {
              type: LINK_BUTTON,
              icon: 'fa fa-fw fa-list-ul',
              label: trans('all_resources', {}, 'resource'),
              active: !!matchPath(this.props.location.pathname, {path: `${this.props.basePath}/${this.props.currentNode.slug}/all`}),
              target: `${this.props.basePath}/${this.props.currentNode.slug}/all`
            }
          ].concat(this.props.directories.map(this.getDirectorySummary))}
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
  rootNode: T.shape(
    ResourceNodeTypes.propTypes
  ),
  currentNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  directories: T.arrayOf(T.shape(
    ResourceNodeTypes.propTypes
  )).isRequired,
  fetchDirectories: T.func.isRequired,
  toggleDirectoryOpen: T.func.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

export {
  DirectoryMenu
}
