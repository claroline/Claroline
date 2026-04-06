import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Resource} from '#/main/core/resource'

import {ProjectEditor} from '#/plugin/project/resources/project/editor/components/editor'
import {Overview} from '#/plugin/project/resources/project/overview/components/overview'
import {MySubmission} from '#/plugin/project/resources/project/player/components/my-submission'
import {SubmissionList} from '#/plugin/project/resources/project/correction/components/submission-list'
import {CorrectionForm} from '#/plugin/project/resources/project/correction/components/correction-form'

const ProjectResource = props =>
  <Resource
    {...omit(props)}
    editor={ProjectEditor}
    overviewPage={Overview}
    actions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-upload',
        label: trans('my_submission', {}, 'project'),
        target: `${props.path}/my/submission`,
        displayed: !props.canEdit
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-list',
        label: trans('submissions', {}, 'project'),
        target: `${props.path}/submissions`,
        displayed: props.canEdit
      }
    ]}
    pages={[
      {
        path: '/my/submission',
        component: MySubmission,
        disabled: props.canEdit
      }, {
        path: '/submissions',
        component: SubmissionList,
        disabled: !props.canEdit
      }, {
        path: '/correction/:id',
        component: CorrectionForm,
        disabled: !props.canEdit
      }
    ]}
  />

ProjectResource.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  project: T.object.isRequired,
  mySubmissions: T.array,
  myCorrections: T.array
}

export {
  ProjectResource
}
