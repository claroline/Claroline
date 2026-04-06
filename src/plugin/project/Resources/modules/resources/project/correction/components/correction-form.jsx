import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/project/resources/project/store/selectors'
import {GRADING_MODE_RAW_SCORE, GRADING_MODE_RUBRIC} from '#/plugin/project/resources/project/constants'

const CorrectionForm = () => {
  const project = useSelector(selectors.project)

  return (
    <FormData
      name={selectors.STORE_NAME + '.correctionForm'}
      title={trans('correction', {}, 'project')}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'score',
              type: 'number',
              label: trans('score'),
              displayed: GRADING_MODE_RAW_SCORE === project.gradingMode,
              required: true,
              options: {
                min: 0,
                max: project.scoreMax,
                unit: '/ ' + project.scoreMax
              }
            }, {
              name: 'comment',
              type: 'html',
              label: trans('comment'),
              options: {
                minRows: 3
              }
            }
          ]
        }
      ]}
    />
  )
}

export {
  CorrectionForm
}
