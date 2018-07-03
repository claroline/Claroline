import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {SummarizedContent} from '#/main/app/content/summary/components/content'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {constants as listConstants} from '#/main/core/data/list/constants'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

// todo expose directories actions in summary

const ResourceExplorer = props => {
  function summaryLink(directory) {
    return {
      type: 'callback',
      id: directory.id,
      icon: directory._opened ? 'fa fa-fw fa-folder-open' : 'fa fa-fw fa-folder',
      label: directory.name,
      collapsed: !directory._opened,
      collapsible: !directory._loaded || (directory.children && 0 !== directory.children.length),
      toggleCollapse: (collapsed) => props.toggleDirectoryOpen(directory, !collapsed),
      active: props.current && props.current.id === directory.id,
      callback: () => props.changeDirectory(directory),
      children: directory.children ? directory.children.map(summaryLink) : []
    }
  }

  return (
    <SummarizedContent
      className="resources-explorer"
      summary={{
        displayed: true,
        title: trans('directories'),
        links: props.directories.map(summaryLink)
      }}
    >
      <DataListContainer
        name={`${props.name}.resources`}
        primaryAction={props.primaryAction && ((resourceNode) => {
          if ('directory' !== resourceNode.meta.type) {
            return props.primaryAction(resourceNode)
          } else {
            // do not open directory, just change the target of the explorer
            return {
              label: trans('open', {}, 'actions'),
              type: 'callback',
              callback: () => props.changeDirectory(resourceNode)
            }
          }
        })}
        fetch={{
          url: ['apiv2_resource_list', {parent: get(props, 'current.id') || get(props, 'root.id') || null}],
          autoload: props.initialized
        }}
        definition={[
          {
            name: 'name',
            label: trans('name'),
            displayed: true,
            primary: true
          }, {
            name: 'meta.created',
            label: trans('creation_date'),
            type: 'date',
            alias: 'creationDate',
            displayed: true,
            filterable: false
          }, {
            name: 'createdAfter',
            label: trans('created_after'),
            type: 'date',
            displayable: false
          }, {
            name: 'createdBefore',
            label: trans('created_before'),
            type: 'date',
            displayable: false
          }
        ]}
        actions={props.actions}
        card={ResourceCard}

        display={{
          current: listConstants.DISPLAY_TILES_SM
        }}
      />
    </SummarizedContent>
  )
}

ResourceExplorer.propTypes = {
  initialized: T.bool,
  name: T.string.isRequired,
  primaryAction: T.func,
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  current: T.shape(
    ResourceNodeTypes.propTypes
  ),
  directories: T.arrayOf(T.shape(
    ResourceNodeTypes.propTypes
  )),
  toggleDirectoryOpen: T.func.isRequired,
  changeDirectory: T.func.isRequired,
  actions: T.func
}

ResourceExplorer.defaultProps = {
  initialized: false,
  root: {},
  current: {},
  directories: []
}

export {
  ResourceExplorer
}
