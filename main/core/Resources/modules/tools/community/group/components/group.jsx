import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors as baseSelectors} from '#/main/core/tools/community/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

const GroupForm = props =>
  <FormData
    level={3}
    name={`${baseSelectors.STORE_NAME}.groups.current`}
    buttons={true}
    target={(group, isNew) => isNew ?
      ['apiv2_group_create'] :
      ['apiv2_group_update', {id: group.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/groups',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }
        ]
      }
    ]}
  />

GroupForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  group: T.shape({
    id: T.string
  }).isRequired,
  pickUsers: T.func.isRequired
}

const Group = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, baseSelectors.STORE_NAME+'.groups.current')),
    group: formSelect.data(formSelect.form(state, baseSelectors.STORE_NAME+'.groups.current'))
  })
)(GroupForm)

export {
  Group
}
