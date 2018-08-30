import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {SummarizedContent} from '#/main/app/content/summary/components/content'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

// todo expose directories actions in summary

const ResourceExplorer = props => {
  function summaryLink(directory) {
    return {
      type: CALLBACK_BUTTON,
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
      <ListData
        name={`${props.name}.resources`}
        primaryAction={(resourceNode) => {
          if ('directory' !== resourceNode.meta.type) {
            return props.primaryAction && props.primaryAction(resourceNode)
          } else {
            // do not open directory, just change the target of the explorer
            return {
              label: trans('open', {}, 'actions'),
              type: CALLBACK_BUTTON,
              callback: () => props.changeDirectory(resourceNode)
            }
          }
        }}
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
            name: 'meta.published',
            alias: 'published',
            type: 'boolean',
            label: trans('published'),
            displayed: props.current && props.current.permissions && props.current.permissions.administrate,
            filterable: props.current && props.current.permissions && props.current.permissions.administrate
          }, {
            name: 'meta.created',
            label: trans('creation_date'),
            type: 'date',
            alias: 'creationDate',
            displayed: true
          }, {
            name: 'meta.updated',
            label: trans('modification_date'),
            type: 'date',
            alias: 'modificationDate',
            displayed: true
          }, {
            name: 'resourceType',
            label: trans('type'),
            type: 'string',
            displayable: false,
            filterable: true
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
