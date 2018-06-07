import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {SummarizedContent} from '#/main/app/content/summary/components/content'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {constants as listConstants} from '#/main/core/data/list/constants'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

import {getActions} from '#/main/core/resource/utils'

// todo create a full app with store

const ResourceExplorer = props =>
  <SummarizedContent
    className="resources-explorer"
    summary={{
      displayed: true,
      title: trans('directories'),
      links: []
    }}
  >
    <DataListContainer
      name="resources"
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
        url: ['apiv2_resource_list', {parent: props.current.id || props.root.id || null}],
        autoload: true
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
      actions={getActions}
      card={ResourceCard}

      display={{
        current: listConstants.DISPLAY_TILES_SM
      }}
    />
  </SummarizedContent>

ResourceExplorer.propTypes = {
  primaryAction: T.func,
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  current: T.shape(
    ResourceNodeTypes.propTypes
  ),
  changeDirectory: T.func.isRequired
}

ResourceExplorer.defaultProps = {
  root: {},
  current: {}
}

export {
  ResourceExplorer
}
