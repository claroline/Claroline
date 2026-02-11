import React from 'react'
import {useSelector} from 'react-redux'

import {Button} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {selectors as toolSelectors, ToolOverview} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'
import {PageSection} from '#/main/app/page'

import {TrainingsRegistrationsPending} from '#/plugin/cursus/tools/trainings/components/registrations-pending'
import {TrainingsRegistrationsConfirm} from '#/plugin/cursus/tools/trainings/components/registrations-confirm'
import {TrainingsRegistrationsFull} from '#/plugin/cursus/tools/trainings/components/registrations-full'

const TrainingsOverview = () => {
  const toolPath = useSelector(toolSelectors.path)
  const contextType = useSelector(toolSelectors.contextType)

  return (
    <ToolOverview>
      <PageSection size="lg">
        <Button
          className="btn btn-primary ms-auto mt-5 mb-5"
          type={LINK_BUTTON}
          label={trans('presence_confirm', {}, 'presence')}
          target={`${toolPath}/presence`}
          exact={true}
        />
      </PageSection>
      <TrainingsRegistrationsFull contextType={contextType} path={toolPath} />
      <TrainingsRegistrationsConfirm contextType={contextType} path={toolPath} />
      <TrainingsRegistrationsPending contextType={contextType} path={toolPath} />
    </ToolOverview>
  )
}

export {
  TrainingsOverview
}
