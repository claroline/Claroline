import React from 'react'
import {useSelector} from 'react-redux'

import {PageContent, PageHeadingSkeleton, PageToolbarSkeleton} from '#/main/app/page'
import {ToolPage} from '#/main/core/tool'

import {EventShow} from '#/plugin/agenda/event/components/show'
import {selectors} from '#/plugin/agenda/tools/agenda/store'

const AgendaEvent = () => {
  const event = useSelector(selectors.currentEvent)

  if (!event) {
    return (
      <ToolPage className="event-page">
        <PageContent className="placeholder-glow">
          <PageToolbarSkeleton toolbar="edit more" />
          <PageHeadingSkeleton icon={true} />
        </PageContent>
      </ToolPage>
    )
  }

  return (
    <EventShow event={event} />
  )
}

export {
  AgendaEvent
}
