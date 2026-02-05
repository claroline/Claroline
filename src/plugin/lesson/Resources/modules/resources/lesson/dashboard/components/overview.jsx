import React from 'react'
import {useSelector} from 'react-redux'
import Accordion from 'react-bootstrap/Accordion'
import {Badge} from '#/main/app/components/badge'
import {PageContent, PageHeading, PageSection} from '#/main/app/page'

import {ActivityChart} from '#/main/evaluation/chart/components/activity'
import get from 'lodash/get'
import {ResourceDashboardMetrics} from '#/main/core/resource/dashboard/overview/components/metrics'
import {ResourceDashboardInfo} from '#/main/core/resource/dashboard/overview/components/info'
import {trans, transChoice} from '#/main/app/intl/translation'
import {selectors as LessonSelectors} from '#/plugin/lesson/resources/lesson/store'
const LessonDashboardOverview = () => {
  const pages = useSelector(LessonSelectors.pages)
  const lesson = useSelector(LessonSelectors.lesson)

  return (
    <PageContent>
      <PageHeading title={trans('overview')} className="visually-hidden" level={2} />
      <PageSection className="my-4" size="lg" title={trans('general')} level={3} showTitle={false} flush={true}>
        <ResourceDashboardInfo />
      </PageSection>

      <PageSection size="full" className="mb-4">
        <div className="border-top border-bottom" role="presentation">
          <ResourceDashboardMetrics />
        </div>
      </PageSection>

      <PageSection size="xl" className="my-5">
        {pages.length === 0 ? (
          <div />
        ) : (
          <Accordion
            defaultActiveKey={pages[0] ? String(pages[0].id) : undefined}
          >
            {pages.map((page) => {
              const views = get(page, 'meta.views', 0)
              return (
                <Accordion.Item
                  key={page.id}
                  eventKey={String(page.id)}
                >
                  <Accordion.Header>
                    <div className="d-flex justify-content-between align-items-center w-100 pe-3">
                      <div className="text-truncate">
                        {page.title || ''}
                      </div>

                      <Badge variant="secondary" subtle>
                        <span className="fa fa-eye me-2" />
                        {transChoice('display_views', views, {count: views})}
                      </Badge>
                    </div>
                  </Accordion.Header>
                  <Accordion.Body>
                    <ActivityChart
                      name={`LessonActivity${page.id}`}
                      activityUrl={(activityType) => [
                        'apiv2_chapter_activity',
                        {lessonId: lesson.id, id: page.id, activityType: activityType}
                      ]}
                      logUrl={['apiv2_chapter_functional_logs', {lessonId: lesson.id, id: page.id}]}
                      viewUrl={['apiv2_chapter_views', {lessonId: lesson.id, id: page.id}]}
                    />
                  </Accordion.Body>
                </Accordion.Item>
              )
            })}
          </Accordion>
        )}
      </PageSection>
    </PageContent>
  )
}

export {
  LessonDashboardOverview
}
