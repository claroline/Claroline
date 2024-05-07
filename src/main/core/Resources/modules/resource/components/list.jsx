import React from 'react'
import {connect} from 'react-redux'
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

const Resources = props => {
  const refresher = merge({
    add:    () => props.invalidate(props.name),
    update: () => props.invalidate(props.name),
    delete: () => props.invalidate(props.name)
  }, props.refresher || {})

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, refresher, props.path, props.currentUser)}
      actions={(rows) => getActions(rows, refresher, props.path, props.currentUser).then((actions) => [].concat(actions, props.customActions(rows)))}
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
          name: 'parent',
          label: trans('directory', {}, 'resource'),
          type: 'resource'
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
          alias: 'creationDate',
          displayed: true
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
          name: 'evaluation.estimatedDuration',
          label: trans('estimated_duration'),
          type: 'number',
          options: {
            unit: trans('minutes')
          },
          alias: 'estimatedDuration'
        }, {
          name: 'evaluation.required',
          label: trans('required_resource', {}, 'resource'),
          type: 'boolean',
          alias: 'required'
        }, {
          name: 'evaluation.evaluated',
          label: trans('evaluated_resource', {}, 'resource'),
          type: 'boolean',
          alias: 'evaluated'
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
        current: listConst.DISPLAY_TILES_SM
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

Resources.propTypes = {
  path: T.string,
  name: T.string.isRequired,
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

Resources.defaultProps = {
  autoload: true,
  customDefinition: [],
  customActions: () => []
}

const ResourceList = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  }),
  (dispatch) => ({
    invalidate(name) {
      dispatch(listActions.invalidateData(name))
    }
  })
)(Resources)

export {
  ResourceList
}
