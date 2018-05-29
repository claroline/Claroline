import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {SummarizedContent} from '#/main/app/content/summary/components/content'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {constants as listConstants} from '#/main/core/data/list/constants'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

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
      primaryAction={props.primaryAction}
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
  )
}

ResourceExplorer.defaultProps = {
  root: {},
  current: {}
}

export {
  ResourceExplorer
}
