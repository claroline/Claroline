import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {DataMicro} from '#/main/app/data/components/micro'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConst} from '#/main/app/content/list/constants'
import {actions as listActions} from '#/main/app/content/list/store'

import {getActions, getDefaultAction} from '#/main/evaluation/sequence/utils'
import {SequenceCard} from '#/main/evaluation/sequence/components/card'

const SequenceList = (props) => {
  const currentUser = useSelector(securitySelectors.currentUser)

  const dispatch = useDispatch()

  const refresher = merge({
    add:    () => dispatch(listActions.invalidateData(props.name)),
    update: () => dispatch(listActions.invalidateData(props.name)),
    delete: () => dispatch(listActions.invalidateData(props.name))
  }, props.refresher || {})

  return (
    <ListData
      primaryAction={(row) => getDefaultAction(row, refresher, props.path, currentUser)}
      actions={(rows) => getActions(rows, refresher, props.path, currentUser)}
      definition={[
        {
          name: 'name',
          type: 'string',
          label: trans('name'),
          displayed: true,
          primary: true,
          render: (course) => <DataMicro object={course} />
        }, {
          name: 'meta.description',
          type: 'string',
          label: trans('description'),
          sortable: false,
          options: {long: true}
        }, {
          name: 'code',
          type: 'string',
          label: trans('code')
        }, {
          name: 'meta.createdAt',
          label: trans('creation_date'),
          type: 'date',
          alias: 'createdAt',
          filterable: false,
          options: {time: true}
        }, {
          name: 'meta.updatedAt',
          label: trans('modification_date'),
          type: 'date',
          alias: 'updatedAt',
          displayed: true,
          filterable: false,
          options: {time: true}
        }, {
          name: 'meta.creator',
          label: trans('creator'),
          type: 'user',
          alias: 'creator',
          sortable: false
        }, {
          name: 'meta.published',
          label: trans('published'),
          type: 'boolean',
          alias: 'published'
        }, {
          name: 'tags',
          type: 'tag',
          label: trans('tags'),
          sortable: false,
          options: {
            objectClass: 'Claroline\\EvaluationBundle\\Entity\\Sequence'
          }
        }
      ]}
      display={{
        current: listConst.DISPLAY_TILES
      }}

      {...omit(props, 'path', 'url', 'autoload', 'refresher')}

      name={props.name}
      fetch={{
        url: props.url,
        autoload: props.autoload
      }}
      card={SequenceCard}
    />
  )
}

SequenceList.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]),
  autoload: T.bool,
  refresher: T.object,
  children: T.node
}

SequenceList.defaultProps = {
  url: ['apiv2_evaluation_sequence_list'],
  autoload: true
}

export {
  SequenceList
}
