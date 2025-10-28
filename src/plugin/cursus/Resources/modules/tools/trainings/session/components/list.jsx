import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {DataMicro} from '#/main/app/data/components/micro'
import {PageContentList} from '#/main/app/page'
import {ToolPage} from '#/main/core/tool'

import {MODAL_TRAINING_COURSES} from '#/plugin/cursus/modals/courses'
import {MODAL_SESSION_FORM} from '#/plugin/cursus/session/modals/form'
import {SessionList} from '#/plugin/cursus/session/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/session/store'

const TrainingsSessionList = (props) => {
  return (
    <ToolPage
      title={trans('sessions', {}, 'cursus')}
    >
      <PageContentList
        title={trans('sessions', {}, 'cursus')}
        addAction={'workspace' === props.contextType ? {
          name: 'plan-session',
          type: MODAL_BUTTON,
          label: trans('plan_training_session', {}, 'actions'),
          modal: [MODAL_SESSION_FORM, {
            course: props.course,
            onSave: props.invalidateList
          }],
          // we can only create sessions from a workspace if the course does not define a model to generate a
          // new workspace for each session
          displayed: !isEmpty(props.course) && !get(props.course, 'workspace.meta.model') && props.canCreateSession
        } : {
          name: 'plan-session',
          type: MODAL_BUTTON,
          label: trans('plan_training_session', {}, 'actions'),
          modal: [MODAL_TRAINING_COURSES, {
            multiple: false,
            selectAction: (selected) => ({
              type: MODAL_BUTTON,
              label: trans('plan_training_session', {}, 'actions'),
              modal: [MODAL_SESSION_FORM, {
                course: selected[0],
                onSave: props.invalidateList
              }]
            })
          }],
          displayed: props.canCreateSession
        }}
      >
        <SessionList
          className="mb-5"
          flush={true}
          path={props.path}
          name={selectors.STORE_NAME + '.list'}
          url={['apiv2_cursus_session_context_list', {context: props.contextType, contextId: props.contextId}]}
          customDefinition={[
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              displayed: 'desktop' === props.contextType,
              displayable: 'desktop' === props.contextType,
              primary: true,
              filterable: false,
              render: (course) => <DataMicro object={course} />,
              order: 1,
              alias: 'course'
            }, {
              name: 'course',
              type: 'training_course',
              label: trans('course', {}, 'cursus'),
              displayable: false,
              sortable: false,
              filterable: 'desktop' === props.contextType
            }, {
              name: 'meta.canceled',
              type: 'boolean',
              alias: 'canceled',
              label: trans('canceled', {}, 'cursus'),
              displayable: false,
              sortable: false,
              filterable: true
            }
          ]}
        />
      </PageContentList>
    </ToolPage>
  )
}


TrainingsSessionList.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  invalidateList: T.func.isRequired,
  canCreateSession: T.bool.isRequired,
  course: T.object
}

export {
  TrainingsSessionList
}
