import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import merge from 'lodash/merge'

import {trans} from '#/main/app/intl/translation'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {actions as listActions} from '#/main/app/content/list/store'
import {constants as listConst} from '#/main/app/content/list/constants'

import {getActions, getDefaultAction, getTypes} from '#/main/core/resource/utils'
import {ResourceCard} from '#/main/core/resource/components/card'
import {ResourceIcon} from '#/main/core/resource/components/icon'

const ResourceList = props => {
  const dispatch = useDispatch()
  const currentUser = useSelector(securitySelectors.currentUser)

  const refresher = merge({
    add:    () => dispatch(listActions.invalidateData(props.name)),
    update: () => dispatch(listActions.invalidateData(props.name)),
    delete: () => dispatch(listActions.invalidateData(props.name))
  }, props.refresher || {})

  return (
    <ListData
      autoFocus={props.autoFocus}
      primaryAction={(row) => getDefaultAction(row, refresher, props.path, currentUser)}
      actions={(rows) => getActions(rows, refresher, props.path, currentUser).then((actions) => [].concat(actions, props.customActions(rows)))}
      definition={[
        {
          name: 'name',
          label: trans('name'),
          type: 'string',
          displayed: true,
          primary: true,
          render: (resourceNode) => (
            <div className="d-flex flex-direction-row gap-3 align-items-center" role="presentation">
              <ResourceIcon mimeType={resourceNode.meta.mimeType} size="xs" />
              {resourceNode.name}
            </div>
          )
        }, {
          name: 'code',
          label: trans('code'),
          type: 'string'
        }, {
          name: 'meta.type',
          alias: 'resourceType',
          label: trans('type'),
          type: 'choice',
          options: {
            choices: getTypes()
              .sort((a, b) => trans(a.name, {}, 'resource') >= trans(b.name, {}, 'resource') ? 1 : -1)
              .reduce((resourceTypes, current) => Object.assign(resourceTypes, {[current.name]: trans(current.name, {}, 'resource')}), {}),
            condensed: true
          }
        }, {
          name: 'meta.views',
          type: 'number',
          label: trans('views')
        }, {
          name: 'meta.published',
          alias: 'published',
          type: 'boolean',
          label: trans('published')
        }, {
          name: 'meta.created',
          label: trans('creation_date'),
          type: 'date',
          alias: 'creationDate'
        }, {
          name: 'meta.updated',
          label: trans('modification_date'),
          type: 'date',
          alias: 'modificationDate',
          displayed: true
        }, {
          name: 'meta.creator',
          type: 'user',
          label: trans('creator'),
          displayed: true
        }, {
          name: 'estimatedDuration',
          label: trans('estimated_duration'),
          type: 'number',
          options: {
            unit: trans('minutes')
          }
        }, {
          name: 'restrictions.hidden',
          label: trans('hidden'),
          type: 'boolean',
          alias: 'hidden',
          filterable: true,
          displayable: false
        }, {
          name: 'tags',
          type: 'tag',
          label: trans('tags'),
          displayable: true,
          sortable: false,
          options: {
            objectClass: 'Claroline\\CoreBundle\\Entity\\Resource\\ResourceNode'
          }
        }
      ].concat(props.customDefinition)}
      display={{
        current: listConst.DISPLAY_TILES
      }}

      {...omit(props, 'path', 'url', 'autoload', 'backAction', 'customDefinition', 'customActions', 'refresher', 'invalidate')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}
      customActions={props.backAction ? [props.backAction] : null}
      card={ResourceCard}
    />
  )
}

ResourceList.propTypes = {
  path: T.string,
  name: T.string.isRequired,
  autoFocus: T.bool,
  autoload: T.bool,
  url: T.oneOfType([T.string, T.array]).isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),
  backAction: T.object,
  customActions: T.func,
  invalidate: T.func.isRequired,
  currentUser: T.object,
  refresher: T.shape({
    add: T.func,
    update: T.func,
    delete: T.func
  })
}

ResourceList.defaultProps = {
  autoload: true,
  customDefinition: [],
  customActions: () => []
}

export {
  ResourceList
}
